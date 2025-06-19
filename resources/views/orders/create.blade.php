@extends('layouts.admin')

@section('content')
<form id="order-create-form" method="POST" action="{{ route('orders.store') }}">
    @csrf
    <div class="container">
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
                            <input type="text" class="form-control" placeholder="Find products...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">Advanced search</button>
                                <button class="btn btn-primary ml-2" type="button">Products history</button>
                            </div>
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
                                <option>بانتظار الدفع</option>
                                <!-- More statuses -->
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-danger">Payment method *</label>
                            <select class="form-control" name="payment_method">
                                <option>No value</option>
                                <!-- More payment methods -->
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
                                            <input type="number" class="form-control order-discount" value="0" min="0" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <!-- Shipping Row -->
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted">Shipping</label>
                                        <div class="input-group">
                                            <span class="input-group-text">ج.م</span>
                                            <input type="number" class="form-control order-shipping" value="0" min="0" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <!-- Tax Row -->
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted">Tax</label>
                                        <div class="input-group">
                                            <span class="input-group-text">ج.م</span>
                                            <input type="number" class="form-control order-taxes" value="0" min="0" step="0.01">
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
    var $prodInput = $('input[placeholder="Find products..."]');
    var $prodTable = $('table tbody');
    var $prodDropdown;

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
        if (q.length < 2) { if ($prodDropdown) $prodDropdown.remove(); return; }
        $.getJSON("{{ route('orders.products.search') }}", {q: q}, function(products) {
            if ($prodDropdown) $prodDropdown.remove();
            $prodDropdown = $('<div class="list-group position-absolute w-100" style="z-index:1000;"></div>');
            if (products.length === 0) {
                $prodDropdown.append('<div class="list-group-item">No products found</div>');
            } else {
                products.forEach(function(p) {
                    var skuOrId = p.sku ? p.sku : p.id;
                    $prodDropdown.append('<button type="button" class="list-group-item list-group-item-action prod-item" data-id="'+p.id+'" data-name="'+p.name+'" data-price="'+p.price+'">'+p.name+' <small class="text-muted">('+skuOrId+')</small> <span class="float-right">'+(p.price || 0)+'</span></button>');
                });
            }
            $prodInput.after($prodDropdown);
        });
    });

    $(document).on('click', '.prod-item', function() {
        var name = $(this).data('name');
        var price = $(this).data('price') || 0;
        var id = $(this).data('id');
        var $existingRow = $prodTable.find('tr[data-id="'+id+'"]');
        if ($existingRow.length) {
            var $qtyInput = $existingRow.find('.order-qty');
            $qtyInput.val(parseInt($qtyInput.val() || 1) + 1).trigger('input');
        } else {
            var row = '<tr data-id="'+id+'">'
                +'<td>'+name+'</td>'
                +'<td class="order-price">'+price+'</td>'
                +'<td><input type="number" class="form-control form-control-sm order-qty" value="1" min="1" style="width:70px;"></td>'
                +'<td class="line-item-total">'+price+'</td>'
                +'<td><button type="button" class="btn btn-sm btn-danger remove-item">&times;</button></td>'
                +'</tr>';
            $prodTable.append(row);
        }
        if ($prodDropdown) $prodDropdown.remove();
        $prodInput.val('');
        recalcSummary();
    });

    $prodTable.on('input', '.order-qty', function() {
        recalcSummary();
    });

    $prodTable.on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        recalcSummary();
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.list-group, [placeholder="Find products..."]').length) {
            if ($prodDropdown) $prodDropdown.remove();
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
        $custDetails.html('<div class="alert alert-info p-2">'+name+'<br><small>'+email+'</small></div>').show();
        if ($custDropdown) $custDropdown.remove();
    });
    $(document).on('click', '.add-new-customer', function(e) {
        e.preventDefault();
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
                id: $(this).data('id'),
                name: $(this).find('td').eq(0).text(),
                price: $(this).find('.order-price').text(),
                qty: $(this).find('.order-qty').val()
            });
        });
        $('#order_items').val(JSON.stringify(items));
        // Set customer id if selected
        var custId = $custDropdown && $custDropdown.find('.cust-item.active').data('id');
        if (custId) $('#customer_id').val(custId);
    });
});
</script>
@endpush 