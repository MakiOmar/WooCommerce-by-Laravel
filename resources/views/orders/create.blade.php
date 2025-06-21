@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Create New Order</h4>
                </div>
                <div class="card-body">
                    <form id="create-order-form" method="POST" action="{{ route('orders.store') }}">
                        @csrf
                        <input type="hidden" name="order_items" id="order_items">
                        <input type="hidden" name="customer_id" id="customer_id" value="1">

                        <div class="row">
                            <div class="col-md-8">
                                <!-- Product Search Section -->
                                <div class="form-group">
                                    <label for="product_search">Search Products</label>
                                    <div class="position-relative">
                                        <input type="text" id="product_search" class="form-control" placeholder="Search products by name or SKU...">
                                        <div id="product_search_dropdown" class="list-group position-absolute w-100" style="z-index:1000; display:none; max-height: 300px; overflow-y: auto;"></div>
                                    </div>
                                </div>

                                <!-- Order Items Table -->
                                <div class="table-responsive">
                                    <table id="products-table" class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Product</th>
                                                <th width="100">Price</th>
                                                <th width="80">Qty</th>
                                                <th width="100">Total</th>
                                                <th width="50"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Order items will be added here dynamically -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Order Summary -->
                                <div class="row mt-3">
                                    <div class="col-md-6 offset-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Subtotal:</strong></td>
                                                <td class="text-right">$<span id="subtotal_amount">0.00</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Tax:</strong></td>
                                                <td class="text-right">$<span id="tax_amount">0.00</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Shipping:</strong></td>
                                                <td class="text-right">$<span id="shipping_amount">0.00</span></td>
                                            </tr>
                                            <tr class="table-active">
                                                <td><strong>Total:</strong></td>
                                                <td class="text-right"><strong>$<span id="total_amount">0.00</span></strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Customer Information -->
                                <div class="form-group">
                                    <label for="customer_search">Customer</label>
                                    <input type="text" id="customer_search" class="form-control" placeholder="Search customers...">
                                    <div id="customer_details" class="mt-2"></div>
                                </div>

                                <!-- Order Notes -->
                                <div class="form-group">
                                    <label for="customer_note">Order Notes</label>
                                    <textarea id="customer_note" name="customer_note" class="form-control" rows="3" placeholder="Add any special instructions..."></textarea>
                                </div>

                                <!-- Order Status -->
                                <div class="form-group">
                                    <label for="order_status">Order Status</label>
                                    <select id="order_status" name="order_status" class="form-control">
                                        <option value="pending">Pending</option>
                                        <option value="processing" selected>Processing</option>
                                        <option value="completed">Completed</option>
                                        <option value="on-hold">On Hold</option>
                                    </select>
                                </div>

                                <!-- Submit Button -->
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-block" id="submit-btn">
                                        <i class="fas fa-save"></i> Create Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
