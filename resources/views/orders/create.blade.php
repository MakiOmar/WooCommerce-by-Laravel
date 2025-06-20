@extends('layouts.admin')

@section('content')
<form id="order-create-form" method="POST" action="{{ route('orders.store') }}">
    @csrf
    <div class="container">
        <!-- Flash Messages -->
        @include('woo-order-dashboard::partials.flash-messages')

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
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Find products..." id="product_search">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">Advanced search</button>
                                <button class="btn btn-primary ml-2" type="button">Products history</button>
                            </div>
                        </div>
                        <div id="product_search_dropdown" class="list-group position-absolute w-100" style="z-index:1000; display:none; max-height: 300px; overflow-y: auto;"></div>
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
                            <input type="text" class="form-control" id="customer-search" placeholder="Guest" autocomplete="off">
                        </div>
                        <input type="hidden" name="customer_id" id="customer_id">
                        <div id="customer-details" class="mt-2" style="display:none;"></div>
                        <div class="form-group mb-2">
                            <label>Billing Details</label>
                            <span>Egypt</span>
                            <a href="#" class="ml-2"><i class="fa fa-pencil"></i></a>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="shipToDifferentAddress" name="ship_to_different_address">
                            <label class="form-check-label" for="shipToDifferentAddress">Ship to a different address?</label>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="form-group mb-2">
                            <label>Order date</label>
                            <input type="date" class="form-control mb-1" name="order_date">
                            <div class="d-flex">
                                <select class="form-control mr-2" style="width: 70px;" name="order_hour">
                                    <option>19</option>
                                    <!-- More hours -->
                                </select>
                                <select class="form-control" style="width: 70px;" name="order_minute">
                                    <option>25</option>
                                    <!-- More minutes -->
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label>Order status</label>
                            <select class="form-control" name="order_status">
                                <option value="pending">Pending</option>
                                <option value="processing" selected>Processing</option>
                                <option value="completed">Completed</option>
                                <option value="on-hold">On Hold</option>
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
                                    <span class="font-weight-bold">ج.م <span class="order-subtotal">0.00</span></span>
                                </div>

                                <!-- Discount Row -->
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted">Discount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">ج.م</span>
                                            <input type="number" class="form-control order-discount" name="discount" value="0" min="0" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <!-- Shipping Row -->
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted">Shipping</label>
                                        <div class="input-group">
                                            <span class="input-group-text">ج.م</span>
                                            <input type="number" class="form-control order-shipping" name="shipping" value="0" min="0" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <!-- Tax Row -->
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted">Tax</label>
                                        <div class="input-group">
                                            <span class="input-group-text">ج.م</span>
                                            <input type="number" class="form-control order-taxes" name="taxes" value="0" min="0" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- Total Row -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Total</h5>
                                    <h5 class="mb-0">ج.م <span class="order-grand-total">0.00</span></h5>
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

