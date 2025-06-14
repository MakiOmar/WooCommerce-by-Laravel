<?php

namespace Makiomar\WooOrderDashboard\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WooCommerceService
{
    protected $baseUrl;
    protected $consumerKey;
    protected $consumerSecret;
    protected $apiVersion;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('woo-order-dashboard.store_url'), '/');
        $this->consumerKey = config('woo-order-dashboard.consumer_key');
        $this->consumerSecret = config('woo-order-dashboard.consumer_secret');
        $this->apiVersion = config('woo-order-dashboard.api.version');
    }

    public function getOrders(array $filters = [])
    {
        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->timeout(config('woo-order-dashboard.api.timeout'))
                ->get("{$this->baseUrl}/wp-json/{$this->apiVersion}/orders", $this->prepareFilters($filters));

            if ($response->successful()) {
                return [
                    'data' => $response->json(),
                    'headers' => $response->headers(),
                ];
            }

            Log::error('WooCommerce API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('WooCommerce API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    public function getOrder($id)
    {
        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->timeout(config('woo-order-dashboard.api.timeout'))
                ->get("{$this->baseUrl}/wp-json/{$this->apiVersion}/orders/{$id}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('WooCommerce API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('WooCommerce API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    protected function prepareFilters(array $filters)
    {
        $preparedFilters = [];

        if (isset($filters['order_id'])) {
            $preparedFilters['id'] = $filters['order_id'];
        }

        if (isset($filters['start_date'])) {
            $preparedFilters['after'] = $filters['start_date'];
        }

        if (isset($filters['end_date'])) {
            $preparedFilters['before'] = $filters['end_date'];
        }

        if (isset($filters['status'])) {
            $preparedFilters['status'] = $filters['status'];
        }

        if (isset($filters['meta_key']) && isset($filters['meta_value'])) {
            $preparedFilters['meta_data'] = [
                [
                    'key' => $filters['meta_key'],
                    'value' => $filters['meta_value'],
                ],
            ];
        }

        $preparedFilters['per_page'] = $filters['per_page'] ?? config('woo-order-dashboard.pagination.per_page');
        $preparedFilters['page'] = $filters['page'] ?? 1;

        return $preparedFilters;
    }
} 