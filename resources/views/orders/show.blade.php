@extends('woo-order-dashboard::layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold">Order #{{ $order['id'] }}</h1>
                <p class="text-gray-500">Created on {{ \Carbon\Carbon::parse($order['date_created'])->format(config('woo-order-dashboard.date_format.display')) }}</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('woo.orders') }}" class="button-secondary">Back to Orders</a>
                @if($order['status'] !== 'completed')
                    <button onclick="window.print()" class="button-secondary">Print Order</button>
                @endif
            </div>
        </div>

        <!-- Order Status -->
        <div class="mb-6">
            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                {{ $order['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                   ($order['status'] == 'processing' ? 'bg-blue-100 text-blue-800' : 
                   ($order['status'] == 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                {{ ucfirst($order['status']) }}
            </span>
        </div>

        <!-- Order Summary + Addresses -->
        @include('woo-order-dashboard::partials.order-summary')
        @include('woo-order-dashboard::partials.order-addresses')

        <!-- Order Items -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold mb-4">Order Items</h2>
            @include('woo-order-dashboard::partials.order-items')
        </div>

        <!-- Order Notes -->
        @include('woo-order-dashboard::partials.order-notes')

        <!-- Additional Meta -->
        @include('woo-order-dashboard::partials.order-meta')
    </div>
</div>
@endsection
