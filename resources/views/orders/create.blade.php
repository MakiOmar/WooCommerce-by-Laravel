@php
    $wooCurrency = config('woo-order-dashboard.currency', 'SAR');
@endphp
<script>
    window.wooCurrency = @json($wooCurrency);
</script>
@extends('layouts.admin')

@section('content')
<form action="{{ route('orders.store') }}" method="POST" id="order-create-form">
    @csrf
    <div class="container-fluid rtl">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h3 mb-0">Create New Order</h2>
                    <div>
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success">Submit Order</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Order details</h5>
                            <div>
                                <button class="btn btn-link" type="button">Create custom product</button>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="product_search">Search Products</label>
                            <div class="row">
                                <div class="col-md-3">
                                    <select class="form-control" id="search_type">
                                        <option value="sku" selected>Search by SKU</option>
                                        <option value="title">Search by Title</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <div class="search-input-container">
                                        <input type="text" class="form-control" id="product_search" placeholder="Search for products..." autocomplete="off">
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
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Products will be added here dynamically -->
                                <tr id="no-products-row">
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                        <p class="mb-0">No products added yet. Search for products above to add them to your order.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <input type="hidden" name="order_items" id="order_items">
                        <div class="form-group">
                            <label>Customer provided note</label>
                            <textarea class="form-control" rows="2" name="customer_note" placeholder="Add a note"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Private note</label>
                            <textarea class="form-control" rows="2" name="private_note" placeholder="Add a note"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Find or create a customer <button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button></h6>
                        <div class="form-group mb-2">
                            <a href="#">New customer</a>
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="search-input-container">
                                        <input type="text" class="form-control" id="customer-search" placeholder="Guest" autocomplete="off">
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
                            <label>Billing Details</label>
                            <span id="billing-display">No customer selected</span>
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
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="cancel-billing-btn">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="form-group mb-2">
                            <label>Order date</label>
                            <input type="text" class="form-control mb-1" name="order_date" value="{{ old('order_date', $defaultOrderDate ?? '') }}">
                            <div class="d-flex">
                                <select class="form-control mr-2" style="width: 125px;" name="order_hour">
                                    @for ($h = 0; $h < 24; $h++)
                                        @php
                                            $ampm = $h == 0 ? '12 AM' : ($h < 12 ? $h . ' AM' : ($h == 12 ? '12 PM' : ($h-12) . ' PM'));
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
                            <label>Order status</label>
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
                        <div class="form-group">
                            <label for="payment_method">{{ __('Payment Method') }}</label>
                            @php
                                $paymentGatewayHelper = new \Makiomar\WooOrderDashboard\Helpers\Gateways\PaymentGatewayHelper();
                                $paymentGateways = $paymentGatewayHelper->getEnabledPaymentGateways();
                            @endphp
                            <select class="form-control" name="payment_method" id="payment_method">
                                <option value="">{{ __('Select a payment method') }}</option>
                                @if (!empty($paymentGateways))
                                    @foreach ($paymentGateways as $gateway_id => $gateway)
                                        <option value="{{ $gateway_id }}">{{ $gateway['title'] }}</option>
                                    @endforeach
                                @else
                                    <option value="">{{ __('No payment methods available') }}</option>
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
                                <h5 class="card-title mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <!-- Subtotal Row -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Subtotal</span>
                                    <span class="font-weight-bold">{{$wooCurrency}} <span class="order-subtotal">0.00</span></span>
                                </div>

                                <!-- Discount Row -->
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted">Discount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{$wooCurrency}}</span>
                                            <input type="number" class="form-control order-discount" name="discount" value="0" min="0" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <!-- Shipping Row -->
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted">Shipping</label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{$wooCurrency}}</span>
                                            <input type="number" class="form-control order-shipping" name="shipping" value="0" min="0" step="0.01">
                                            <button type="button" class="btn btn-outline-secondary" id="shipping-methods-btn" style="display: none;">
                                                <i class="fas fa-truck"></i>
                                            </button>
                                        </div>
                                        <div id="shipping-methods-dropdown" class="list-group position-absolute w-100" style="z-index:3000; display:none; max-height: 200px; overflow-y: auto;"></div>
                                    </div>
                                </div>

                                <!-- Tax Row -->
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted">Tax</label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{$wooCurrency}}</span>
                                            <input type="number" class="form-control order-taxes" name="taxes" value="0" min="0" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- Total Row -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Total</h5>
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
}
#shipping-methods-btn:hover {
    background: #6c47e5;
    color: #fff;
}
#shipping-methods-dropdown {
    right: 0;
    left: auto;
    min-width: 220px;
    border-radius: 0.5rem;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    font-size: 0.97rem;
}
#shipping-methods-dropdown .list-group-item {
    cursor: pointer;
    transition: background 0.15s;
}
#shipping-methods-dropdown .list-group-item:hover {
    background: #f3f4f6;
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

    function recalcSummary() {
        var subtotal = 0;
        
        // Calculate subtotal from line items
        $('#products-table tbody tr').each(function() {
            var qty = parseInt($(this).find('.order-qty').val()) || 1;
            var price = parseFloat($(this).find('.order-price').text()) || 0;
            var lineTotal = qty * price;
            $(this).find('.line-item-total').text(formatCurrency(lineTotal));
            subtotal += lineTotal;
        });

        // Get additional costs
        var discount = parseFloat($('.order-discount').val()) || 0;
        var shipping = parseFloat($('.order-shipping').val()) || 0;
        var taxes = parseFloat($('.order-taxes').val()) || 0;

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
                $prodDropdown.append('<div class="list-group-item">No products found</div>');
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
            $prodDropdown.empty().show().append('<div class="list-group-item text-danger">Search failed: ' + error + '</div>');
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
            console.error('Could not retrieve product data.');
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
                    '<td><input type="number" class="form-control form-control-sm order-qty" value="1" min="1" style="width:70px;"></td>' +
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
                $custDropdown.append('<div class="list-group-item">No customers found. <a href="#" class="text-primary add-new-customer">Create new</a></div>');
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
        $custDetails.html('<div class="alert alert-warning p-2">New customer will be created on order submit.</div>').show();
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
    $(document).on('change keyup', '.order-qty, .order-discount, .order-shipping, .order-taxes', recalcSummary);
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
                '<td><input type="number" class="form-control form-control-sm order-qty" value="1" min="1" style="width:70px;"></td>' +
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
            alert('Please add at least one product to the order.');
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
                        var html = '<button type="button" class="list-group-item list-group-item-action shipping-method-item" data-id="'+method.id+'" data-cost="'+method.cost+'">'+
                            '<div class="d-flex justify-content-between align-items-center">'+
                            '<div><strong>'+method.title+'</strong></div>'+
                            '<div class="text-right"><strong>' + window.wooCurrency + parseFloat(method.cost).toFixed(2) + '</strong></div>'+
                            '</div></button>';
                        $dropdown.append(html);
                    });
                } else {
                    $dropdown.append('<div class="list-group-item">No shipping methods available</div>');
                }
            },
            error: function(xhr, status, error) {
                $('#shipping-methods-dropdown').empty().show().append('<div class="list-group-item text-danger">Failed to load shipping methods</div>');
            }
        });
    });
    
    // Shipping method selection
    $(document).on('click', '.shipping-method-item', function() {
        var cost = parseFloat($(this).data('cost')) || 0;
        $('.order-shipping').val(cost.toFixed(2)).trigger('input');
        $shippingDropdown.hide();
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
});
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('input[name="order_date"]', {
        dateFormat: "Y-m-d",
        allowInput: true
    });
});
</script>
@endsection