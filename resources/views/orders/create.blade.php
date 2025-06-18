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
                        <table class="table table-bordered mb-3">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Cost</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Order items will be dynamically added here -->
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
                        <div class="d-flex justify-content-between">
                            <span>Subtotal</span>
                            <span>ج.م <span class="order-subtotal">0.00</span></span>
                        </div>
                        <div><a href="#" class="add-coupon">Add coupon</a></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Discount</span>
                            <input type="number" class="form-control form-control-sm order-discount ml-2" value="0" min="0" style="width:100px;display:inline-block;" name="discount">
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span>Shipping</span>
                            <input type="number" class="form-control form-control-sm order-shipping ml-2" value="0" min="0" style="width:100px;display:inline-block;" name="shipping">
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span>Taxes:</span>
                            <input type="number" class="form-control form-control-sm order-taxes ml-2" value="0" min="0" style="width:100px;display:inline-block;" name="taxes">
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between font-weight-bold">
                            <span>Order Total</span>
                            <span>ج.م <span class="order-total">0.00</span></span>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-block mt-3">Submit Order</button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(function() {
    // Product search dropdown
    var $prodInput = $('input[placeholder="Find products..."]');
    var $prodTable = $('table tbody');
    var $prodDropdown;

    function recalcSummary() {
        var subtotal = 0;
        $prodTable.find('tr').each(function() {
            var qty = parseInt($(this).find('.order-qty').val() || 1);
            var price = parseFloat($(this).find('.order-price').text() || 0);
            var total = qty * price;
            $(this).find('.order-total').text(total.toFixed(2));
            subtotal += total;
        });
        var discount = parseFloat($('.order-discount').val() || 0);
        var shipping = parseFloat($('.order-shipping').val() || 0);
        var taxes = parseFloat($('.order-taxes').val() || 0);
        var total = subtotal - discount + shipping + taxes;
        $('.order-subtotal').text(subtotal.toFixed(2));
        $('.order-total').text(total.toFixed(2));
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
        var row = '<tr data-id="'+id+'">'
            +'<td>'+name+'</td>'
            +'<td class="order-price">'+price+'</td>'
            +'<td><input type="number" class="form-control form-control-sm order-qty" value="1" min="1" style="width:70px;"></td>'
            +'<td class="order-total">'+price+'</td>'
            +'<td><button type="button" class="btn btn-sm btn-danger remove-item">&times;</button></td>'
            +'</tr>';
        $prodTable.append(row);
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

    $('.order-discount, .order-shipping, .order-taxes').on('input', recalcSummary);

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