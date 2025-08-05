<?php

namespace Makiomar\WooOrderDashboard\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RedBoxService
{
    protected $config;
    protected $apiKey;
    protected $baseUrl;
    protected $timeout;

    public function __construct()
    {
        $this->config = config('woo-order-dashboard.redbox');
        $this->apiKey = $this->config['api']['key'];
        $this->baseUrl = $this->config['api']['base_url'];
        $this->timeout = $this->config['api']['timeout'];
    }

    /**
     * Check if RedBox is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'] && !empty($this->apiKey);
    }

    /**
     * Get pickup points from RedBox API
     */
    public function getPickupPoints(float $lat, float $lng, int $distance = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'RedBox is not enabled'];
        }

        $distance = $distance ?? $this->config['map']['search_radius'];
        $endpoint = $this->config['endpoints']['get_points'];
        $url = $this->baseUrl . $endpoint;

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->get($url, [
                    'lat' => $lat,
                    'lng' => $lng,
                    'distance' => $distance,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('RedBox API: Successfully retrieved pickup points', [
                    'lat' => $lat,
                    'lng' => $lng,
                    'count' => count($data['points'] ?? [])
                ]);
                return $data;
            } else {
                Log::error('RedBox API: Failed to get pickup points', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return ['success' => false, 'message' => 'Failed to retrieve pickup points'];
            }
        } catch (\Exception $e) {
            Log::error('RedBox API: Exception while getting pickup points', [
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Network error: ' . $e->getMessage()];
        }
    }

    /**
     * Get Apple Maps token
     */
    public function getMapToken(): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'RedBox is not enabled'];
        }

        $endpoint = $this->config['endpoints']['get_map_token'];
        $url = $this->baseUrl . $endpoint;

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['token'])) {
                    Log::info('RedBox API: Successfully retrieved map token');
                    return ['success' => true, 'token' => $data['token']];
                } else {
                    Log::error('RedBox API: No token in response', ['response' => $data]);
                    return ['success' => false, 'message' => 'No token received'];
                }
            } else {
                Log::error('RedBox API: Failed to get map token', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return ['success' => false, 'message' => 'Failed to get map token'];
            }
        } catch (\Exception $e) {
            Log::error('RedBox API: Exception while getting map token', [
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Network error: ' . $e->getMessage()];
        }
    }

    /**
     * Create shipment in RedBox
     */
    public function createShipment(array $orderData): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'RedBox is not enabled'];
        }

        $endpoint = $this->config['endpoints']['create_shipment'];
        $url = $this->baseUrl . $endpoint;

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $orderData);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('RedBox API: Successfully created shipment', [
                    'order_id' => $orderData['reference'] ?? 'unknown',
                    'tracking_number' => $data['tracking_number'] ?? 'unknown'
                ]);
                return $data;
            } else {
                Log::error('RedBox API: Failed to create shipment', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'order_data' => $orderData
                ]);
                return ['success' => false, 'message' => 'Failed to create shipment'];
            }
        } catch (\Exception $e) {
            Log::error('RedBox API: Exception while creating shipment', [
                'error' => $e->getMessage(),
                'order_data' => $orderData
            ]);
            return ['success' => false, 'message' => 'Network error: ' . $e->getMessage()];
        }
    }

    /**
     * Update shipment in RedBox
     */
    public function updateShipment(array $orderData): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'RedBox is not enabled'];
        }

        $endpoint = $this->config['endpoints']['update_shipment'];
        $url = $this->baseUrl . $endpoint;

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $orderData);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('RedBox API: Successfully updated shipment', [
                    'order_id' => $orderData['reference'] ?? 'unknown'
                ]);
                return $data;
            } else {
                Log::error('RedBox API: Failed to update shipment', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'order_data' => $orderData
                ]);
                return ['success' => false, 'message' => 'Failed to update shipment'];
            }
        } catch (\Exception $e) {
            Log::error('RedBox API: Exception while updating shipment', [
                'error' => $e->getMessage(),
                'order_data' => $orderData
            ]);
            return ['success' => false, 'message' => 'Network error: ' . $e->getMessage()];
        }
    }

    /**
     * Save store information to RedBox
     */
    public function saveStoreInfo(array $storeData): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'RedBox is not enabled'];
        }

        $endpoint = $this->config['endpoints']['save_store_info'];
        $url = $this->baseUrl . $endpoint;

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $storeData);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('RedBox API: Successfully saved store info');
                return $data;
            } else {
                Log::error('RedBox API: Failed to save store info', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return ['success' => false, 'message' => 'Failed to save store info'];
            }
        } catch (\Exception $e) {
            Log::error('RedBox API: Exception while saving store info', [
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Network error: ' . $e->getMessage()];
        }
    }

    /**
     * Get shipment details from RedBox
     */
    public function getShipmentDetail(string $trackingNumber): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'RedBox is not enabled'];
        }

        $endpoint = $this->config['endpoints']['shipment_detail'];
        $url = $this->baseUrl . $endpoint;

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->get($url, ['tracking_number' => $trackingNumber]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('RedBox API: Successfully retrieved shipment details', [
                    'tracking_number' => $trackingNumber
                ]);
                return $data;
            } else {
                Log::error('RedBox API: Failed to get shipment details', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return ['success' => false, 'message' => 'Failed to get shipment details'];
            }
        } catch (\Exception $e) {
            Log::error('RedBox API: Exception while getting shipment details', [
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Network error: ' . $e->getMessage()];
        }
    }

    /**
     * Get configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get map configuration
     */
    public function getMapConfig(): array
    {
        return $this->config['map'];
    }

    /**
     * Get shipping method configuration
     */
    public function getShippingMethodConfig(): array
    {
        return $this->config['shipping_method'];
    }

    /**
     * Get meta keys configuration
     */
    public function getMetaKeysConfig(): array
    {
        return $this->config['meta_keys'];
    }
} 