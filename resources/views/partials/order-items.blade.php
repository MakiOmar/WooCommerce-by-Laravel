@include('woo-order-dashboard::partials.order-summary')

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-shopping-cart mr-2"></i>Order Items
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="border-0">Product</th>
                        <th class="border-0 text-center">SKU</th>
                        <th class="border-0 text-center">Quantity</th>
                        <th class="border-0 text-right">Price</th>
                        <th class="border-0 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order['line_items'] as $item)
                    <tr>
                        <td class="align-middle">
                            <div class="d-flex align-items-center">
                                @if(isset($item['image']) && $item['image']['src'])
                                    <img src="{{ $item['image']['src'] }}" alt="{{ $item['name'] }}" 
                                         class="img-thumbnail mr-3" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded mr-3 d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-box text-muted"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-0">{{ $item['name'] }}</h6>
                                    @if(isset($item['meta_data']) && count($item['meta_data']) > 0)
                                        <small class="text-muted">
                                            @foreach($item['meta_data'] as $meta)
                                                @if($meta['display_key'] && $meta['display_value'])
                                                    {{ $meta['display_key'] }}: {{ $meta['display_value'] }}<br>
                                                @endif
                                            @endforeach
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="align-middle text-center">
                            <span class="badge badge-light">{{ $item['sku'] ?? 'N/A' }}</span>
                        </td>
                        <td class="align-middle text-center">
                            <span class="badge badge-info">{{ $item['quantity'] }}</span>
                        </td>
                        <td class="align-middle text-right">
                            {{ $order['currency'] }} {{ number_format($item['price'], 2) }}
                        </td>
                        <td class="align-middle text-right">
                            <strong>{{ $order['currency'] }} {{ number_format($item['total'], 2) }}</strong>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                        <td class="text-right">{{ $order['currency'] }} {{ number_format($order['subtotal'], 2) }}</td>
                    </tr>
                    @if($order['shipping_total'] > 0)
                    <tr>
                        <td colspan="4" class="text-right"><strong>Shipping:</strong></td>
                        <td class="text-right">{{ $order['currency'] }} {{ number_format($order['shipping_total'], 2) }}</td>
                    </tr>
                    @endif
                    @if($order['discount_total'] > 0)
                    <tr>
                        <td colspan="4" class="text-right"><strong>Discount:</strong></td>
                        <td class="text-right text-danger">-{{ $order['currency'] }} {{ number_format($order['discount_total'], 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="4" class="text-right"><strong>Total:</strong></td>
                        <td class="text-right"><strong>{{ $order['currency'] }} {{ number_format($order['total'], 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>