@push('js')
<script>
$(function() {
    // Product search dropdown
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

    $prodInput.on('input', function() {
        var q = $(this).val();
        if (q.length < 2) { 
            $prodDropdown.hide(); 
            return; 
        }
        
        $.getJSON("{{ route('orders.products.search') }}", {q: q}, function(data) {
            $prodDropdown.empty().show();
            if (data.length === 0) {
                $prodDropdown.append('<div class="list-group-item">No products found</div>');
            } else {
                data.forEach(function(p) {
                    var attrs = '';
                    if (p.variation_id && p.attributes && Object.keys(p.attributes).length > 0) {
                        attrs = '<br><small class="text-info">' + Object.entries(p.attributes).map(function([k, v]) {
                            return k.replace('attribute_', '') + ': ' + v;
                        }).join(', ') + '</small>';
                    }
                    
                    var skuInfo = p.sku ? ' (SKU: ' + p.sku + ')' : '';
                    $prodDropdown.append(
                        '<button type="button" class="list-group-item list-group-item-action prod-item" ' +
                        'data-product-id="'+p.product_id+'" data-variation-id="'+p.variation_id+'" ' +
                        'data-name="'+p.name+'" data-price="'+p.price+'" data-attributes=\''+JSON.stringify(p.attributes || {})+'\'>' +
                        '<div class="d-flex justify-content-between align-items-start">' +
                        '<div><strong>'+p.name+'</strong>'+attrs+'<br><small class="text-muted">ID: '+p.product_id+(p.variation_id ? ' | Variation: '+p.variation_id : '')+skuInfo+'</small></div>' +
                        '<div class="text-right"><strong>$'+(parseFloat(p.price) || 0).toFixed(2)+'</strong></div>' +
                        '</div></button>'
                    );
                });
            }
        });
    });

    $(document).on('click', '.prod-item', function() {
        var productId = $(this).data('product-id');
        var variationId = $(this).data('variation-id');
        var name = $(this).data('name');
        var price = parseFloat($(this).data('price')) || 0;
        var attributes = $(this).data('attributes') ? JSON.parse($(this).attr('data-attributes')) : {};

        var rowId = variationId > 0 ? variationId : productId;
        var $existingRow = $prodTable.find('tr[data-row-id="'+rowId+'"]');
        
        if ($existingRow.length > 0) {
            var $qtyInput = $existingRow.find('.order-qty');
            $qtyInput.val(parseInt($qtyInput.val() || 1) + 1).trigger('input');
        } else {
            var attrHtml = '';
            if (Object.keys(attributes).length > 0) {
                attrHtml = '<br><small class="text-info">' + Object.entries(attributes).map(function([k, v]) {
                    return k.replace('attribute_', '') + ': ' + v;
                }).join(', ') + '</small>';
            }
            
            var row = '<tr data-row-id="'+rowId+'" data-product-id="'+productId+'" data-variation-id="'+variationId+'" data-attributes=\''+JSON.stringify(attributes)+'\">' +
                '<td><strong>'+name+'</strong>'+attrHtml+'</td>' +
                '<td class="order-price">'+price.toFixed(2)+'</td>' +
                '<td><input type="number" class="form-control form-control-sm order-qty" value="1" min="1" style="width:70px;"></td>' +
                '<td class="line-item-total">'+price.toFixed(2)+'</td>' +
                '<td><button type="button" class="btn btn-sm btn-danger remove-item">&times;</button></td>' +
                '</tr>';
            $prodTable.append(row);
        }
        
        $prodDropdown.hide();
        $prodInput.val('');
        recalcSummary();
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
    $custInput.on('input', function() {
        var q = $(this).val();
        if (q.length < 2) { if ($custDropdown) $custDropdown.remove(); return; }
        $.getJSON("{{ route('orders.customers.search') }}", {q: q}, function(customers) {
            if ($custDropdown) $custDropdown.remove();
            $custDropdown = $('<div class="list-group position-absolute w-100" style="z-index:1000;"></div>');
            if (customers.length === 0) {
                $custDropdown.append('<div class="list-group-item">No customers found. <a href="#" class="text-primary add-new-customer">Create new</a></div>');
            } else {
                customers.forEach(function(c) {
                    $custDropdown.append('<button type="button" class="list-group-item list-group-item-action cust-item" data-id="'+c.id+'" data-name="'+c.name+'" data-email="'+c.email+'">'+c.name+' <small class="text-muted">('+c.email+')</small></button>');
                });
            }
            $custInput.after($custDropdown);
        });
    });
    $(document).on('click', '.cust-item', function() {
        var name = $(this).data('name');
        var email = $(this).data('email');
        var id = $(this).data('id');
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
    });

    // On form submit, serialize order items and customer id
    $('#order-create-form').on('submit', function(e) {
        var items = [];
        $prodTable.find('tr').each(function() {
            items.push({
                product_id: $(this).data('product-id'),
                variation_id: $(this).data('variation-id'),
                name: $(this).find('td').eq(0).text().trim(),
                price: parseFloat($(this).find('.order-price').text()) || 0,
                qty: parseInt($(this).find('.order-qty').val()) || 1,
                attributes: $(this).data('attributes') ? JSON.parse($(this).attr('data-attributes')) : {}
            });
        });
        $('#order_items').val(JSON.stringify(items));
        
        // Validate that we have at least one item
        if (items.length === 0) {
            e.preventDefault();
            alert('Please add at least one product to the order.');
            return false;
        }
    });
});
</script>
@endpush