$(document).ready(function() {
    var $prodSearch = $('#product_search');
    var $prodDropdown = $('#product_search_dropdown');
    var $prodTable = $('#products-table');
    var $orderItemsInput = $('#order_items');
    var $totalAmountSpan = $('#total_amount');
    var $subtotalSpan = $('#subtotal_amount');
    var $taxSpan = $('#tax_amount');
    var $shippingSpan = $('#shipping_amount');
    var $customerSearch = $('#customer_search');
    var $customerDetails = $('#customer_details');
    var $submitBtn = $('#submit-btn');

    // Product search
    $prodSearch.on('keyup', function() {
        var q = $(this).val();
        if (q.length < 2) {
            $prodDropdown.hide();
            return;
        }
        
        $.getJSON("{{ route('orders.products.search') }}", {q: q}, function(data) {
            $prodDropdown.empty().show();
            if (data.length === 0) {
                $prodDropdown.append('<div class="list-group-item text-muted">No products found</div>');
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

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$prodSearch.is(e.target) && $prodSearch.has(e.target).length === 0 && !$prodDropdown.is(e.target) && $prodDropdown.has(e.target).length === 0) {
            $prodDropdown.hide();
        }
    });

    // Add product to table
    $('body').on('click', '.prod-item', function() {
        var productId = $(this).data('product-id');
        var variationId = $(this).data('variation-id');
        var name = $(this).data('name');
        var price = parseFloat($(this).data('price')) || 0;
        var attributes = $(this).data('attributes') ? JSON.parse($(this).attr('data-attributes')) : {};

        var rowId = variationId > 0 ? variationId : productId;
        var $existingRow = $prodTable.find('tr[data-row-id="'+rowId+'"]');

        if ($existingRow.length > 0) {
            var $qtyInput = $existingRow.find('.order-qty');
            var currentQty = parseInt($qtyInput.val());
            $qtyInput.val(currentQty + 1).trigger('change');
        } else {
            var attrHtml = '';
            if (Object.keys(attributes).length > 0) {
                attrHtml = '<br><small class="text-info">' + Object.entries(attributes).map(function([k, v]) {
                    return k.replace('attribute_', '') + ': ' + v;
                }).join(', ') + '</small>';
            }
            
            var row = '<tr data-row-id="'+rowId+'" data-product-id="'+productId+'" data-variation-id="'+variationId+'" data-attributes=\''+JSON.stringify(attributes)+'\'>' +
                '<td><strong>'+name+'</strong>'+attrHtml+'</td>' +
                '<td class="order-price text-right">$'+price.toFixed(2)+'</td>' +
                '<td><input type="number" class="form-control form-control-sm order-qty" value="1" min="1" style="width: 60px;"></td>' +
                '<td class="order-line-total text-right">$'+price.toFixed(2)+'</td>' +
                '<td><button type="button" class="btn btn-danger btn-sm remove-item">&times;</button></td>' +
                '</tr>';
            $prodTable.find('tbody').append(row);
        }
        
        updateTotals();
        $prodSearch.val('');
        $prodDropdown.hide();
    });

    // Update totals when quantity changes
    $prodTable.on('change keyup', '.order-qty', function() {
        var $row = $(this).closest('tr');
        var qty = parseInt($(this).val()) || 0;
        var price = parseFloat($row.find('.order-price').text().replace('$', '')) || 0;
        var lineTotal = qty * price;
        $row.find('.order-line-total').text('$' + lineTotal.toFixed(2));
        updateTotals();
    });

    // Remove item from table
    $prodTable.on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        updateTotals();
    });

    // Calculate and update all totals
    function updateTotals() {
        var subtotal = 0;
        $prodTable.find('.order-line-total').each(function() {
            subtotal += parseFloat($(this).text().replace('$', '')) || 0;
        });
        
        var tax = subtotal * 0.1; // 10% tax - adjust as needed
        var shipping = 0; // Add shipping calculation if needed
        
        $subtotalSpan.text(subtotal.toFixed(2));
        $taxSpan.text(tax.toFixed(2));
        $shippingSpan.text(shipping.toFixed(2));
        $totalAmountSpan.text((subtotal + tax + shipping).toFixed(2));
    }

    // Customer search
    $customerSearch.on('keyup', function() {
        var q = $(this).val();
        if (q.length < 2) {
            $customerDetails.empty();
            return;
        }
        
        $.getJSON("{{ route('orders.customers.search') }}", {q: q}, function(data) {
            if (data.length > 0) {
                var customer = data[0]; // Use first result
                $customerSearch.val(customer.name);
                $('#customer_id').val(customer.id);
                $customerDetails.html('<div class="alert alert-info p-2"><strong>'+customer.name+'</strong><br><small>'+customer.email+'</small></div>');
            }
        });
    });

    // Form submission
    $('#create-order-form').submit(function(e) {
        e.preventDefault();
        
        var items = [];
        $prodTable.find('tbody tr').each(function() {
            items.push({
                product_id: $(this).data('product-id'),
                variation_id: $(this).data('variation-id'),
                name: $(this).find('td').eq(0).text().trim(),
                price: parseFloat($(this).find('.order-price').text().replace('$', '')) || 0,
                qty: parseInt($(this).find('.order-qty').val()) || 1,
                attributes: $(this).data('attributes') ? JSON.parse($(this).attr('data-attributes')) : {}
            });
        });

        if (items.length === 0) {
            alert('Please add at least one product to the order.');
            return false;
        }

        $orderItemsInput.val(JSON.stringify(items));
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
        
        // Submit the form
        this.submit();
    });
});
</script>
@endpush

@push('css')
<style>
.prod-item:hover {
    background-color: #f8f9fa;
    cursor: pointer;
}
.prod-item:active {
    background-color: #e9ecef;
}
#product_search_dropdown {
    border: 1px solid #ddd;
    border-top: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.order-qty {
    text-align: center;
}
</style>
@endpush
@endsection