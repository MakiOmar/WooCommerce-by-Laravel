<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-address-card mr-2"></i>Billing Address
                </h6>
            </div>
            <div class="card-body">
                @php
                    $billing_first_name = $order->meta->where('meta_key', '_billing_first_name')->first()->meta_value ?? '';
                    $billing_last_name = $order->meta->where('meta_key', '_billing_last_name')->first()->meta_value ?? '';
                    $billing_email = $order->meta->where('meta_key', '_billing_email')->first()->meta_value ?? '';
                    $billing_phone = $order->meta->where('meta_key', '_billing_phone')->first()->meta_value ?? '';
                    $billing_address_1 = $order->meta->where('meta_key', '_billing_address_1')->first()->meta_value ?? '';
                    $billing_address_2 = $order->meta->where('meta_key', '_billing_address_2')->first()->meta_value ?? '';
                    $billing_city = $order->meta->where('meta_key', '_billing_city')->first()->meta_value ?? '';
                    $billing_state = $order->meta->where('meta_key', '_billing_state')->first()->meta_value ?? '';
                    $billing_postcode = $order->meta->where('meta_key', '_billing_postcode')->first()->meta_value ?? '';
                    $billing_country = $order->meta->where('meta_key', '_billing_country')->first()->meta_value ?? '';
                @endphp
                <div class="mb-2">
                    <strong>{{ $billing_first_name }} {{ $billing_last_name }}</strong>
                </div>
                <div class="mb-2">
                    <i class="fas fa-envelope text-primary mr-2"></i>
                    <a href="mailto:{{ $billing_email }}" class="text-decoration-none">
                        {{ $billing_email }}
                    </a>
                </div>
                <div class="mb-2">
                    <i class="fas fa-phone text-primary mr-2"></i>
                    <a href="tel:{{ $billing_phone }}" class="text-decoration-none">
                        {{ $billing_phone }}
                    </a>
                </div>
                <div class="text-muted">
                    {{ $billing_address_1 }}
                    @if($billing_address_2)
                        <br>{{ $billing_address_2 }}
                    @endif
                    <br>{{ $billing_city }}, {{ $billing_state }} {{ $billing_postcode }}
                    <br>{{ $billing_country }}
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
                @php
                    $shipping_first_name = $order->meta->where('meta_key', '_shipping_first_name')->first()->meta_value ?? '';
                    $shipping_last_name = $order->meta->where('meta_key', '_shipping_last_name')->first()->meta_value ?? '';
                    $shipping_address_1 = $order->meta->where('meta_key', '_shipping_address_1')->first()->meta_value ?? '';
                    $shipping_address_2 = $order->meta->where('meta_key', '_shipping_address_2')->first()->meta_value ?? '';
                    $shipping_city = $order->meta->where('meta_key', '_shipping_city')->first()->meta_value ?? '';
                    $shipping_state = $order->meta->where('meta_key', '_shipping_state')->first()->meta_value ?? '';
                    $shipping_postcode = $order->meta->where('meta_key', '_shipping_postcode')->first()->meta_value ?? '';
                    $shipping_country = $order->meta->where('meta_key', '_shipping_country')->first()->meta_value ?? '';
                @endphp
                <div class="mb-2">
                    <strong>{{ $shipping_first_name }} {{ $shipping_last_name }}</strong>
                </div>
                <div class="text-muted">
                    {{ $shipping_address_1 }}
                    @if($shipping_address_2)
                        <br>{{ $shipping_address_2 }}
                    @endif
                    <br>{{ $shipping_city }}, {{ $shipping_state }} {{ $shipping_postcode }}
                    <br>{{ $shipping_country }}
                </div>
            </div>
        </div>
    </div>
</div>
@if($order->post_excerpt)
<div class="alert alert-info mb-0">
    <i class="fas fa-sticky-note mr-2"></i>
    <strong>Customer Note:</strong>
    <p class="mb-0 mt-2">{{ $order->post_excerpt }}</p>
</div>
@endif 