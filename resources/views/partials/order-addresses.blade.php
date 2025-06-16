<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Billing Address</h5>
            </div>
            <div class="card-body">
                <address class="mb-0">
                    {{ $order['billing']['first_name'] }} {{ $order['billing']['last_name'] }}<br>
                    @if(!empty($order['billing']['company']))
                        {{ $order['billing']['company'] }}<br>
                    @endif
                    {{ $order['billing']['address_1'] }}<br>
                    @if(!empty($order['billing']['address_2']))
                        {{ $order['billing']['address_2'] }}<br>
                    @endif
                    {{ $order['billing']['city'] }}, {{ $order['billing']['state'] }} {{ $order['billing']['postcode'] }}<br>
                    {{ $order['billing']['country'] }}<br>
                    @if(!empty($order['billing']['email']))
                        <a href="mailto:{{ $order['billing']['email'] }}">{{ $order['billing']['email'] }}</a><br>
                    @endif
                    @if(!empty($order['billing']['phone']))
                        <a href="tel:{{ $order['billing']['phone'] }}">{{ $order['billing']['phone'] }}</a>
                    @endif
                </address>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Shipping Address</h5>
            </div>
            <div class="card-body">
                <address class="mb-0">
                    {{ $order['shipping']['first_name'] }} {{ $order['shipping']['last_name'] }}<br>
                    @if(!empty($order['shipping']['company']))
                        {{ $order['shipping']['company'] }}<br>
                    @endif
                    {{ $order['shipping']['address_1'] }}<br>
                    @if(!empty($order['shipping']['address_2']))
                        {{ $order['shipping']['address_2'] }}<br>
                    @endif
                    {{ $order['shipping']['city'] }}, {{ $order['shipping']['state'] }} {{ $order['shipping']['postcode'] }}<br>
                    {{ $order['shipping']['country'] }}
                </address>
            </div>
        </div>
    </div>
</div>
