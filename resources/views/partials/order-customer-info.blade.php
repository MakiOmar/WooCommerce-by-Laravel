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
                    $customerId = $order->meta->where('meta_key', '_customer_user')->first()->meta_value ?? null;
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
                    
                    $hasBillingData = !empty($billing_first_name) || !empty($billing_last_name) || !empty($billing_email);
                @endphp
                
                @if($hasBillingData)
                    <div class="mb-2">
                        <strong>{{ $billing_first_name }} {{ $billing_last_name }}</strong>
                        @if($customerId && $customerId != '0')
                            <small class="text-muted">(Customer ID: {{ $customerId }})</small>
                        @endif
                    </div>
                    @if(!empty($billing_email))
                        <div class="mb-2">
                            <i class="fas fa-envelope text-primary mr-2"></i>
                            <a href="mailto:{{ $billing_email }}" class="text-decoration-none">
                                {{ $billing_email }}
                            </a>
                        </div>
                    @endif
                    @if(!empty($billing_phone))
                        <div class="mb-2">
                            <i class="fas fa-phone text-primary mr-2"></i>
                            <a href="tel:{{ $billing_phone }}" class="text-decoration-none">
                                {{ $billing_phone }}
                            </a>
                        </div>
                    @endif
                    @if(!empty($billing_address_1) || !empty($billing_city))
                        <div class="text-muted">
                            @if(!empty($billing_address_1))
                                {{ $billing_address_1 }}
                                @if($billing_address_2)
                                    <br>{{ $billing_address_2 }}
                                @endif
                            @endif
                            @if(!empty($billing_city))
                                <br>{{ $billing_city }}, {{ $billing_state }} {{ $billing_postcode }}
                                <br>{{ $billing_country }}
                            @endif
                        </div>
                    @endif
                @else
                    <div class="text-muted">
                        @if($customerId && $customerId != '0')
                            <i class="fas fa-user mr-2"></i>
                            Customer ID: {{ $customerId }}
                            <br><small>No billing information available</small>
                        @else
                            <i class="fas fa-user-slash mr-2"></i>
                            Guest Customer
                            <br><small>No billing information available</small>
                        @endif
                    </div>
                @endif
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
                    
                    $hasShippingData = !empty($shipping_first_name) || !empty($shipping_last_name) || !empty($shipping_address_1) || !empty($shipping_city);
                @endphp
                
                @if($hasShippingData)
                    <div class="mb-2">
                        <strong>{{ $shipping_first_name }} {{ $shipping_last_name }}</strong>
                    </div>
                    <div class="text-muted">
                        @if(!empty($shipping_address_1))
                            {{ $shipping_address_1 }}
                            @if($shipping_address_2)
                                <br>{{ $shipping_address_2 }}
                            @endif
                        @endif
                        @if(!empty($shipping_city))
                            <br>{{ $shipping_city }}, {{ $shipping_state }} {{ $shipping_postcode }}
                            <br>{{ $shipping_country }}
                        @endif
                    </div>
                @else
                    <div class="text-muted">
                        <i class="fas fa-info-circle mr-2"></i>
                        Same as billing address
                        <br><small>No separate shipping information available</small>
                    </div>
                @endif
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