<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
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
                @if($order['billing']['email'])<br>Email: {{ $order['billing']['email'] }}<br>@endif
                @if($order['billing']['phone'])Phone: {{ $order['billing']['phone'] }}@endif
            </address>
        </div>
    </div>

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
