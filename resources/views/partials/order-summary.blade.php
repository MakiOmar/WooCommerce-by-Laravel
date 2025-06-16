<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <x-order.section title="Customer Information">
        <x-order.detail label="Name" :value="$order['billing']['first_name'] . ' ' . $order['billing']['last_name']" />
        <x-order.detail label="Email" :value="$order['billing']['email']" />
        <x-order.detail label="Phone" :value="$order['billing']['phone']" />
        @if($order['customer_id'])
            <x-order.detail label="Customer ID" :value="$order['customer_id']" />
        @endif
    </x-order.section>

    <x-order.section title="Order Information">
        <x-order.detail label="Order Number" :value="$order['id']" />
        <x-order.detail label="Date" :value="\Carbon\Carbon::parse($order['date_created'])->format(config('woo-order-dashboard.date_format.display'))" />
        <x-order.detail label="Payment Method" :value="$order['payment_method_title']" />
        <x-order.detail label="Transaction ID" :value="$order['transaction_id'] ?? 'N/A'" />
        <x-order.detail label="Currency" :value="$order['currency']" />
    </x-order.section>

    <x-order.section title="Shipping Information">
        <x-order.detail label="Method" :value="$order['shipping_method'] ?? 'N/A'" />
        <x-order.detail label="Cost" :value="$order['shipping_total']" />
        <x-order.detail label="Tax" :value="$order['shipping_tax']" />
    </x-order.section>
</div>

