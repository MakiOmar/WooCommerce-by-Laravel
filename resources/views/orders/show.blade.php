@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Order #{{ $order->ID }}</h3>
                    <div>
                        <a href="{{ route('woo.orders') }}" class="btn btn-secondary">
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
                    @include('woo-order-dashboard::partials.order-info', ['order' => $order])
                    @include('woo-order-dashboard::partials.order-customer-info', ['order' => $order])
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
<link href="{{ asset('css/woo-order-dashboard.css') }}" rel="stylesheet">
@endpush 