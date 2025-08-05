@php
    $wooCurrency = config('woo-order-dashboard.currency', 'SAR');
@endphp
<script>
    window.wooCurrency = @json($wooCurrency);
    window.translations = {
        no_products_found: @json(__('woo-order-dashboard::orders.no_products_found')),
        search_failed: @json(__('woo-order-dashboard::orders.search_failed')),
        could_not_retrieve_product: @json(__('woo-order-dashboard::orders.could_not_retrieve_product')),
        please_add_products: @json(__('woo-order-dashboard::orders.please_add_products')),
        no_customers_found: @json(__('woo-order-dashboard::orders.no_customers_found')),
        create_new: @json(__('woo-order-dashboard::orders.create_new')),
        new_customer_will_be_created: @json(__('woo-order-dashboard::orders.new_customer_will_be_created')),
        no_shipping_methods: @json(__('woo-order-dashboard::orders.no_shipping_methods')),
        failed_to_load_shipping: @json(__('woo-order-dashboard::orders.failed_to_load_shipping')),
        am: @json(__('woo-order-dashboard::orders.am')),
        pm: @json(__('woo-order-dashboard::orders.pm')),
    };
</script>
@extends('layouts.admin')

@section('content')
<form action="{{ route('orders.store') }}" method="POST" id="order-create-form">
    @csrf
    <div class="container-fluid rtl">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h3 mb-0">{{ __('woo-order-dashboard::orders.create_new_order') }}</h2>
                    <div>
                        @include('woo-order-dashboard::partials.language-switcher')
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">{{ __('woo-order-dashboard::orders.cancel') }}</a>
                        <button type="submit" class="btn btn-success">{{ __('woo-order-dashboard::orders.submit_order') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">{{ __('woo-order-dashboard::orders.order_details') }}</h5>
                            <div>
                                <button class="btn btn-link" type="button">{{ __('woo-order-dashboard::orders.create_custom_product') }}</button>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="product_search">{{ __('woo-order-dashboard::orders.search_products') }}</label>
                            <div class="row">
                                <div class="col-md-3">
                                    <select class="form-control" id="search_type">
                                        <option value="sku" selected>{{ __('woo-order-dashboard::orders.search_by_sku') }}</option>
                                        <option value="title">{{ __('woo-order-dashboard::orders.search_by_title') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <div class="search-input-container">
                                        <input type="text" class="form-control" id="product_search" placeholder="{{ __('woo-order-dashboard::orders.search_for_products') }}" autocomplete="off">
                                        <div class="loading-indicator">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary w-100" id="search_btn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="product_search_dropdown" class="list-group position-absolute w-100" style="z-index:1000; display:none;"></div>
                        </div>
                        <table class="table" id="products-table">
                            <thead>
                                <tr>
                                    <th>{{ __('woo-order-dashboard::orders.product') }}</th>
                                    <th>{{ __('woo-order-dashboard::orders.price') }}</th>
                                    <th>{{ __('woo-order-dashboard::orders.quantity') }}</th>
                                    <th>{{ __('woo-order-dashboard::orders.total') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Products will be added here dynamically -->
                                <tr id="no-products-row">
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                        <p class="mb-0">{{ __('woo-order-dashboard::orders.no_products_added') }}</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <input type="hidden" name="order_items" id="order_items">
                        <div class="form-group">
                            <label>{{ __('woo-order-dashboard::orders.customer_provided_note') }}</label>
                            <textarea class="form-control" rows="2" name="customer_note" placeholder="{{ __('woo-order-dashboard::orders.add_note') }}"></textarea>
                        </div>
                        <div class="form-group">
                            <label>{{ __('woo-order-dashboard::orders.private_note') }}</label>
                            <textarea class="form-control" rows="2" name="private_note" placeholder="{{ __('woo-order-dashboard::orders.add_note') }}"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>{{ __('woo-order-dashboard::orders.find_or_create_customer') }} <button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button></h6>
                        <div class="form-group mb-2">
                            <a href="#">{{ __('woo-order-dashboard::orders.new_customer') }}</a>
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="search-input-container">
                                        <input type="text" class="form-control" id="customer-search" placeholder="{{ __('woo-order-dashboard::orders.guest') }}" autocomplete="off">
                                        <div class="loading-indicator">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary w-100" id="customer_search_btn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="customer_id" id="customer_id">
                        <div id="customer-details" class="mt-2" style="display:none;"></div>
                        <div class="form-group mb-2">
                            <label>{{ __('woo-order-dashboard::orders.billing_details') }}</label>
                            <span id="billing-display">{{ __('woo-order-dashboard::orders.no_customer_selected') }}</span>
                            <a href="#" class="ml-2" id="edit-billing-btn" style="display:none;"><i class="fa fa-pencil-alt"></i></a>
                        </div>
                        
                        <!-- Hidden billing input fields -->
                        <div id="billing-fields" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label>First Name</label>
                                        <input type="text" class="form-control" id="billing_first_name" name="billing_first_name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label>Last Name</label>
                                        <input type="text" class="form-control" id="billing_last_name" name="billing_last_name">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label>Email</label>
                                <input type="email" class="form-control" id="billing_email" name="billing_email">
                            </div>
                            <div class="form-group mb-2">
                                <label>Phone</label>
                                <input type="text" class="form-control" id="billing_phone" name="billing_phone">
                            </div>
                            <div class="form-group mb-2">
                                <label>Address Line 1</label>
                                <input type="text" class="form-control" id="billing_address_1" name="billing_address_1">
                            </div>
                            <div class="form-group mb-2">
                                <label>Address Line 2</label>
                                <input type="text" class="form-control" id="billing_address_2" name="billing_address_2">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label>City</label>
                                        <input type="text" class="form-control" id="billing_city" name="billing_city">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label>State</label>
                                        <input type="text" class="form-control" id="billing_state" name="billing_state">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label>Postcode</label>
                                        <input type="text" class="form-control" id="billing_postcode" name="billing_postcode">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label>Country</label>
                                        <input type="text" class="form-control" id="billing_country" name="billing_country">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="cancel-billing-btn">{{ __('woo-order-dashboard::orders.close') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="form-group mb-2">
                            <label>{{ __('woo-order-dashboard::orders.order_date') }}</label>
                            <input type="text" class="form-control mb-1" name="order_date" value="{{ old('order_date', $defaultOrderDate ?? '') }}">
                            <div class="d-flex">
                                <select class="form-control mr-2" style="width: 125px;" name="order_hour">
                                    @for ($h = 0; $h < 24; $h++)
                                        @php
                                            $ampm = $h == 0 ? '12 ' . __('woo-order-dashboard::orders.am') : ($h < 12 ? $h . ' ' . __('woo-order-dashboard::orders.am') : ($h == 12 ? '12 ' . __('woo-order-dashboard::orders.pm') : ($h-12) . ' ' . __('woo-order-dashboard::orders.pm')));
                                            $label = sprintf('%02d', $h) . ' (' . $ampm . ')';
                                        @endphp
                                        <option value="{{ $h }}" {{ (old('order_hour', $defaultOrderHour ?? '') == $h) ? 'selected' : '' }}>{{ $label }}</option>
                                    @endfor
                                </select>
                                <select class="form-control" style="width: 70px;" name="order_minute">
                                    @for ($m = 0; $m < 60; $m++)
                                        <option value="{{ $m }}" {{ (old('order_minute', $defaultOrderMinute ?? '') == $m) ? 'selected' : '' }}>{{ sprintf('%02d', $m) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label>{{ __('woo-order-dashboard::orders.order_status') }}</label>
                            <select class="form-control" name="order_status">
                                @php
                                    $orderStatuses = \Makiomar\WooOrderDashboard\Helpers\Orders\StatusHelper::getAllStatuses();
                                @endphp
                                @foreach($orderStatuses as $statusKey => $statusLabel)
                                    <option value="{{ $statusKey }}" {{ $statusKey === 'processing' ? 'selected' : '' }}>
                                        {{ $statusLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="payment_method_type">{{ __('woo-order-dashboard::orders.payment_method_type') }}</label>
                            <select class="form-control" name="payment_method_type" id="payment_method_type">
                                <option value="static" selected>{{ __('woo-order-dashboard::orders.static_payment_methods') }}</option>
                                <option value="dynamic">{{ __('woo-order-dashboard::orders.dynamic_payment_methods') }}</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="static_payment_methods">
                            <label for="payment_method">{{ __('woo-order-dashboard::orders.payment_method') }}</label>
                            <select class="form-control" name="payment_method" id="payment_method">
                                <option value="bacs" selected>{{ __('woo-order-dashboard::orders.direct_bank_transfer') }}</option>
                                <option value="cod">{{ __('woo-order-dashboard::orders.cash_on_delivery') }}</option>
                                <option value="online">{{ __('woo-order-dashboard::orders.online_payment') }}</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="dynamic_payment_methods" style="display: none;">
                            <label for="payment_method_dynamic">{{ __('woo-order-dashboard::orders.payment_method') }}</label>
                            @php
                                $paymentGatewayHelper = new \Makiomar\WooOrderDashboard\Helpers\Gateways\PaymentGatewayHelper();
                                $paymentGateways = $paymentGatewayHelper->getEnabledPaymentGateways();
                            @endphp
                            <select class="form-control" name="payment_method_dynamic" id="payment_method_dynamic">
                                <option value="">{{ __('woo-order-dashboard::orders.select_payment_method') }}</option>
                                @if (!empty($paymentGateways))
                                    @foreach ($paymentGateways as $gateway_id => $gateway)
                                        <option value="{{ $gateway_id }}">{{ $gateway['title'] }}</option>
                                    @endforeach
                                @else
                                    <option value="">{{ __('woo-order-dashboard::orders.no_payment_methods') }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <!-- Order Summary Section -->
                        <div class="card mt-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">{{ __('woo-order-dashboard::orders.order_summary') }}</h5>
                            </div>
                            <div class="card-body">
                                <!-- Subtotal Row -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">{{ __('woo-order-dashboard::orders.subtotal') }}</span>
                                    <span class="font-weight-bold">{{$wooCurrency}} <span class="order-subtotal">0.00</span></span>
                                </div>

                                <!-- Discount Row -->
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted">{{ __('woo-order-dashboard::orders.discount') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{$wooCurrency}}</span>
                                            <input type="text" class="form-control order-discount" name="discount" value="0" pattern="[0-9]*\.?[0-9]*" inputmode="decimal">
                                        </div>
                                    </div>
                                </div>

                                <!-- Shipping Row -->
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted">{{ __('woo-order-dashboard::orders.shipping') }}</label>
                                        <div class="input-group shipping-input-group">
                                            <span class="input-group-text">{{$wooCurrency}}</span>
                                            <input type="text" class="form-control order-shipping" name="shipping" value="0" pattern="[0-9]*\.?[0-9]*" inputmode="decimal">
                                            <button type="button" class="btn btn-outline-secondary" id="shipping-methods-btn" style="display: none;">
                                                <i class="fas fa-truck"></i>
                                            </button>
                                        </div>
                                        <div id="shipping-methods-dropdown" class="list-group position-absolute w-100" style="z-index:3000; display:none; max-height: 200px; overflow-y: auto;"></div>
                                        <!-- Hidden fields for shipping method details -->
                                        <input type="hidden" name="shipping_method_id" id="shipping_method_id" value="">
                                        <input type="hidden" name="shipping_method_title" id="shipping_method_title" value="">
                                        <input type="hidden" name="shipping_instance_id" id="shipping_instance_id" value="">
                                    </div>
                                </div>

                                <!-- RedBox Pickup Section -->
                                <div class="row mb-3" id="redbox-pickup-section" style="display: none;">
                                    <div class="col-12">
                                        <div class="card border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-box-open"></i> 
                                                    {{ __('woo-order-dashboard::shipping.redbox_pickup') }}
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group mb-3">
                                                    <label>{{ __('woo-order-dashboard::shipping.select_pickup_point') }}</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="redbox_point" name="redbox_point" readonly placeholder="{{ __('woo-order-dashboard::shipping.choose_pickup_point') }}">
                                                        <button type="button" class="btn btn-outline-primary" id="select-redbox-point">
                                                            <i class="fas fa-map-marker-alt"></i> 
                                                            {{ __('woo-order-dashboard::shipping.select_point') }}
                                                        </button>
                                                    </div>
                                                    <input type="hidden" name="redbox_point_id" id="redbox_point_id">
                                                    <small class="form-text text-muted">{{ __('woo-order-dashboard::shipping.pickup_point_help') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tax Row -->
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted">{{ __('woo-order-dashboard::orders.tax') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{$wooCurrency}}</span>
                                            <input type="text" class="form-control order-taxes" name="taxes" value="0" pattern="[0-9]*\.?[0-9]*" inputmode="decimal">
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- Total Row -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">{{ __('woo-order-dashboard::orders.grand_total') }}</h5>
                                    <h5 class="mb-0">{{$wooCurrency}} <span class="order-grand-total">0.00</span></h5>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-block mt-3">Submit Order</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RedBox Pickup Modal -->
    <div class="modal fade" id="redbox-modal" tabindex="-1" role="dialog" aria-labelledby="redbox-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="redbox-modal-label">
                        <i class="fas fa-map-marker-alt"></i> 
                        {{ __('woo-order-dashboard::shipping.select_redbox_pickup_point') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label>{{ __('woo-order-dashboard::shipping.search_location') }}</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="redbox-search" placeholder="{{ __('woo-order-dashboard::shipping.enter_location') }}">
                                    <button type="button" class="btn btn-primary" id="redbox-search-btn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="redbox-map" style="height: 400px; border: 1px solid #ddd; border-radius: 8px;"></div>
                        </div>
                        <div class="col-md-4">
                            <h6>{{ __('woo-order-dashboard::shipping.available_points') }}</h6>
                            <div id="redbox-points-list" class="list-group" style="max-height: 400px; overflow-y: auto;">
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-map-marker-alt fa-2x mb-2"></i>
                                    <p>{{ __('woo-order-dashboard::shipping.no_points_available') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('woo-order-dashboard::shipping.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="confirm-redbox-point" disabled>
                        {{ __('woo-order-dashboard::shipping.confirm_selection') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
@if(config('woo-order-dashboard.css_mode', 'inline'))
    @include('woo-order-dashboard::partials.woo-order-dashboard-inline-css')
@else
    <link href="{{ asset('vendor/woo-order-dashboard/css/woo-order-dashboard.css') }}" rel="stylesheet">
@endif
<style>
/* RTL Support */
.rtl, .rtl * {
    direction: rtl;
    text-align: right;
}
.rtl .input-group {
    flex-direction: row-reverse;
}
.rtl .input-group .form-control {
    border-radius: 0 .375rem .375rem 0;
}
.rtl .input-group .input-group-text, .rtl .input-group .btn {
    border-radius: .375rem 0 0 .375rem;
}
.rtl .order-summary-label {
    text-align: right;
    float: right;
}

/* Improved Order Summary Styling */
.card.order-summary {
    border-radius: 1rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    background: #fff;
    padding: 1.5rem 1.25rem;
}
.card.order-summary .card-header {
    background: #f9fafb;
    border-radius: 1rem 1rem 0 0;
    border-bottom: 1px solid #eee;
    text-align: right;
}
.card.order-summary .card-title {
    color: #6c47e5;
    font-size: 1.2rem;
    font-weight: 700;
}
.card.order-summary .input-group {
    margin-bottom: 0.5rem;
}
.card.order-summary .input-group .form-control {
    min-width: 60px;
    text-align: right;
}
.card.order-summary .input-group .input-group-text, .card.order-summary .input-group .btn {
    min-width: 40px;
    text-align: center;
}
.card.order-summary .order-summary-label {
    font-size: 0.95rem;
    color: #888;
    margin-bottom: 0.2rem;
}
.card.order-summary .order-grand-total {
    font-size: 1.3rem;
    font-weight: bold;
}
#shipping-methods-btn {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    background: #f3f4f6;
    color: #6c47e5;
    border: 1px solid #e5e7eb;
    transition: background 0.2s, color 0.2s;
    position: absolute;
    left: -70px;
    border-radius: 0.375rem;
    height: 38px;
}

/* RedBox Pickup Styles */
.redbox-pickup-section {
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    transition: border-color 0.2s;
}

.redbox-pickup-section.active {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.redbox-point-item {
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
}

.redbox-point-item:hover {
    border-color: #3b82f6;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.redbox-point-item.selected {
    border-color: #3b82f6;
    background-color: #eff6ff;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.redbox-point-header {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.redbox-point-icon {
    width: 24px;
    height: 24px;
    margin-right: 0.5rem;
}

.redbox-point-name {
    font-weight: 600;
    color: #1f2937;
}

.redbox-point-type {
    font-size: 0.875rem;
    color: #6b7280;
    margin-left: auto;
}

.redbox-point-details {
    font-size: 0.875rem;
    color: #6b7280;
}

.redbox-point-address {
    margin-bottom: 0.25rem;
}

.redbox-point-hours {
    margin-bottom: 0.25rem;
}

.redbox-point-delivery {
    margin-bottom: 0.25rem;
}

.redbox-point-payment {
    margin-bottom: 0.25rem;
}

.redbox-point-restricted {
    background-color: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 0.25rem;
    padding: 0.5rem;
    margin-top: 0.5rem;
}

.redbox-point-restricted .form-check {
    margin-top: 0.5rem;
}

.redbox-map-container {
    position: relative;
    height: 400px;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    overflow: hidden;
}

.redbox-map-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: #6b7280;
}

.redbox-search-container {
    position: relative;
    margin-bottom: 1rem;
}

.redbox-search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    max-height: 200px;
    overflow-y: auto;
    display: none;
}

.redbox-search-result {
    padding: 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background-color 0.2s;
}

.redbox-search-result:hover {
    background-color: #f9fafb;
}

.redbox-search-result:last-child {
    border-bottom: none;
}

.redbox-modal .modal-xl {
    max-width: 1200px;
}

.redbox-points-list {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 1rem;
}

.redbox-points-loading {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

.redbox-points-empty {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

/* RTL Support for RedBox */
.rtl .redbox-point-icon {
    margin-right: 0;
    margin-left: 0.5rem;
}

.rtl .redbox-point-type {
    margin-left: 0;
    margin-right: auto;
}

/* Sale price styling */
.text-decoration-line-through {
    text-decoration: line-through;
}
.text-success {
    color: #28a745 !important;
}
.text-info {
    color: #17a2b8 !important;
}
.text-muted {
    color: #6c757d !important;
}

/* Customer search dropdown styling */
.customer-search-dropdown {
    z-index: 20 !important;
    max-height: 200px;
    overflow-y: auto;
    border-radius: 0.5rem;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    font-size: 0.97rem;
}
.customer-search-dropdown .list-group-item {
    cursor: pointer;
    transition: background 0.15s;
}
.customer-search-dropdown .list-group-item:hover {
    background: #f3f4f6;
}
.shipping-input-group .order-shipping {
    min-width: 0;
    flex: 1 1 80px;
}
.shipping-input-group #shipping-methods-btn {
    flex: 0 0 auto;
}

/* Numeric input styling */
.order-qty, .order-discount, .order-shipping, .order-taxes {
    text-align: right;
    font-family: 'Courier New', monospace;
}

/* Prevent zoom on mobile for numeric inputs */
.order-qty, .order-discount, .order-shipping, .order-taxes {
    font-size: 16px;
}

/* Payment method styling */
#payment_method_type {
    margin-bottom: 1rem;
}

#static_payment_methods, #dynamic_payment_methods {
    transition: all 0.3s ease;
}

.payment-method-section {
    border-left: 3px solid #007cba;
    padding-left: 1rem;
    margin-left: 0.5rem;
}
</style>
@endsection

@section('scripts')
@if(config('woo-order-dashboard.js_mode', 'inline'))
    @include('woo-order-dashboard::partials.woo-order-dashboard-inline-js')
@else
    <script src="{{ asset('vendor/woo-order-dashboard/js/loading-utils.js') }}"></script>
@endif
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
$(document).ready(function() {
    // Product search dropdown - define variables inside document ready
    var $prodInput = $('#product_search');
    var $prodTable = $('#products-table tbody');
    var $prodDropdown = $('#product_search_dropdown');

    // Input validation for numeric fields
    function validateNumericInput(input, allowDecimal = false) {
        var value = input.value;
        var pattern = allowDecimal ? /^[0-9]*\.?[0-9]*$/ : /^[0-9]*$/;
        
        if (!pattern.test(value)) {
            // Remove non-numeric characters
            input.value = value.replace(/[^0-9.]/g, '');
            
            // For decimal inputs, ensure only one decimal point
            if (allowDecimal) {
                var parts = input.value.split('.');
                if (parts.length > 2) {
                    input.value = parts[0] + '.' + parts.slice(1).join('');
                }
            }
        }
    }

    // Bind validation to numeric inputs
    $(document).on('input paste', '.order-qty', function() {
        validateNumericInput(this, false);
    });

    $(document).on('input paste', '.order-discount, .order-shipping, .order-taxes', function() {
        validateNumericInput(this, true);
    });

    function recalcSummary() {
        var subtotal = 0;
        
        // Calculate subtotal from line items
        $('#products-table tbody tr').each(function() {
            var qtyInput = $(this).find('.order-qty').val();
            var qty = parseInt(qtyInput) || 1;
            // Ensure quantity is at least 1
            if (qty < 1) {
                qty = 1;
                $(this).find('.order-qty').val('1');
            }
            var price = parseFloat($(this).find('.order-price').text()) || 0;
            var lineTotal = qty * price;
            $(this).find('.line-item-total').text(formatCurrency(lineTotal));
            subtotal += lineTotal;
        });

        // Get additional costs - ensure they are valid numbers
        var discountInput = $('.order-discount').val();
        var shippingInput = $('.order-shipping').val();
        var taxesInput = $('.order-taxes').val();
        
        var discount = parseFloat(discountInput) || 0;
        var shipping = parseFloat(shippingInput) || 0;
        var taxes = parseFloat(taxesInput) || 0;

        // Calculate final total
        var grandTotal = subtotal - discount + shipping + taxes;

        // Update display
        $('.order-subtotal').text(formatCurrency(subtotal));
        $('.order-grand-total').text(formatCurrency(grandTotal));
    }

    function formatCurrency(amount) {
        return parseFloat(amount).toFixed(2);
    }

    // Function to perform product search
    function performProductSearch() {
        var q = $prodInput.val().trim();
        var searchType = $('#search_type').val(); // Get the selected search type
        
        if (q.length < 2) { 
            $prodDropdown.hide(); 
            return; 
        }
        
        // Show loading indicator
        loadingManager.showInputLoading('#product_search');
        
        $.getJSON("{{ route('products.search') }}", {q: q, search_type: searchType}, function(data) {
            $prodDropdown.empty().show().css('z-index', '3000');
            if (data.length === 0) {
                $prodDropdown.append('<div class="list-group-item">' + window.translations.no_products_found + '</div>');
            } else {
                data.forEach(function(p) {
                    var productData = {
                        productId: p.product_id,
                        variationId: p.variation_id,
                        name: p.name,
                        price: p.price,
                        priceWithTax: p.price_with_tax,
                        regularPrice: p.regular_price,
                        salePrice: p.sale_price,
                        isOnSale: p.is_on_sale,
                        taxRate: p.tax_rate,
                        taxAmount: p.tax_amount,
                        sku: p.sku,
                        attributes: p.attributes || {}
                    };

                    var attrs = '<br><small class="text-info">' + Object.entries(p.attributes).map(function([k, v]) {
                        try { k = decodeURIComponent(k); } catch (e) {}
                        try { v = decodeURIComponent(v); } catch (e) {}
                        return k + ': ' + v;
                    }).join(', ') + '</small>';
                    
                    var skuInfo = p.sku ? ' (SKU: ' + p.sku + ')' : '';
                    
                    // Build price display with sale and tax info
                    var priceDisplay = '';
                    if (p.is_on_sale && p.regular_price > 0) {
                        priceDisplay += '<div class="text-decoration-line-through text-muted">' + window.wooCurrency + parseFloat(p.regular_price).toFixed(2) + '</div>';
                        priceDisplay += '<div class="text-success"><strong>' + window.wooCurrency + parseFloat(p.price).toFixed(2) + '</strong></div>';
                    } else {
                        priceDisplay += '<div><strong>' + window.wooCurrency + parseFloat(p.price).toFixed(2) + '</strong></div>';
                    }
                    
                    // Add tax info
                    priceDisplay += '<small class="text-muted">+ Tax: ' + window.wooCurrency + parseFloat(p.tax_amount).toFixed(2) + '</small>';
                    priceDisplay += '<br><small class="text-info">Total: ' + window.wooCurrency + parseFloat(p.price_with_tax).toFixed(2) + '</small>';
                    
                    var buttonHtml = '<div class="d-flex justify-content-between align-items-start">' +
                        '<div><strong>'+p.name+'</strong>'+attrs+'<br><small class="text-muted">ID: '+p.product_id+(p.variation_id ? ' | Variation: '+p.variation_id : '')+skuInfo+'</small></div>' +
                        '<div class="text-right">'+priceDisplay+'</div>' +
                        '</div>';

                    $('<button type="button" class="list-group-item list-group-item-action prod-item"></button>')
                        .html(buttonHtml)
                        .data('product', productData)
                        .appendTo($prodDropdown);
                });
            }
        }).fail(function(xhr, status, error) {
            console.error('Search failed:', error);
            $prodDropdown.empty().show().append('<div class="list-group-item text-danger">' + window.translations.search_failed + ': ' + error + '</div>');
        }).always(function() {
            // Hide loading indicator
            loadingManager.hideInputLoading('#product_search');
        });
    }

    // Search button click
    $('#search_btn').on('click', function() {
        performProductSearch();
    });

    // Enter key press in search input
    $prodInput.on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            performProductSearch();
        }
    });

    // Clear dropdown when input is cleared
    $prodInput.on('input', function() {
        if ($(this).val().length === 0) {
            $prodDropdown.hide();
        }
    });

    $(document).on('click', '.prod-item', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var product = $(this).data('product');
        if (!product) {
            console.error(window.translations.could_not_retrieve_product);
            return;
        }

        var productId = product.productId;
        var variationId = product.variationId;
        var name = product.name;
        var price = parseFloat(product.price) || 0;
        var priceWithTax = parseFloat(product.priceWithTax) || price;
        var attributes = product.attributes || {};

        var rowId = variationId > 0 ? variationId : productId;
        var $existingRow = $prodTable.find('tr[data-row-id="'+rowId+'"]');
        
        if ($existingRow.length > 0) {
            var $qtyInput = $existingRow.find('.order-qty');
            $qtyInput.val(parseInt($qtyInput.val() || 1) + 1).trigger('input');
        } else {
            var attrHtml = '';
            if (Object.keys(attributes).length > 0) {
                attrHtml = '<br><small class="text-info">' + Object.entries(attributes).map(function([k, v]) {
                    try { k = decodeURIComponent(k); } catch (e) {}
                    try { v = decodeURIComponent(v); } catch (e) {}
                    return k + ': ' + v;
                }).join(', ') + '</small>';
            }
            
            var row = $('<tr></tr>')
                .attr('data-row-id', rowId)
                .attr('data-product-id', productId)
                .attr('data-variation-id', variationId)
                .data('attributes', attributes)
                .data('price', price)
                .data('priceWithTax', priceWithTax)
                .html(
                    '<td><strong>'+name+'</strong>'+attrHtml+'<br><small class="text-muted">Base: ' + window.wooCurrency + price.toFixed(2) + ' | With Tax: ' + window.wooCurrency + priceWithTax.toFixed(2) + '</small></td>' +
                    '<td class="order-price">'+priceWithTax.toFixed(2)+'</td>' +
                    '<td><input type="text" class="form-control form-control-sm order-qty" value="1" pattern="[0-9]*" inputmode="numeric" style="width:70px;"></td>' +
                    '<td class="line-item-total">'+priceWithTax.toFixed(2)+'</td>' +
                    '<td><button type="button" class="btn btn-sm btn-danger remove-item">&times;</button></td>'
                );

            $prodTable.append(row);
        }
        
        $prodDropdown.hide();
        $prodInput.val('');
        recalcSummary();
        
        // Hide the "no products" row when products are added
        $('#no-products-row').hide();
    });

    $prodTable.on('input', '.order-qty', function() {
        recalcSummary();
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.list-group, #product_search').length) {
            $prodDropdown.hide();
        }
    });

    // Customer autocomplete dropdown
    var $custInput = $('#customer-search');
    var $custDropdown;
    var $custDetails = $('#customer-details');
    
    // Store selected customer's shipping address
    var selectedShippingCountry = '';
    var selectedShippingState = '';
    var selectedShippingPostcode = '';

    // Function to perform customer search
    function performCustomerSearch() {
        var q = $custInput.val().trim();
        if (q.length < 2) { 
            if ($custDropdown) $custDropdown.remove(); 
            return; 
        }
        
        // Show loading indicator
        loadingManager.showInputLoading('#customer-search');
        
        $.getJSON("{{ route('customers.search') }}", {q: q}, function(customers) {
            if ($custDropdown) $custDropdown.remove();
            $custDropdown = $('<div class="list-group customer-search-dropdown position-absolute w-100"></div>');
            if (customers.length === 0) {
                $custDropdown.append('<div class="list-group-item">' + window.translations.no_customers_found + '. <a href="#" class="text-primary add-new-customer">' + window.translations.create_new + '</a></div>');
            } else {
                customers.forEach(function(c) {
                    $custDropdown.append('<button type="button" class="list-group-item list-group-item-action cust-item" data-id="'+c.id+'" data-name="'+c.name+'" data-email="'+c.email+'" data-billing-first-name="'+(c.billing_first_name || '')+'" data-billing-last-name="'+(c.billing_last_name || '')+'" data-billing-phone="'+(c.billing_phone || '')+'" data-billing-address-1="'+(c.billing_address_1 || '')+'" data-billing-address-2="'+(c.billing_address_2 || '')+'" data-billing-city="'+(c.billing_city || '')+'" data-billing-state="'+(c.billing_state || '')+'" data-billing-postcode="'+(c.billing_postcode || '')+'" data-billing-country="'+(c.billing_country || '')+'">'+c.name+' <small class="text-muted">('+c.email+')</small></button>');
                });
            }
            $custInput.after($custDropdown);
        }).fail(function(xhr, status, error) {
            console.error('Customer search failed:', error);
            if ($custDropdown) $custDropdown.remove();
            $custDropdown = $('<div class="list-group customer-search-dropdown position-absolute w-100"></div>');
            $custDropdown.append('<div class="list-group-item text-danger">Search failed: ' + error + '</div>');
            $custInput.after($custDropdown);
        }).always(function() {
            // Hide loading indicator
            loadingManager.hideInputLoading('#customer-search');
        });
    }
    
    // Customer search button click
    $('#customer_search_btn').on('click', function() {
        performCustomerSearch();
    });
    
    // Enter key press in customer search input
    $custInput.on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            performCustomerSearch();
        }
    });
    
    // Clear dropdown when input is cleared
    $custInput.on('input', function() {
        if ($(this).val().length === 0) {
            if ($custDropdown) $custDropdown.remove();
        }
    });

    $(document).on('click', '.cust-item', function() {
        var name = $(this).data('name');
        var email = $(this).data('email');
        var id = $(this).data('id');
        
        // Get billing fields from data attributes
        var billingData = {
            first_name: $(this).data('billing-first-name') || '',
            last_name: $(this).data('billing-last-name') || '',
            phone: $(this).data('billing-phone') || '',
            address_1: $(this).data('billing-address-1') || '',
            address_2: $(this).data('billing-address-2') || '',
            city: $(this).data('billing-city') || '',
            state: $(this).data('billing-state') || '',
            postcode: $(this).data('billing-postcode') || '',
            country: $(this).data('billing-country') || ''
        };
        
        // Update shipping variables for shipping calculations
        selectedShippingCountry = billingData.country;
        selectedShippingState = billingData.state;
        selectedShippingPostcode = billingData.postcode;
        
        // Populate billing fields
        $('#billing_first_name').val(billingData.first_name);
        $('#billing_last_name').val(billingData.last_name);
        $('#billing_email').val(email);
        $('#billing_phone').val(billingData.phone);
        $('#billing_address_1').val(billingData.address_1);
        $('#billing_address_2').val(billingData.address_2);
        $('#billing_city').val(billingData.city);
        $('#billing_state').val(billingData.state);
        $('#billing_postcode').val(billingData.postcode);
        $('#billing_country').val(billingData.country);
        
        // Update billing display
        var displayText = name;
        if (billingData.city && billingData.country) {
            displayText += ' - ' + billingData.city + ', ' + billingData.country;
        }
        $('#billing-display').text(displayText);
        $('#edit-billing-btn').show();
        
        console.log('Customer selected:', {
            id: id,
            name: name,
            email: email,
            billing_data: billingData
        });
        
        $custInput.val(name);
        $('#customer_id').val(id);
        $custDetails.html('<div class="alert alert-info p-2">'+name+'<br><small>'+email+'</small></div>').show();
        if ($custDropdown) $custDropdown.remove();
    });
    $(document).on('click', '.add-new-customer', function(e) {
        e.preventDefault();
        $('#customer_id').val('');
        $custDetails.html('<div class="alert alert-warning p-2">' + window.translations.new_customer_will_be_created + '</div>').show();
        if ($custDropdown) $custDropdown.remove();
        
        // Clear billing fields for new customer
        $('#billing_first_name, #billing_last_name, #billing_email, #billing_phone, #billing_address_1, #billing_address_2, #billing_city, #billing_state, #billing_postcode, #billing_country').val('');
        $('#billing-display').text('New customer');
        $('#edit-billing-btn').show();
    });
    
    // Billing fields toggle functionality
    $('#edit-billing-btn').on('click', function(e) {
        e.preventDefault();
        $('#billing-fields').show();
        $(this).hide();
    });
    
    $('#cancel-billing-btn').on('click', function() {
        $('#billing-fields').hide();
        $('#edit-billing-btn').show();
    });
    
    // Update shipping variables when billing fields change
    $('#billing_country, #billing_state, #billing_postcode').on('change keyup', function() {
        selectedShippingCountry = $('#billing_country').val();
        selectedShippingState = $('#billing_state').val();
        selectedShippingPostcode = $('#billing_postcode').val();
    });
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.list-group, #customer-search').length) {
            if ($custDropdown) $custDropdown.remove();
        }
    });

    // Bind events to recalculate on any change
    $(document).on('change keyup input', '.order-qty, .order-discount, .order-shipping, .order-taxes', recalcSummary);
    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        recalcSummary();
        
        // Show the "no products" row if no products remain
        if ($('#products-table tbody tr').not('#no-products-row').length === 0) {
            $('#no-products-row').show();
        }
    });

    // Test button to add a sample product
    $('#test-add-product').on('click', function() {
        var testProduct = {
            productId: 1,
            variationId: 0,
            name: 'Test Product',
            price: 29.99,
            sku: 'TEST-001',
            attributes: {}
        };
        
        var row = $('<tr></tr>')
            .attr('data-row-id', testProduct.productId)
            .attr('data-product-id', testProduct.productId)
            .attr('data-variation-id', testProduct.variationId)
            .data('attributes', testProduct.attributes)
            .html(
                '<td><strong>'+testProduct.name+'</strong></td>' +
                '<td class="order-price">'+testProduct.price.toFixed(2)+'</td>' +
                '<td><input type="text" class="form-control form-control-sm order-qty" value="1" pattern="[0-9]*" inputmode="numeric" style="width:70px;"></td>' +
                '<td class="line-item-total">'+testProduct.price.toFixed(2)+'</td>' +
                '<td><button type="button" class="btn btn-sm btn-danger remove-item">&times;</button></td>'
            );

        $prodTable.append(row);
        $('#no-products-row').hide();
        recalcSummary();
        
        console.log('Test product added:', testProduct);
    });

    // On form submit, serialize order items and customer id
    $('#order-create-form').on('submit', function(e) {
        console.log('Form submission started');
        
        // Handle payment method selection
        var paymentMethodType = $('#payment_method_type').val();
        
        if (paymentMethodType === 'dynamic') {
            // Use dynamic payment method value
            var dynamicValue = $('#payment_method_dynamic').val();
            if (dynamicValue) {
                // Remove any existing hidden payment_method input
                $('input[name="payment_method"][type="hidden"]').remove();
                // Create a hidden input for the actual payment method
                $('<input type="hidden" name="payment_method" value="' + dynamicValue + '">').appendTo($(this));
            }
        }
        
        var items = [];
        $('#products-table tbody tr').not('#no-products-row').each(function() {
            var $row = $(this);
            var item = {
                product_id: $row.data('product-id'),
                variation_id: $row.data('variation-id'),
                name: $row.find('td:first-child strong').text(),
                price: parseFloat($row.find('.order-price').text()) || 0,
                qty: parseInt($row.find('.order-qty').val()) || 1,
                attributes: $row.data('attributes') || {}
            };
            
            // Only add items that have a valid name and product_id
            if (item.name && item.name.trim() !== '' && item.product_id) {
                items.push(item);
                console.log('Added item:', item);
            } else {
                console.log('Skipped invalid item:', item);
            }
        });
        
        var itemsJson = JSON.stringify(items);
        $('#order_items').val(itemsJson);
        
        console.log('Order items JSON:', itemsJson);
        console.log('Form data before submit:', $(this).serialize());
        
        // Validate that we have at least one item
        if (items.length === 0) {
            e.preventDefault();
            alert(window.translations.please_add_products);
            return false;
        }
        
        // Show loading state using loading manager
        loadingManager.showButtonLoading('button[type="submit"]', 'Creating Order...');
        
        console.log('Form submission proceeding...');
    });

    // Shipping methods functionality
    var $shippingBtn = $('#shipping-methods-btn');
    var $shippingDropdown = $('#shipping-methods-dropdown');
    
    // Function to check if products are selected and show/hide shipping button
    function updateShippingButtonVisibility() {
        var hasProducts = $('#products-table tbody tr').not('#no-products-row').length > 0;
        if (hasProducts) {
            $shippingBtn.show();
        } else {
            $shippingBtn.hide();
            $shippingDropdown.hide();
        }
    }
    
    // Helper to collect cart items for shipping
    function getCartItems() {
        var items = [];
        $('#products-table tbody tr').not('#no-products-row').each(function() {
            var $row = $(this);
            items.push({
                product_id: $row.data('product-id'),
                variation_id: $row.data('variation-id'),
                qty: parseInt($row.find('.order-qty').val()) || 1,
                price: parseFloat($row.find('.order-price').text()) || 0
            });
        });
        return items;
    }

    // Shipping methods button click
    $shippingBtn.on('click', function() {
        var cartItems = getCartItems();
        var shippingData = {
            country: selectedShippingCountry,
            state: selectedShippingState,
            postcode: selectedShippingPostcode,
            items: cartItems
        };
        
        console.log('Shipping methods request data:', shippingData);
        
        $.ajax({
            url: '{{ route('shipping.methods') }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
            },
            data: shippingData,
            success: function(response) {
                // Render shipping methods as before
                var $dropdown = $('#shipping-methods-dropdown');
                $dropdown.empty().show();
                if (response.methods && response.methods.length > 0) {
                    response.methods.forEach(function(method) {
                        var html = '<button type="button" class="list-group-item list-group-item-action shipping-method-item" data-id="'+method.id+'" data-cost="'+method.cost+'" data-title="'+method.title+'" data-method-id="'+method.method_id+'" data-instance-id="'+method.instance_id+'">'+
                            '<div class="d-flex justify-content-between align-items-center">'+
                            '<div><strong>'+method.title+'</strong></div>'+
                            '<div class="text-right"><strong>' + window.wooCurrency + parseFloat(method.cost).toFixed(2) + '</strong></div>'+
                            '</div></button>';
                        $dropdown.append(html);
                    });
                } else {
                    $dropdown.append('<div class="list-group-item">' + window.translations.no_shipping_methods + '</div>');
                }
            },
            error: function(xhr, status, error) {
                $('#shipping-methods-dropdown').empty().show().append('<div class="list-group-item text-danger">' + window.translations.failed_to_load_shipping + '</div>');
            }
        });
    });
    
    // Shipping method selection
    $(document).on('click', '.shipping-method-item', function() {
        var cost = $(this).data('cost');
        var title = $(this).data('title');
        var methodId = $(this).data('method-id');
        var instanceId = $(this).data('instance-id');
        
        // Calculate shipping with 15% tax
        var shippingTax = cost * 0.15;
        var shippingWithTax = cost + shippingTax;
        
        $('.order-shipping').val(shippingWithTax.toFixed(2));
        $('#shipping_method_id').val(methodId);
        $('#shipping_method_title').val(title);
        $('#shipping_instance_id').val(instanceId);
        
        $('#shipping-methods-dropdown').hide();
        recalcSummary(); // Recalculate totals after selecting shipping method
        
        // Check if RedBox method is selected
        checkRedBoxShippingMethod();
    });
    
    // Hide shipping dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#shipping-methods-btn, #shipping-methods-dropdown').length) {
            $shippingDropdown.hide();
        }
    });
    
    // Update shipping button visibility when products are added/removed
    $(document).on('click', '.prod-item', function() {
        // Existing product selection code...
        setTimeout(updateShippingButtonVisibility, 100);
    });
    
    $(document).on('click', '.remove-item', function() {
        // Existing remove item code...
        setTimeout(updateShippingButtonVisibility, 100);
    });
    
    // Initial check for shipping button visibility
    updateShippingButtonVisibility();
    
    // Payment method type switching
    $('#payment_method_type').on('change', function() {
        var selectedType = $(this).val();
        
        if (selectedType === 'static') {
            $('#static_payment_methods').show();
            $('#dynamic_payment_methods').hide();
            // Clear dynamic payment method value
            $('#payment_method_dynamic').val('');
        } else {
            $('#static_payment_methods').hide();
            $('#dynamic_payment_methods').show();
            // Clear static payment method value
            $('#payment_method').val('bacs');
        }
    });

    // RedBox Pickup Functionality
    var redboxMap = null;
    var redboxMarkers = [];
    var redboxPoints = [];
    var selectedRedboxPoint = null;
    var redboxMapToken = null;

    // Check if RedBox shipping method is selected
    function checkRedBoxShippingMethod() {
        var shippingMethodId = $('#shipping_method_id').val();
        var isRedBoxSelected = shippingMethodId && shippingMethodId.includes('redbox_pickup_delivery');
        
        if (isRedBoxSelected) {
            $('#redbox-pickup-section').show();
        } else {
            $('#redbox-pickup-section').hide();
            // Clear RedBox data when not selected
            $('#redbox_point').val('');
            $('#redbox_point_id').val('');
            selectedRedboxPoint = null;
        }
    }

    // Initialize RedBox map
    function initRedBoxMap() {
        if (typeof mapkit === 'undefined') {
            console.error('Apple Maps not loaded');
            return;
        }

        if (!redboxMapToken) {
            loadRedBoxMapToken();
            return;
        }

        try {
            mapkit.init({
                authorizationCallback: function(done) {
                    done(redboxMapToken);
                },
                language: '{{ app()->getLocale() }}'
            });

            redboxMap = new mapkit.Map('redbox-map', {
                showsUserLocationControl: true,
                showsMapTypeControl: false
            });

            // Set default center (Riyadh)
            var defaultLat = {{ config('woo-order-dashboard.redbox.map.default_lat', 24.7135517) }};
            var defaultLng = {{ config('woo-order-dashboard.redbox.map.default_lng', 46.6752957) }};
            var center = new mapkit.Coordinate(defaultLat, defaultLng);
            redboxMap.setCenterAnimated(center);

            loadRedBoxPoints(defaultLat, defaultLng);
        } catch (error) {
            console.error('Error initializing RedBox map:', error);
        }
    }

    // Load RedBox map token
    function loadRedBoxMapToken() {
        $.ajax({
            url: '{{ route('redbox.map-token') }}',
            method: 'GET',
            success: function(response) {
                if (response.success && response.token) {
                    redboxMapToken = response.token;
                    initRedBoxMap();
                } else {
                    console.error('Failed to load RedBox map token:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading RedBox map token:', error);
            }
        });
    }

    // Load RedBox pickup points
    function loadRedBoxPoints(lat, lng) {
        var $pointsList = $('#redbox-points-list');
        $pointsList.html('<div class="redbox-points-loading"><i class="fas fa-spinner fa-spin"></i> {{ __("woo-order-dashboard::shipping.loading_points") }}</div>');

        $.ajax({
            url: '{{ route('redbox.points') }}',
            method: 'GET',
            data: {
                lat: lat,
                lng: lng,
                distance: {{ config('woo-order-dashboard.redbox.map.search_radius', 100000000) }}
            },
            success: function(response) {
                if (response.success && response.points) {
                    redboxPoints = response.points;
                    renderRedBoxPoints();
                    addRedBoxMarkers();
                } else {
                    $pointsList.html('<div class="redbox-points-empty"><i class="fas fa-map-marker-alt"></i> {{ __("woo-order-dashboard::shipping.no_pickup_points") }}</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading RedBox points:', error);
                $pointsList.html('<div class="redbox-points-empty text-danger"><i class="fas fa-exclamation-triangle"></i> Error loading pickup points</div>');
            }
        });
    }

    // Render RedBox points list
    function renderRedBoxPoints() {
        var $pointsList = $('#redbox-points-list');
        $pointsList.empty();

        if (!redboxPoints || redboxPoints.length === 0) {
            $pointsList.html('<div class="redbox-points-empty"><i class="fas fa-map-marker-alt"></i> {{ __("woo-order-dashboard::shipping.no_pickup_points") }}</div>');
            return;
        }

        redboxPoints.forEach(function(point) {
            var pointHtml = createRedBoxPointHtml(point);
            $pointsList.append(pointHtml);
        });
    }

    // Create HTML for RedBox point
    function createRedBoxPointHtml(point) {
        var iconClass = getRedBoxPointIcon(point.type_point);
        var statusClass = point.status === 'LockTemporary' ? 'text-danger' : 'text-success';
        var statusText = point.status === 'LockTemporary' ? '{{ __("woo-order-dashboard::shipping.temporarily_closed") }}' : '{{ __("woo-order-dashboard::shipping.available") }}';
        
        var estimatedTime = getEstimatedDeliveryTime(point.estimateTime);
        var acceptsPayment = point.lockers && point.lockers.find(function(l) { return l.accept_payment === true; }) ? '{{ __("woo-order-dashboard::shipping.yes") }}' : '{{ __("woo-order-dashboard::shipping.no") }}';
        
        var restrictedHtml = '';
        if (!point.is_public) {
            restrictedHtml = `
                <div class="redbox-point-restricted">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirm-access-${point.id}">
                        <label class="form-check-label" for="confirm-access-${point.id}">
                            {{ __("woo-order-dashboard::shipping.confirm_access") }}
                        </label>
                    </div>
                </div>
            `;
        }

        return `
            <div class="redbox-point-item" data-point-id="${point.id}" data-lat="${point.location.lat}" data-lng="${point.location.lng}">
                <div class="redbox-point-header">
                    <i class="fas ${iconClass} redbox-point-icon"></i>
                    <div class="redbox-point-name">${point.point_name}</div>
                    <div class="redbox-point-type">${getPointTypeText(point.type_point)}</div>
                </div>
                <div class="redbox-point-details">
                    <div class="redbox-point-address">
                        <i class="fas fa-map-marker-alt"></i> ${point.address.city} - ${point.address.district} - ${point.address.street}
                    </div>
                    <div class="redbox-point-hours">
                        <i class="fas fa-clock"></i> ${point.open_hour}
                    </div>
                    <div class="redbox-point-delivery">
                        <i class="fas fa-truck"></i> {{ __("woo-order-dashboard::shipping.estimated_delivery") }}: ${estimatedTime}
                    </div>
                    <div class="redbox-point-payment">
                        <i class="fas fa-credit-card"></i> {{ __("woo-order-dashboard::shipping.accepts_payment") }}: ${acceptsPayment}
                    </div>
                    <div class="${statusClass}">
                        <i class="fas fa-circle"></i> ${statusText}
                    </div>
                </div>
                ${restrictedHtml}
                <button type="button" class="btn btn-primary btn-sm mt-2 select-point-btn" ${!point.is_public ? 'disabled' : ''}>
                    {{ __("woo-order-dashboard::shipping.ship_to_location") }}
                </button>
            </div>
        `;
    }

    // Get RedBox point icon class
    function getRedBoxPointIcon(type) {
        switch (type) {
            case 'Locker': return 'fa-box';
            case 'Counter': return 'fa-store';
            case 'Both': return 'fa-boxes';
            default: return 'fa-map-marker-alt';
        }
    }

    // Get point type text
    function getPointTypeText(type) {
        switch (type) {
            case 'Locker': return '{{ __("woo-order-dashboard::shipping.locker") }}';
            case 'Counter': return '{{ __("woo-order-dashboard::shipping.counter") }}';
            case 'Both': return '{{ __("woo-order-dashboard::shipping.counter_locker") }}';
            default: return type;
        }
    }

    // Get estimated delivery time
    function getEstimatedDeliveryTime(hours) {
        var days = hours / 24;
        if (days < 2) {
            return '1-2 {{ __("woo-order-dashboard::shipping.days") }}';
        } else {
            var floorDays = Math.floor(days);
            return floorDays + '-' + (floorDays + 1) + ' {{ __("woo-order-dashboard::shipping.days") }}';
        }
    }

    // Add RedBox markers to map
    function addRedBoxMarkers() {
        // Clear existing markers
        redboxMarkers.forEach(function(marker) {
            if (redboxMap) {
                redboxMap.removeAnnotation(marker);
            }
        });
        redboxMarkers = [];

        if (!redboxMap || !redboxPoints) return;

        redboxPoints.forEach(function(point) {
            var coordinate = new mapkit.Coordinate(point.location.lat, point.location.lng);
            var marker = new mapkit.ImageAnnotation(coordinate, {
                url: {
                    1: getRedBoxMarkerIcon(point)
                },
                title: point.point_name,
                data: point,
                anchorOffset: new DOMPoint(0, -8)
            });

            marker.addEventListener('select', function() {
                selectRedBoxPoint(point);
            });

            redboxMap.addAnnotation(marker);
            redboxMarkers.push(marker);
        });
    }

    // Get RedBox marker icon
    function getRedBoxMarkerIcon(point) {
        // For now, use a simple colored circle
        // In a real implementation, you'd use actual SVG icons
        var color = '#3b82f6'; // Blue for available
        if (point.status === 'LockTemporary') {
            color = '#ef4444'; // Red for locked
        }
        
        return `data:image/svg+xml;base64,${btoa(`
            <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="8" fill="${color}" stroke="white" stroke-width="2"/>
            </svg>
        `)}`;
    }

    // Select RedBox point
    function selectRedBoxPoint(point) {
        selectedRedboxPoint = point;
        
        // Update UI
        $('.redbox-point-item').removeClass('selected');
        $(`.redbox-point-item[data-point-id="${point.id}"]`).addClass('selected');
        
        // Enable confirm button
        $('#confirm-redbox-point').prop('disabled', false);
        
        // Center map on selected point
        if (redboxMap) {
            var coordinate = new mapkit.Coordinate(point.location.lat, point.location.lng);
            redboxMap.setCenterAnimated(coordinate);
        }
    }

    // RedBox point selection from list
    $(document).on('click', '.select-point-btn', function() {
        var $pointItem = $(this).closest('.redbox-point-item');
        var pointId = $pointItem.data('point-id');
        var point = redboxPoints.find(function(p) { return p.id === pointId; });
        
        if (point) {
            selectRedBoxPoint(point);
        }
    });

    // Confirm RedBox point selection
    $('#confirm-redbox-point').on('click', function() {
        if (selectedRedboxPoint) {
            var pointInfo = selectedRedboxPoint.point_name + ' - ' + 
                           selectedRedboxPoint.address.city + ' - ' + 
                           selectedRedboxPoint.address.district + ' - ' + 
                           selectedRedboxPoint.address.street;
            
            $('#redbox_point').val(pointInfo);
            $('#redbox_point_id').val(selectedRedboxPoint.id);
            
            $('#redbox-modal').modal('hide');
            
            // Show success message
            alert('{{ __("woo-order-dashboard::shipping.pickup_point_selected") }}');
        }
    });

    // RedBox modal events
    $('#redbox-modal').on('shown.bs.modal', function() {
        if (!redboxMap) {
            initRedBoxMap();
        }
    });

    // RedBox search functionality
    $('#redbox-search-btn').on('click', function() {
        var searchTerm = $('#redbox-search').val();
        if (searchTerm && redboxMap) {
            var search = new mapkit.Search({region: redboxMap.region});
            search.autocomplete(searchTerm, function(error, data) {
                if (error) {
                    console.error('Search error:', error);
                    return;
                }
                
                if (data.results && data.results.length > 0) {
                    var result = data.results[0];
                    if (result.coordinate) {
                        redboxMap.setCenterAnimated(result.coordinate);
                        loadRedBoxPoints(result.coordinate.latitude, result.coordinate.longitude);
                    }
                }
            });
        }
    });

    // RedBox search on Enter key
    $('#redbox-search').on('keypress', function(e) {
        if (e.which === 13) {
            $('#redbox-search-btn').click();
        }
    });

    // Select RedBox point button click
    $('#select-redbox-point').on('click', function() {
        $('#redbox-modal').modal('show');
    });



    // Initial check for RedBox
    checkRedBoxShippingMethod();

    // Load Apple Maps script
    if (!window.mapkitLoaded) {
        var script = document.createElement('script');
        script.src = 'https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js';
        script.onload = function() {
            window.mapkitLoaded = true;
        };
        document.head.appendChild(script);
    }

});

document.addEventListener('DOMContentLoaded', function() {
    flatpickr('input[name="order_date"]', {
        dateFormat: "Y-m-d",
        allowInput: true
    });
});
</script>
@endsection