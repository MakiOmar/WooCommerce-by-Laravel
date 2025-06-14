@extends('woo-order-dashboard::layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Order #{{ $order['id'] }}</h1>
            <a href="{{ route('woo.orders') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Back to Orders
            </a>
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Customer Information -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-4">Customer Information</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Name:</span> {{ $order['billing']['first_name'] }} {{ $order['billing']['last_name'] }}</p>
                    <p><span class="font-medium">Email:</span> {{ $order['billing']['email'] }}</p>
                    <p><span class="font-medium">Phone:</span> {{ $order['billing']['phone'] }}</p>
                </div>
            </div>

            <!-- Order Information -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-4">Order Information</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Date:</span> {{ \Carbon\Carbon::parse($order['date_created'])->format(config('woo-order-dashboard.date_format.display')) }}</p>
                    <p><span class="font-medium">Payment Method:</span> {{ $order['payment_method_title'] }}</p>
                    <p><span class="font-medium">Total:</span> {{ $order['total'] }}</p>
                </div>
            </div>
        </div>

        <!-- Billing Address -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold mb-4">Billing Address</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <address class="not-italic">
                    {{ $order['billing']['first_name'] }} {{ $order['billing']['last_name'] }}<br>
                    {{ $order['billing']['address_1'] }}<br>
                    @if($order['billing']['address_2'])
                        {{ $order['billing']['address_2'] }}<br>
                    @endif
                    {{ $order['billing']['city'] }}, {{ $order['billing']['state'] }} {{ $order['billing']['postcode'] }}<br>
                    {{ $order['billing']['country'] }}
                </address>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['quantity'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['price'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['total'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Subtotal</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order['subtotal'] }}</td>
                        </tr>
                        @if($order['shipping_total'] > 0)
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Shipping</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order['shipping_total'] }}</td>
                        </tr>
                        @endif
                        @if($order['total_tax'] > 0)
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Tax</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order['total_tax'] }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Total</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $order['total'] }}</td>
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
@endsection 