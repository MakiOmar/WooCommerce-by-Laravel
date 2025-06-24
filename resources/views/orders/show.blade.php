@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Order #{{ $order->ID }}</h3>
                    <div>
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                        @if($order->post_status !== 'completed')
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="fas fa-print"></i> Print
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs" id="orderTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="order-info-tab" data-toggle="tab" href="#order-info" role="tab" aria-controls="order-info" aria-selected="true">
                                <i class="fas fa-info-circle mr-1"></i>Order Info & Items
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="customer-info-tab" data-toggle="tab" href="#customer-info" role="tab" aria-controls="customer-info" aria-selected="false">
                                <i class="fas fa-user mr-1"></i>Customer Info
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="order-notes-tab" data-toggle="tab" href="#order-notes" role="tab" aria-controls="order-notes" aria-selected="false">
                                <i class="fas fa-sticky-note mr-1"></i>Order Notes
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="orderTabsContent">
                        <!-- Tab 1: Order Info & Items -->
                        <div class="tab-pane fade show active" id="order-info" role="tabpanel" aria-labelledby="order-info-tab">
                            <div class="mt-4">
                                @include('woo-order-dashboard::partials.order-items', ['order' => $order])
                                @include('woo-order-dashboard::partials.order-meta', ['order' => $order])
                            </div>
                        </div>

                        <!-- Tab 2: Customer Info -->
                        <div class="tab-pane fade" id="customer-info" role="tabpanel" aria-labelledby="customer-info-tab">
                            <div class="mt-4">
                                @include('woo-order-dashboard::partials.order-customer-info', ['order' => $order])
                            </div>
                        </div>

                        <!-- Tab 3: Order Notes -->
                        <div class="tab-pane fade" id="order-notes" role="tabpanel" aria-labelledby="order-notes-tab">
                            <div class="mt-4">
                                @include('woo-order-dashboard::partials.order-notes', ['order' => $order])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('css/woo-order-dashboard.css') }}" rel="stylesheet">
<style>
    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 0.75rem 1rem;
        font-weight: 500;
    }
    
    .nav-tabs .nav-link:hover {
        color: #495057;
        border-color: transparent;
        background-color: transparent;
    }
    
    .nav-tabs .nav-link.active {
        color: #007bff;
        background-color: transparent;
        border-color: transparent;
        border-bottom-color: #007bff;
    }
    
    .tab-content {
        padding-top: 1rem;
    }
    
    .tab-pane {
        min-height: 400px;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle tab switching
    $('#orderTabs a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // Store active tab in localStorage for persistence
    $('#orderTabs a').on('shown.bs.tab', function (e) {
        localStorage.setItem('activeOrderTab', $(e.target).attr('href'));
    });
    
    // Restore active tab on page load
    var activeTab = localStorage.getItem('activeOrderTab');
    if (activeTab) {
        $('#orderTabs a[href="' + activeTab + '"]').tab('show');
    }
});
</script>
@endpush 