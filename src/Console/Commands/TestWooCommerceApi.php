<?php

namespace Makiomar\WooOrderDashboard\Console\Commands;

use Illuminate\Console\Command;
use Makiomar\WooOrderDashboard\Services\WooCommerceApiService;

class TestWooCommerceApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'woo:test-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test WooCommerce API connection';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing WooCommerce API connection...');
        
        // Check if API is enabled
        if (!config('woo-order-dashboard.api.enabled', false)) {
            $this->error('WooCommerce API is not enabled. Set WOO_API_ENABLED=true in your .env file.');
            return 1;
        }
        
        // Check required configuration
        $requiredConfig = ['site_url', 'consumer_key', 'consumer_secret'];
        $missingConfig = [];
        
        foreach ($requiredConfig as $config) {
            if (empty(config('woo-order-dashboard.api.' . $config))) {
                $missingConfig[] = $config;
            }
        }
        
        if (!empty($missingConfig)) {
            $this->error('Missing required API configuration: ' . implode(', ', $missingConfig));
            $this->line('Please set the following environment variables:');
            foreach ($missingConfig as $config) {
                $this->line('- WOO_' . strtoupper($config));
            }
            return 1;
        }
        
        try {
            $apiService = new WooCommerceApiService();
            
            $this->info('Testing connection to: ' . config('woo-order-dashboard.api.site_url'));
            
            if ($apiService->testConnection()) {
                $this->info('✅ WooCommerce API connection successful!');
                $this->info('API is ready to use for order creation.');
                return 0;
            } else {
                $this->error('❌ WooCommerce API connection failed!');
                $this->line('Please check your API credentials and site URL.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
            return 1;
        }
    }
} 