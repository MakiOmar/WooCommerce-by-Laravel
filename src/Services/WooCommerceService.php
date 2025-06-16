<?php

namespace Makiomar\WooOrderDashboard\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
}