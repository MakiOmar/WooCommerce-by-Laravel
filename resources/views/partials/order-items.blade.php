@include('woo-order-dashboard::partials.order-summary')
@include('woo-order-dashboard::partials.order-addresses')

<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach($order['line_items'] as $item)
        <tr>
            <td class="px-6 py-4 text-sm text-gray-900">{{ $item['name'] }}</td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $item['sku'] ?? 'N/A' }}</td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $item['quantity'] }}</td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $order['currency'] }} {{ $item['price'] }}</td>
            <td class="px-6 py-4 text-sm text-gray-900">{{ $order['currency'] }} {{ $item['total'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>