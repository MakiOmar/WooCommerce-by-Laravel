<?php

namespace Makiomar\WooOrderDashboard\Tests\Feature;

use Tests\TestCase;
use Makiomar\WooOrderDashboard\Services\WooCommerceStockService;
use Makiomar\WooOrderDashboard\Services\WooCommerceOrderNotesService;
use Makiomar\WooOrderDashboard\Services\WooCommerceCacheService;
use Makiomar\WooOrderDashboard\Services\WooCommerceOrderManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WooCommerceOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected WooCommerceStockService $stockService;
    protected WooCommerceOrderNotesService $notesService;
    protected WooCommerceCacheService $cacheService;
    protected WooCommerceOrderManagementService $orderManagementService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockService = app(WooCommerceStockService::class);
        $this->notesService = app(WooCommerceOrderNotesService::class);
        $this->cacheService = app(WooCommerceCacheService::class);
        $this->orderManagementService = app(WooCommerceOrderManagementService::class);
    }

    /** @test */
    public function it_can_get_stock_reducing_statuses()
    {
        $statuses = $this->stockService->getStockReducingStatuses();
        
        $this->assertIsArray($statuses);
        $this->assertContains('on-hold', $statuses);
        $this->assertContains('processing', $statuses);
        $this->assertContains('completed', $statuses);
    }

    /** @test */
    public function it_can_get_stock_restoring_statuses()
    {
        $statuses = $this->stockService->getStockRestoringStatuses();
        
        $this->assertIsArray($statuses);
        $this->assertContains('cancelled', $statuses);
        $this->assertContains('refunded', $statuses);
        $this->assertContains('failed', $statuses);
    }

    /** @test */
    public function it_can_clear_cache()
    {
        $result = $this->cacheService->clearOrderCache();
        
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_clear_all_woocommerce_cache()
    {
        $result = $this->cacheService->clearAllWooCommerceCache();
        
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_get_order_note_count()
    {
        // This test assumes no notes exist for order 999999
        $count = $this->notesService->getOrderNoteCount(999999);
        
        $this->assertIsInt($count);
        $this->assertEquals(0, $count);
    }

    /** @test */
    public function it_can_check_if_order_can_be_cancelled()
    {
        $canCancel = $this->orderManagementService->canCancelOrder(999999);
        
        $this->assertIsBool($canCancel);
    }

    /** @test */
    public function it_can_check_if_order_can_be_refunded()
    {
        $canRefund = $this->orderManagementService->canRefundOrder(999999);
        
        $this->assertIsBool($canRefund);
    }

    /** @test */
    public function it_can_get_order_summary()
    {
        $summary = $this->orderManagementService->getOrderSummary(999999);
        
        $this->assertIsArray($summary);
        $this->assertEmpty($summary); // Should be empty for non-existent order
    }

    /** @test */
    public function it_can_get_formatted_order_notes()
    {
        $notes = $this->orderManagementService->getOrderNotesFormatted(999999);
        
        $this->assertIsArray($notes);
        $this->assertEmpty($notes); // Should be empty for non-existent order
    }

    /** @test */
    public function it_can_get_order_status_history()
    {
        $history = $this->orderManagementService->getOrderStatusHistory(999999);
        
        $this->assertIsArray($history);
        $this->assertEmpty($history); // Should be empty for non-existent order
    }
} 