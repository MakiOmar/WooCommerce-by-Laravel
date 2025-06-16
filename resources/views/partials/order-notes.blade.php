@if(isset($order['order_notes']) && count($order['order_notes']) > 0)
    <div class="mb-8">
        <h2 class="text-lg font-semibold mb-4">Order Notes</h2>
        <div class="space-y-4">
            @foreach($order['order_notes'] as $note)
                <div class="p-3 bg-gray-100 rounded">
                    <p class="text-sm text-gray-800">{{ $note['note'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">By {{ $note['added_by'] }} on {{ \Carbon\Carbon::parse($note['date_created'])->format(config('woo-order-dashboard.date_format.display')) }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endif