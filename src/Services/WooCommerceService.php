<?php

namespace Makiomar\WooOrderDashboard\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class WooCommerceService
{
    protected $prefix;

    public function __construct()
    {
        $this->prefix = config('woo-order-dashboard.db_prefix', 'wp_');
    }

    public function getOrders(array $filters = [])
    {
        try {
            $query = DB::connection('woocommerce')->table('posts as p')
                ->select([
                    'p.ID as id',
                    'p.post_date as date_created',
                    'p.post_status as status',
                    'p.post_type',
                    'pm.meta_value as order_data'
                ])
                ->join('postmeta as pm', function ($join) {
                    $join->on('p.ID', '=', 'pm.post_id')
                        ->where('pm.meta_key', '=', '_order_data');
                })
                ->where('p.post_type', 'shop_order');

            // Apply filters
            if (isset($filters['order_id'])) {
                $query->where('p.ID', $filters['order_id']);
            }

            if (isset($filters['start_date'])) {
                $query->where('p.post_date', '>=', $filters['start_date']);
            }

            if (isset($filters['end_date'])) {
                $query->where('p.post_date', '<=', $filters['end_date']);
            }

            if (isset($filters['status'])) {
                $query->where('p.post_status', 'wc-' . $filters['status']);
            }

            if (isset($filters['meta_key']) && isset($filters['meta_value'])) {
                $query->join('postmeta as pm2', function ($join) use ($filters) {
                    $join->on('p.ID', '=', 'pm2.post_id')
                        ->where('pm2.meta_key', '=', $filters['meta_key'])
                        ->where('pm2.meta_value', 'LIKE', '%' . $filters['meta_value'] . '%');
                });
            }

            // Get total count for pagination
            $total = $query->count();

            // Pagination
            $perPage = $filters['per_page'] ?? config('woo-order-dashboard.pagination.per_page');
            $page = $filters['page'] ?? 1;

            // Get the paginated results
            $orders = $query->orderBy('p.post_date', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->map(function ($order) {
                    return $this->formatOrder($order);
                });

            // Create a new paginator instance
            $paginator = new LengthAwarePaginator(
                $orders,
                $total,
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );

            return [
                'data' => $paginator,
                'headers' => [
                    'X-WP-Total' => $total,
                    'X-WP-TotalPages' => ceil($total / $perPage)
                ]
            ];
        } catch (\Exception $e) {
            Log::error('WooCommerce Database Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    public function getOrder($id)
    {
        try {
            $order = DB::connection('woocommerce')->table('posts as p')
                ->select([
                    'p.*',
                    'pm.meta_value as order_data'
                ])
                ->join('postmeta as pm', function ($join) {
                    $join->on('p.ID', '=', 'pm.post_id')
                        ->where('pm.meta_key', '=', '_order_data');
                })
                ->where('p.ID', $id)
                ->where('p.post_type', 'shop_order')
                ->first();

            if (!$order) {
                return null;
            }

            return $this->formatOrder($order);
        } catch (\Exception $e) {
            Log::error('WooCommerce Database Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    protected function formatOrder($order)
    {
        // Get order meta data
        $metaData = DB::connection('woocommerce')->table('postmeta')
            ->where('post_id', $order->id)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->meta_key => $item->meta_value];
            });

        // Get order items
        $items = DB::connection('woocommerce')->table('woocommerce_order_items as oi')
            ->select([
                'oi.*',
                'oim.meta_key',
                'oim.meta_value'
            ])
            ->leftJoin('woocommerce_order_itemmeta as oim', 'oi.order_item_id', '=', 'oim.order_item_id')
            ->where('oi.order_id', $order->id)
            ->where('oi.order_item_type', 'line_item')
            ->get()
            ->groupBy('order_item_id')
            ->map(function ($item) {
                $itemData = $item->first();
                $meta = $item->mapWithKeys(function ($meta) {
                    return [$meta->meta_key => $meta->meta_value];
                });

                return [
                    'id' => $itemData->order_item_id,
                    'name' => $itemData->order_item_name,
                    'quantity' => $meta['_qty'] ?? 1,
                    'price' => $meta['_line_total'] ?? 0,
                    'total' => $meta['_line_total'] ?? 0,
                    'sku' => $meta['_sku'] ?? null,
                    'meta_data' => $meta->toArray()
                ];
            });

        // Get order notes
        $notes = DB::connection('woocommerce')->table('comments as c')
            ->select([
                'c.*',
                'cm.meta_value as is_customer_note'
            ])
            ->leftJoin('commentmeta as cm', function ($join) {
                $join->on('c.comment_ID', '=', 'cm.comment_id')
                    ->where('cm.meta_key', '=', 'is_customer_note');
            })
            ->where('c.comment_post_ID', $order->id)
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
            'id' => $order->id,
            'number' => $order->id,
            'status' => str_replace('wc-', '', $order->status),
            'currency' => $metaData['_order_currency'] ?? 'USD',
            'date_created' => $order->date_created,
            'total' => $metaData['_order_total'] ?? 0,
            'subtotal' => $metaData['_order_subtotal'] ?? 0,
            'shipping_total' => $metaData['_order_shipping'] ?? 0,
            'shipping_tax' => $metaData['_order_shipping_tax'] ?? 0,
            'total_tax' => $metaData['_order_tax'] ?? 0,
            'discount_total' => $metaData['_order_discount'] ?? 0,
            'customer_id' => $metaData['_customer_user'] ?? null,
            'customer_note' => $metaData['_customer_note'] ?? null,
            'billing' => [
                'first_name' => $metaData['_billing_first_name'] ?? '',
                'last_name' => $metaData['_billing_last_name'] ?? '',
                'email' => $metaData['_billing_email'] ?? '',
                'phone' => $metaData['_billing_phone'] ?? '',
                'address_1' => $metaData['_billing_address_1'] ?? '',
                'address_2' => $metaData['_billing_address_2'] ?? '',
                'city' => $metaData['_billing_city'] ?? '',
                'state' => $metaData['_billing_state'] ?? '',
                'postcode' => $metaData['_billing_postcode'] ?? '',
                'country' => $metaData['_billing_country'] ?? '',
            ],
            'shipping' => [
                'first_name' => $metaData['_shipping_first_name'] ?? '',
                'last_name' => $metaData['_shipping_last_name'] ?? '',
                'address_1' => $metaData['_shipping_address_1'] ?? '',
                'address_2' => $metaData['_shipping_address_2'] ?? '',
                'city' => $metaData['_shipping_city'] ?? '',
                'state' => $metaData['_shipping_state'] ?? '',
                'postcode' => $metaData['_shipping_postcode'] ?? '',
                'country' => $metaData['_shipping_country'] ?? '',
            ],
            'payment_method' => $metaData['_payment_method'] ?? '',
            'payment_method_title' => $metaData['_payment_method_title'] ?? '',
            'transaction_id' => $metaData['_transaction_id'] ?? null,
            'shipping_method' => $metaData['_shipping_method'] ?? '',
            'line_items' => $items->values()->toArray(),
            'order_notes' => $notes->toArray(),
            'meta_data' => $metaData->toArray()
        ];
    }
}
