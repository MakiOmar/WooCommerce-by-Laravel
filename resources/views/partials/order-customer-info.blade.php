<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-user mr-2"></i>Customer Information
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-address-card mr-2"></i>Billing Address
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>{{ $order['billing']['first_name'] }} {{ $order['billing']['last_name'] }}</strong>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-envelope text-primary mr-2"></i>
                            <a href="mailto:{{ $order['billing']['email'] }}" class="text-decoration-none">
                                {{ $order['billing']['email'] }}
                            </a>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-phone text-primary mr-2"></i>
                            <a href="tel:{{ $order['billing']['phone'] }}" class="text-decoration-none">
                                {{ $order['billing']['phone'] }}
                            </a>
                        </div>
                        <div class="text-muted">
                            {{ $order['billing']['address_1'] }}
                            @if($order['billing']['address_2'])
                                <br>{{ $order['billing']['address_2'] }}
                            @endif
                            <br>{{ $order['billing']['city'] }}, {{ $order['billing']['state'] }} {{ $order['billing']['postcode'] }}
                            <br>{{ $order['billing']['country'] }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-shipping-fast mr-2"></i>Shipping Address
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>{{ $order['shipping']['first_name'] }} {{ $order['shipping']['last_name'] }}</strong>
                        </div>
                        <div class="text-muted">
                            {{ $order['shipping']['address_1'] }}
                            @if($order['shipping']['address_2'])
                                <br>{{ $order['shipping']['address_2'] }}
                            @endif
                            <br>{{ $order['shipping']['city'] }}, {{ $order['shipping']['state'] }} {{ $order['shipping']['postcode'] }}
                            <br>{{ $order['shipping']['country'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(isset($order['customer_note']) && $order['customer_note'])
        <div class="alert alert-info mb-0">
            <i class="fas fa-sticky-note mr-2"></i>
            <strong>Customer Note:</strong>
            <p class="mb-0 mt-2">{{ $order['customer_note'] }}</p>
        </div>
        @endif
    </div>
</div> 