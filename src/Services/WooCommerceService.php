<?php

namespace Makiomar\WooOrderDashboard\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WooCommerceService
{
    protected function cleanStatus($status)
    {
        return str_replace('wc-', '', $status);
    }

    public function getOrders(array $filters = [])
    {
        try {
            // Main query with joins for essential meta fields
            $query = DB::connection('woocommerce')->table('posts as p')
                ->leftJoin('postmeta as pm1', function($join) {
                    $join->on('p.ID', '=', 'pm1.post_id')
                         ->where('pm1.meta_key', '_billing_first_name');
                })
                ->leftJoin('postmeta as pm2', function($join) {
                    $join->on('p.ID', '=', 'pm2.post_id')
                         ->where('pm2.meta_key', '_billing_last_name');
                })
                ->leftJoin('postmeta as pm3', function($join) {
                    $join->on('p.ID', '=', 'pm3.post_id')
                         ->where('pm3.meta_key', '_billing_phone');
                })
                ->leftJoin('postmeta as pm4', function($join) {
                    $join->on('p.ID', '=', 'pm4.post_id')
                         ->where('pm4.meta_key', '_order_total');
                })
                ->select([
                    'p.ID as id', 
                    'p.post_date', 
                    'p.post_status',
                    'pm1.meta_value as billing_first_name',
                    'pm2.meta_value as billing_last_name',
                    'pm3.meta_value as billing_phone',
                    'pm4.meta_value as order_total'
                ])
                ->where('p.post_type', 'shop_order');

            // Apply filters
            if (!empty($filters['order_id'])) {
                $query->where('p.ID', $filters['order_id']);
            }

            if (!empty($filters['start_date'])) {
                $query->where('p.post_date', '>=', $filters['start_date']);
            }

            if (!empty($filters['end_date'])) {
                $query->where('p.post_date', '<=', $filters['end_date']);
            }

            if (!empty($filters['status'])) {
                $query->where('p.post_status', 'wc-' . $filters['status']);
            }

            if (!empty($filters['meta_key']) && !empty($filters['meta_value'])) {
                $query->whereExists(function ($sub) use ($filters) {
                    $sub->select(DB::raw(1))
                        ->from('postmeta')
                        ->whereRaw('postmeta.post_id = p.ID')
                        ->where('postmeta.meta_key', $filters['meta_key'])
                        ->where('postmeta.meta_value', 'LIKE', '%' . $filters['meta_value'] . '%');
                });
            }

            // Get total count before pagination
            $total = $query->count();
            $perPage = $filters['per_page'] ?? config('woo-order-dashboard.pagination.per_page');
            $page = $filters['page'] ?? 1;

            // Get paginated results
            $orders = $query->orderByDesc('p.post_date')
                ->forPage($page, $perPage)
                ->get();

            // Debug the results
            Log::info('WooCommerce Orders Query Results', [
                'total' => $total,
                'perPage' => $perPage,
                'page' => $page,
                'orders_count' => $orders->count(),
                'first_order' => $orders->first(),
                'query' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $orders = $orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'status' => $this->cleanStatus($order->post_status),
                    'date_created' => $order->post_date,
                    'total' => $order->order_total ?? 0,
                    'billing' => [
                        'first_name' => $order->billing_first_name ?? '',
                        'last_name' => $order->billing_last_name ?? '',
                        'phone' => $order->billing_phone ?? ''
                    ]
                ];
            });

            // Create paginator
            $paginator = new LengthAwarePaginator(
                $orders,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            return [
                'data' => $paginator,
                'headers' => [
                    'X-WP-Total' => $total,
                    'X-WP-TotalPages' => ceil($total / $perPage)
                ]
            ];
        } catch (\Exception $e) {
            Log::error('WooCommerce Orders Query Failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $perPage = config('woo-order-dashboard.pagination.per_page', 15);

            return [
                'data' => new LengthAwarePaginator(collect(), 0, $perPage, 1),
                'headers' => [
                    'X-WP-Total' => 0,
                    'X-WP-TotalPages' => 0
                ]
            ];
        }
    }

    public function getOrder($id)
    {
        try {
            // Get order with all meta in one query
            $orderData = DB::connection('woocommerce')
                ->table('posts')
                ->leftJoin('postmeta', 'posts.ID', '=', 'postmeta.post_id')
                ->where('posts.ID', $id)
                ->where('posts.post_type', 'shop_order')
                ->get()
                ->reduce(function($carry, $item) {
                    if (!isset($carry['order'])) {
                        $carry['order'] = [
                            'ID' => $item->ID,
                            'post_date' => $item->post_date,
                            'post_status' => $item->post_status
                        ];
                    }
                    if ($item->meta_key) {
                        $carry['meta'][$item->meta_key] = $item->meta_value;
                    }
                    return $carry;
                }, ['order' => null, 'meta' => []]);

            if (!$orderData['order']) {
                return null;
            }

            $order = $orderData['order'];
            $meta = collect($orderData['meta'] ?? []);

            // Get line items with their meta in one query
            $lineItems = DB::connection('woocommerce')
                ->table('woocommerce_order_items as oi')
                ->leftJoin('woocommerce_order_itemmeta as oim', 'oi.order_item_id', '=', 'oim.order_item_id')
                ->select([
                    'oi.order_item_id',
                    'oi.order_item_name',
                    'oim.meta_key',
                    'oim.meta_value'
                ])
                ->where('oi.order_id', $order['ID'])
                ->where('oi.order_item_type', 'line_item')
                ->get()
                ->groupBy('order_item_id')
                ->map(function ($items) {
                    $firstItem = $items->first();
                    $metas = $items->whereNotNull('meta_key')
                              ->pluck('meta_value', 'meta_key');

                    return [
                        'id' => $firstItem->order_item_id,
                        'name' => $firstItem->order_item_name,
                        'sku' => $metas['_sku'] ?? '',
                        'quantity' => $metas['_qty'] ?? 1,
                        'price' => $metas['_line_total'] ?? 0,
                        'total' => $metas['_line_total'] ?? 0,
                        'meta_data' => $metas->map(function ($value, $key) {
                            return [
                                'display_key' => $key,
                                'display_value' => $value
                            ];
                        })->values()->toArray()
                    ];
                });

            // Get order notes
            $notes = DB::connection('woocommerce')
                ->table('comments as c')
                ->leftJoin('commentmeta as cm', 'c.comment_ID', '=', 'cm.comment_id')
                ->select([
                    'c.comment_ID',
                    'c.comment_content',
                    'c.comment_date',
                    'c.comment_author',
                    'cm.meta_value as is_customer_note'
                ])
                ->where('c.comment_post_ID', $order['ID'])
                ->where('c.comment_type', 'order_note')
                ->get()
                ->map(function ($note) {
                    return [
                        'id' => $note->comment_ID,
                        'note' => $note->comment_content,
                        'date_created' => $note->comment_date,
                        'added_by' => $note->comment_author,
                        'is_customer_note' => (bool) $note->is_customer_note
                    ];
                });

            return [
                'id' => $order['ID'],
                'status' => $this->cleanStatus($order['post_status']),
                'date_created' => $order['post_date'],
                'currency' => $meta['_order_currency'] ?? 'USD',
                'total' => $meta['_order_total'] ?? 0,
                'subtotal' => $meta['_order_subtotal'] ?? 0,
                'shipping_total' => $meta['_order_shipping'] ?? 0,
                'shipping_tax' => $meta['_order_shipping_tax'] ?? 0,
                'total_tax' => $meta['_order_tax'] ?? 0,
                'discount_total' => $meta['_order_discount'] ?? 0,
                'customer_id' => $meta['_customer_user'] ?? null,
                'customer_note' => $meta['_customer_note'] ?? null,
                'billing' => [
                    'first_name' => $meta['_billing_first_name'] ?? '',
                    'last_name' => $meta['_billing_last_name'] ?? '',
                    'email' => $meta['_billing_email'] ?? '',
                    'phone' => $meta['_billing_phone'] ?? '',
                    'address_1' => $meta['_billing_address_1'] ?? '',
                    'address_2' => $meta['_billing_address_2'] ?? '',
                    'city' => $meta['_billing_city'] ?? '',
                    'state' => $meta['_billing_state'] ?? '',
                    'postcode' => $meta['_billing_postcode'] ?? '',
                    'country' => $meta['_billing_country'] ?? '',
                ],
                'shipping' => [
                    'first_name' => $meta['_shipping_first_name'] ?? '',
                    'last_name' => $meta['_shipping_last_name'] ?? '',
                    'address_1' => $meta['_shipping_address_1'] ?? '',
                    'address_2' => $meta['_shipping_address_2'] ?? '',
                    'city' => $meta['_shipping_city'] ?? '',
                    'state' => $meta['_shipping_state'] ?? '',
                    'postcode' => $meta['_shipping_postcode'] ?? '',
                    'country' => $meta['_shipping_country'] ?? '',
                ],
                'payment_method' => $meta['_payment_method'] ?? '',
                'payment_method_title' => $meta['_payment_method_title'] ?? '',
                'transaction_id' => $meta['_transaction_id'] ?? null,
                'shipping_method' => $meta['_shipping_method'] ?? '',
                'line_items' => $lineItems->values(),
                'order_notes' => $notes,
                'meta_data' => $meta->map(function ($value, $key) {
                    return ['display_key' => $key, 'display_value' => $value];
                })->values()->toArray(),
            ];
        } catch (\Exception $e) {
            Log::error('WooCommerce Single Order Failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    public function getProducts($search = null)
    {
        $query = DB::connection('woocommerce')->table('posts as p')
            ->leftJoin('postmeta as sku', function($join) {
                $join->on('p.ID', '=', 'sku.post_id')
                     ->where('sku.meta_key', '_sku');
            })
            ->leftJoin('postmeta as price', function($join) {
                $join->on('p.ID', '=', 'price.post_id')
                     ->where('price.meta_key', '_price');
            })
            ->select([
                'p.ID as id',
                'p.post_title as name',
                'sku.meta_value as sku',
                'price.meta_value as price'
            ])
            ->where('p.post_type', 'product')
            ->where('p.post_status', 'publish');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('p.post_title', 'like', "%$search%")
                  ->orWhere('sku.meta_value', 'like', "%$search%")
                  ->orWhere('p.ID', $search);
            });
        }

        $products = $query->limit(20)->get();

        return $products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
            ];
        });
    }

    public function getCustomers($search = null)
    {
        $query = DB::connection('woocommerce')->table('users as u')
            ->leftJoin('usermeta as fn', function($join) {
                $join->on('u.ID', '=', 'fn.user_id')->where('fn.meta_key', 'first_name');
            })
            ->leftJoin('usermeta as ln', function($join) {
                $join->on('u.ID', '=', 'ln.user_id')->where('ln.meta_key', 'last_name');
            })
            ->select([
                'u.ID as id',
                'u.user_email as email',
                'fn.meta_value as first_name',
                'ln.meta_value as last_name'
            ]);
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('u.user_email', 'like', "%$search%")
                  ->orWhere('fn.meta_value', 'like', "%$search%")
                  ->orWhere('ln.meta_value', 'like', "%$search%")
                  ->orWhere('u.ID', $search);
            });
        }
        $customers = $query->limit(20)->get();
        return $customers->map(function($c) {
            return [
                'id' => $c->id,
                'name' => trim(($c->first_name ?? '').' '.($c->last_name ?? '')),
                'email' => $c->email,
            ];
        });
    }

    public function createOrder($data)
    {
        $db = DB::connection('woocommerce');
        try {
            $db->beginTransaction();

            // 1. Customer creation if needed
            $customerId = $data['customer_id'] ?? null;
            if (!$customerId) {
                // Create new user (customer)
                $customerId = $db->table('users')->insertGetId([
                    'user_login' => 'phoneorder_'.uniqid(),
                    'user_pass' => bcrypt(Str::random(12)),
                    'user_email' => '',
                    'user_registered' => now(),
                    'user_status' => 0,
                    'display_name' => 'Phone Order',
                ]);
                // Optionally add usermeta for name, etc.
            }

            // 2. Insert order (shop_order) in posts
            $orderId = $db->table('posts')->insertGetId([
                'post_author' => $customerId,
                'post_date' => now(),
                'post_date_gmt' => now('UTC'),
                'post_content' => $data['customer_note'] ?? '',
                'post_title' => 'Order &ndash; '.now()->format('F j, Y @ H:i'),
                'post_excerpt' => '',
                'post_status' => 'wc-'.($data['order_status'] ?? 'pending'),
                'comment_status' => 'open',
                'ping_status' => 'closed',
                'post_password' => '',
                'post_name' => 'order-'.now()->timestamp,
                'to_ping' => '',
                'pinged' => '',
                'post_content_filtered' => '',
                'post_type' => 'shop_order',
                'post_mime_type' => '',
                'comment_count' => 0,
                'post_modified' => now(),
                'post_modified_gmt' => now('UTC'),
                'guid' => '',
            ]);

            // 3. Add order meta (postmeta)
            // Calculate order total from items
            $orderTotal = 0;
            foreach ($data['order_items'] as $item) {
                $orderTotal += ($item['price'] * $item['qty']);
            }
            
            // Apply discount, add shipping and taxes
            $discount = $data['discount'] ?? 0;
            $shipping = $data['shipping'] ?? 0;
            $taxes = $data['taxes'] ?? 0;
            $finalTotal = $orderTotal - $discount + $shipping + $taxes;
            
            $meta = [
                ['post_id' => $orderId, 'meta_key' => '_customer_user', 'meta_value' => $customerId],
                ['post_id' => $orderId, 'meta_key' => '_order_total', 'meta_value' => $finalTotal],
                ['post_id' => $orderId, 'meta_key' => '_order_discount', 'meta_value' => $discount],
                ['post_id' => $orderId, 'meta_key' => '_order_shipping', 'meta_value' => $shipping],
                ['post_id' => $orderId, 'meta_key' => '_order_tax', 'meta_value' => $taxes],
                ['post_id' => $orderId, 'meta_key' => '_payment_method', 'meta_value' => $data['payment_method']],
                ['post_id' => $orderId, 'meta_key' => '_created_via', 'meta_value' => 'phone-order-dashboard'],
            ];
            $db->table('postmeta')->insert($meta);

            // 4. Add order items
            foreach ($data['order_items'] as $item) {
                $orderItemId = $db->table('woocommerce_order_items')->insertGetId([
                    'order_item_name' => $item['name'],
                    'order_item_type' => 'line_item',
                    'order_id' => $orderId,
                ]);
                $db->table('woocommerce_order_itemmeta')->insert([
                    ['order_item_id' => $orderItemId, 'meta_key' => '_product_id', 'meta_value' => $item['id']],
                    ['order_item_id' => $orderItemId, 'meta_key' => '_qty', 'meta_value' => $item['qty']],
                    ['order_item_id' => $orderItemId, 'meta_key' => '_line_total', 'meta_value' => $item['price'] * $item['qty']],
                ]);
            }

            // 5. Add order notes (comments)
            if (!empty($data['private_note'])) {
                $db->table('comments')->insert([
                    'comment_post_ID' => $orderId,
                    'comment_author' => 'admin',
                    'comment_content' => $data['private_note'],
                    'comment_type' => 'order_note',
                    'comment_approved' => 1,
                    'comment_date' => now(),
                    'comment_date_gmt' => now('UTC'),
                ]);
            }

            $db->commit();
            return [
                'success' => true,
                'order_id' => $orderId,
                'message' => 'Order created successfully.'
            ];
        } catch (\Exception $e) {
            $db->rollBack();
            \Log::error('Order creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return [
                'success' => false,
                'message' => 'Order creation failed: '.$e->getMessage()
            ];
        }
    }

    public function bulkDeleteOrders(array $orderIds)
    {
        $db = DB::connection('woocommerce');
        try {
            $db->beginTransaction();

            $deletedCount = 0;
            $errors = [];

            foreach ($orderIds as $orderId) {
                try {
                    // 1. Delete order items and their meta
                    $orderItemIds = $db->table('woocommerce_order_items')
                        ->where('order_id', $orderId)
                        ->pluck('order_item_id');

                    if ($orderItemIds->isNotEmpty()) {
                        $db->table('woocommerce_order_itemmeta')
                            ->whereIn('order_item_id', $orderItemIds)
                            ->delete();
                        
                        $db->table('woocommerce_order_items')
                            ->whereIn('order_item_id', $orderItemIds)
                            ->delete();
                    }

                    // 2. Delete order notes (comments)
                    $db->table('comments')
                        ->where('comment_post_ID', $orderId)
                        ->where('comment_type', 'order_note')
                        ->delete();

                    // 3. Delete order meta (postmeta)
                    $db->table('postmeta')
                        ->where('post_id', $orderId)
                        ->delete();

                    // 4. Delete the order post
                    $deleted = $db->table('posts')
                        ->where('ID', $orderId)
                        ->where('post_type', 'shop_order')
                        ->delete();

                    if ($deleted > 0) {
                        $deletedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Order #{$orderId}: " . $e->getMessage();
                }
            }

            $db->commit();

            if ($deletedCount > 0) {
                $message = "Successfully deleted {$deletedCount} order(s)";
                if (!empty($errors)) {
                    $message .= ". Errors: " . implode('; ', $errors);
                }

                // Clear the orders cache
                static::clearCacheByTags('orders');
                
                return [
                    'success' => true,
                    'message' => $message,
                    'deleted_count' => $deletedCount,
                    'errors' => $errors
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No orders were deleted. Errors: ' . implode('; ', $errors)
                ];
            }

        } catch (\Exception $e) {
            $db->rollBack();
            \Log::error('Bulk order deletion failed', [
                'order_ids' => $orderIds,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Bulk deletion failed: ' . $e->getMessage()
            ];
        }
    }
}