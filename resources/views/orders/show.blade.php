@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Order #{{ $order['id'] }}</h3>
                    <div>
                        <a href="{{ route('woo.orders') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                        @if($order['status'] !== 'completed')
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="fas fa-print"></i> Print
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @include('woo-order-dashboard::partials.order-info', ['order' => $order])
                    @include('woo-order-dashboard::partials.order-customer-info', ['order' => $order])
                    @include('woo-order-dashboard::partials.order-addresses', ['order' => $order])
                    @include('woo-order-dashboard::partials.order-items', ['order' => $order])
                    @include('woo-order-dashboard::partials.order-meta', ['order' => $order])
                    @include('woo-order-dashboard::partials.order-notes', ['order' => $order])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .btn, .card-header .btn {
            display: none !important;
        }
        .card {
            border: none !important;
        }
        .card-header {
            background: none !important;
            border-bottom: 1px solid #ddd !important;
        }
    }
    .timeline {
        position: relative;
        padding: 20px 0;
    }
    .timeline-item {
        position: relative;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    .timeline-header {
        margin-bottom: 10px;
    }
    .timeline-body {
        margin-bottom: 10px;
    }
    .timeline-footer {
        margin-top: 10px;
    }
</style>
@endpush 