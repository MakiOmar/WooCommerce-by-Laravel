<?php

namespace Makiomar\WooOrderDashboard\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WooCommerceService
{
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

            return ['data' => collect(), 'headers' => []];
        }
    }

    public function getOrder($id)
    {
        try {
            $order = DB::connection('woocommerce')->table('posts')
                ->where('ID', $id)
                ->where('post_type', 'shop_order')
                ->first();

            return $order ? $this->formatOrderFull($order) : null;
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
            'status' => str_replace('wc-', '', $order->post_status),
            'date_created' => $order->post_date,
            'total' => $meta['_order_total'] ?? 0,
            'billing' => [
                'first_name' => $meta['_billing_first_name'] ?? '',
                'last_name' => $meta['_billing_last_name'] ?? '',
                'phone' => $meta['_billing_phone'] ?? ''
            ]
        ];
    }

    protected function formatOrderFull($order)
    {
        $meta = DB::connection('woocommerce')->table('postmeta')
            ->where('post_id', $order->id)
            ->pluck('meta_value', 'meta_key');

        return [
            'id' => $order->id,
            'status' => str_replace('wc-', '', $order->post_status),
            'date_created' => $order->post_date,
            'total' => $meta['_order_total'] ?? 0,
            'currency' => $meta['_order_currency'] ?? 'USD',
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
            'meta_data' => $meta->toArray()
        ];
    }
}
