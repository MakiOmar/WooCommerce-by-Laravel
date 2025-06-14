@extends('woo-order-dashboard::layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold">Order #{{ $order['id'] }}</h1>
                <p class="text-gray-500">Created on {{ \Carbon\Carbon::parse($order['date_created'])->format(config('woo-order-dashboard.date_format.display')) }}</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('woo.orders') }}"
                    class="button-secondary">
                    Back to Orders
                </a>
                @if($order['status'] !== 'completed')
                <button onclick="window.print()" class="button-secondary">
                    Print Order
                </button>
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

        <!-- Order Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Customer Information -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-4">Customer Information</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Name:</span> {{ $order['billing']['first_name'] }} {{ $order['billing']['last_name'] }}</p>
                    <p><span class="font-medium">Email:</span> {{ $order['billing']['email'] }}</p>
                    <p><span class="font-medium">Phone:</span> {{ $order['billing']['phone'] }}</p>
                    @if($order['customer_id'])
                        <p><span class="font-medium">Customer ID:</span> {{ $order['customer_id'] }}</p>
                    @endif
                </div>
            </div>

            <!-- Order Information -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-4">Order Information</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Order Number:</span> {{ $order['number'] }}</p>
                    <p><span class="font-medium">Date:</span> {{ \Carbon\Carbon::parse($order['date_created'])->format(config('woo-order-dashboard.date_format.display')) }}</p>
                    <p><span class="font-medium">Payment Method:</span> {{ $order['payment_method_title'] }}</p>
                    <p><span class="font-medium">Transaction ID:</span> {{ $order['transaction_id'] ?? 'N/A' }}</p>
                    <p><span class="font-medium">Currency:</span> {{ $order['currency'] }}</p>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-4">Shipping Information</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Method:</span> {{ $order['shipping_method'] ?? 'N/A' }}</p>
                    <p><span class="font-medium">Cost:</span> {{ $order['shipping_total'] }}</p>
                    <p><span class="font-medium">Tax:</span> {{ $order['shipping_tax'] }}</p>
                </div>
            </div>
        </div>

        <!-- Addresses Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Billing Address -->
            <div>
                <h2 class="text-lg font-semibold mb-4">Billing Address</h2>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <address class="not-italic">
                        {{ $order['billing']['first_name'] }} {{ $order['billing']['last_name'] }}<br>
                        {{ $order['billing']['address_1'] }}<br>
                        @if($order['billing']['address_2'])
                            {{ $order['billing']['address_2'] }}<br>
                        @endif
                        {{ $order['billing']['city'] }}, {{ $order['billing']['state'] }} {{ $order['billing']['postcode'] }}<br>
                        {{ $order['billing']['country'] }}<br>
                        @if($order['billing']['email'])
                            <br>Email: {{ $order['billing']['email'] }}<br>
                        @endif
                        @if($order['billing']['phone'])
                            Phone: {{ $order['billing']['phone'] }}
                        @endif
                    </address>
                </div>
            </div>

            <!-- Shipping Address -->
            <div>
                <h2 class="text-lg font-semibold mb-4">Shipping Address</h2>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <address class="not-italic">
                        {{ $order['shipping']['first_name'] }} {{ $order['shipping']['last_name'] }}<br>
                        {{ $order['shipping']['address_1'] }}<br>
                        @if($order['shipping']['address_2'])
                            {{ $order['shipping']['address_2'] }}<br>
                        @endif
                        {{ $order['shipping']['city'] }}, {{ $order['shipping']['state'] }} {{ $order['shipping']['postcode'] }}<br>
                        {{ $order['shipping']['country'] }}
                    </address>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold mb-4">Order Items</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($order['line_items'] as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item['name'] }}
                                @if($item['meta_data'])
                                    <div class="text-xs text-gray-500">
                                        @foreach($item['meta_data'] as $meta)
                                            @if($meta['display_key'] && $meta['display_value'])
                                                <div>{{ $meta['display_key'] }}: {{ $meta['display_value'] }}</div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['sku'] ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['quantity'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order['currency'] }} {{ $item['price'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order['currency'] }} {{ $item['total'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Subtotal</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order['currency'] }} {{ $order['subtotal'] }}</td>
                        </tr>
                        @if($order['shipping_total'] > 0)
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Shipping</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order['currency'] }} {{ $order['shipping_total'] }}</td>
                        </tr>
                        @endif
                        @if($order['total_tax'] > 0)
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Tax</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order['currency'] }} {{ $order['total_tax'] }}</td>
                        </tr>
                        @endif
                        @if($order['discount_total'] > 0)
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Discount</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-{{ $order['currency'] }} {{ $order['discount_total'] }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Total</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $order['currency'] }} {{ $order['total'] }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Order Notes -->
        @if($order['customer_note'])
        <div class="mb-8">
            <h2 class="text-lg font-semibold mb-4">Customer Note</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-700">{{ $order['customer_note'] }}</p>
            </div>
        </div>
        @endif

        <!-- Order Notes History -->
        @if(isset($order['order_notes']) && count($order['order_notes']) > 0)
        <div class="mb-8">
            <h2 class="text-lg font-semibold mb-4">Order Notes History</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="space-y-4">
                    @foreach($order['order_notes'] as $note)
                    <div class="border-b border-gray-200 pb-4 last:border-0 last:pb-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-900">{{ $note['note'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    By {{ $note['added_by'] }} on {{ \Carbon\Carbon::parse($note['date_created'])->format(config('woo-order-dashboard.date_format.display')) }}
                                </p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $note['is_customer_note'] ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $note['is_customer_note'] ? 'Customer Note' : 'Private Note' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Meta Data -->
        @if($order['meta_data'])
        <div>
            <h2 class="text-lg font-semibold mb-4">Additional Information</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($order['meta_data'] as $meta)
                        @if($meta['display_key'] && $meta['display_value'])
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ $meta['display_key'] }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $meta['display_value'] }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    @media print {
        .button-secondary, .button-primary {
            display: none;
        }
    }
</style>
@endpush
@endsection 