@php
    $hidden_meta = [
        '_order_total', '_order_currency', '_payment_method', '_payment_method_title', 
        '_cart_discount', '_order_shipping', '_order_tax', '_customer_user',
        '_billing_first_name', '_billing_last_name', '_billing_email', '_billing_phone',
        '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_state',
        '_billing_postcode', '_billing_country', '_shipping_first_name', '_shipping_last_name',
        '_shipping_address_1', '_shipping_address_2', '_shipping_city', '_shipping_state',
        '_shipping_postcode', '_shipping_country'
    ];
    $display_meta = $order->meta->whereNotIn('meta_key', $hidden_meta)->where('meta_key', 'not like', '\_%');
@endphp

@if($display_meta->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-tags mr-2"></i>Additional Information
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($display_meta as $meta)
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 mr-3">
                                <i class="fas fa-info-circle text-primary"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">{{ ucwords(str_replace('_', ' ', $meta->meta_key)) }}</small>
                                <strong>{{ $meta->meta_value }}</strong>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif