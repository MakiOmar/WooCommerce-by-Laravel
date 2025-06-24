@extends('layouts.admin')

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
                        <li class="nav-item">
                            <a class="nav-link active" id="order-info-tab" data-toggle="tab" href="#order-info" role="tab" aria-controls="order-info" aria-selected="true">
                                <i class="fas fa-info-circle mr-1"></i>Order Info & Items
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="customer-info-tab" data-toggle="tab" href="#customer-info" role="tab" aria-controls="customer-info" aria-selected="false">
                                <i class="fas fa-user mr-1"></i>Customer Info
                            </a>
                        </li>
                        <li class="nav-item">
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

@push('css')
<link href="{{ asset('css/woo-order-dashboard.css') }}" rel="stylesheet">
<!-- Bootstrap 4 and jQuery dependencies -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    .nav-tabs {
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 0;
        background-color: #f8f9fa;
        padding: 0 1.25rem;
        padding-top: 1.25rem;
    }
    .nav-tabs .nav-item {
        margin-bottom: -2px;
    }
    .nav-tabs .nav-link {
        color: #495057 !important;
        background-color: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 0.75rem 1.25rem;
        font-weight: 500;
        transition: all 0.2s ease-in-out;
        text-decoration: none;
    }
    .nav-tabs .nav-link:hover {
        color: #007bff !important;
        background-color: transparent;
        border-color: transparent;
        border-bottom-color: #dee2e6;
        text-decoration: none;
    }
    .nav-tabs .nav-link.active {
        color: #007bff !important;
        background-color: #fff;
        border-color: transparent;
        border-bottom-color: #007bff;
        font-weight: 600;
        text-decoration: none;
    }
    .nav-tabs .nav-link.active:hover {
        color: #007bff !important;
        background-color: #fff;
        border-color: transparent;
        border-bottom-color: #007bff;
        text-decoration: none;
    }
    .tab-content {
        background-color: #fff;
        border: 1px solid #e9ecef;
        border-top: none;
        border-radius: 0 0 0.375rem 0.375rem;
        padding: 1.5rem;
        min-height: 400px;
    }
    .tab-pane {
        min-height: 400px;
    }
    .tab-pane.fade {
        opacity: 0;
        transition: opacity 0.15s linear;
    }
    .tab-pane.fade.show {
        opacity: 1;
    }
    .card-body {
        padding: 0;
    }
    .card-body .tab-content {
        margin: 0;
        border-left: none;
        border-right: none;
        border-bottom: none;
        border-radius: 0;
    }
    .nav-tabs .nav-link {
        color: #495057 !important;
        background-color: transparent !important;
    }
    .nav-tabs .nav-link.active {
        color: #007bff !important;
        background-color: #fff !important;
    }
    .nav-tabs .nav-link:hover {
        color: #007bff !important;
        background-color: transparent !important;
    }
    .nav-tabs {
        display: flex !important;
        flex-wrap: wrap !important;
        list-style: none !important;
        margin: 0 !important;
        padding: 0 1.25rem !important;
        padding-top: 1.25rem !important;
        background-color: #f8f9fa !important;
    }
    .nav-tabs .nav-item {
        display: block !important;
    }
    .nav-tabs .nav-link {
        display: block !important;
        padding: 0.75rem 1.25rem !important;
        text-decoration: none !important;
    }
    .tab-content {
        display: block !important;
    }
    .tab-pane {
        display: none !important;
    }
    .tab-pane.show {
        display: block !important;
    }
    .tab-pane.active {
        display: block !important;
    }
</style>
@endpush

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Ensure "Order Info & Items" tab is always active by default
    $('#orderTabs a[href="#order-info"]').tab('show');
});
</script>
@endpush 