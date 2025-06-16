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
            $query = DB::connection('woocommerce')->table('posts as p')
                ->select(['p.ID as id', 'p.post_date', 'p.post_status'])
                ->where('p.post_type', 'shop_order');

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

            $total = $query->count();
            $perPage = $filters['per_page'] ?? config('woo-order-dashboard.pagination.per_page');
            $page = $filters['page'] ?? 1;

            $orders = $query->orderByDesc('p.post_date')
                ->forPage($page, $perPage)
                ->get()
                ->map(fn($order) => $this->formatOrderSummary($order));

            $paginator = new LengthAwarePaginator(
                $orders,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            return [
                'data' => $paginator,
                'pagination' => [
                    'total' => $total,
                    'pages' => ceil($total / $perPage),
                ]
            ];
        } catch (\Exception $e) {
            Log::error('WooCommerce Orders Query Failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ['data' => collect(), 'pagination' => []];
        }
    }

    public function getOrder($id)
    {
        try {
            $order = DB::connection('woocommerce')->table('posts')
                ->where('ID', $id)
                ->where('post_type', 'shop_order')
                ->first();

            if (!$order) {
                return null;
            }

            $meta = DB::connection('woocommerce')->table('postmeta')
                ->where('post_id', $order->ID)
                ->pluck('meta_value', 'meta_key');

            $itemMetas = DB::connection('woocommerce')
                ->table('woocommerce_order_itemmeta')
                ->get()
                ->groupBy('order_item_id');

            $lineItems = DB::connection('woocommerce')
                ->table('woocommerce_order_items as oi')
                ->select(['oi.order_item_id', 'oi.order_item_name'])
                ->where('oi.order_id', $order->ID)
                ->where('oi.order_item_type', 'line_item')
                ->get()
                ->map(function ($item) use ($itemMetas) {
                    $meta = $itemMetas[$item->order_item_id]->pluck('meta_value', 'meta_key') ?? collect();

                    return [
                        'id' => $item->order_item_id,
                        'name' => $item->order_item_name,
                        'sku' => $meta['_sku'] ?? '',
                        'quantity' => $meta['_qty'] ?? 1,
                        'price' => $meta['_line_total'] ?? 0,
                        'total' => $meta['_line_total'] ?? 0,
                        'meta_data' => $meta->map(function ($value, $key) {
                            return [
                                'display_key' => $key,
                                'display_value' => $value
                            ];
                        })->values()->toArray()
                    ];
                });

            $notes = DB::connection('woocommerce')
                ->table('comments as c')
                ->leftJoin('commentmeta as cm', 'c.comment_ID', '=', 'cm.comment_id')
                ->select(['c.comment_ID', 'c.comment_content', 'c.comment_date', 'c.comment_author', 'cm.meta_value as is_customer_note'])
                ->where('c.comment_post_ID', $order->ID)
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
                'id' => $order->ID,
                'status' => $this->cleanStatus($order->post_status),
                'date_created' => $order->post_date,
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
                'line_items' => $lineItems,
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

    protected function formatOrderSummary($order)
    {
        $meta = DB::connection('woocommerce')->table('postmeta')
            ->where('post_id', $order->id)
            ->whereIn('meta_key', [
                '_billing_first_name',
                '_billing_last_name',
                '_billing_phone',
                '_order_total'
            ])->pluck('meta_value', 'meta_key');

        return [
            'id' => $order->id,
            'status' => $this->cleanStatus($order->post_status),
            'date_created' => $order->post_date,
            'total' => $meta['_order_total'] ?? 0,
            'billing' => [
                'first_name' => $meta['_billing_first_name'] ?? '',
                'last_name' => $meta['_billing_last_name'] ?? '',
                'phone' => $meta['_billing_phone'] ?? ''
            ]
        ];
    }
}
