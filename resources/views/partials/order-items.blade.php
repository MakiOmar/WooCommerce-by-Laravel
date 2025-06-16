@include('woo-order-dashboard::partials.order-summary')
@include('woo-order-dashboard::partials.order-addresses')

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Order Items</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order['line_items'] as $item)
                        <tr>
                            <td>
                                {{ $item['name'] }}
                                @if($item['meta_data'])
                                    <div class="small text-muted">
                                        @foreach($item['meta_data'] as $meta)
                                            @if($meta['display_key'] && $meta['display_value'])
                                                <div>{{ $meta['display_key'] }}: {{ $meta['display_value'] }}</div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>{{ $order['currency'] }} {{ number_format($item['price'], 2) }}</td>
                            <td>{{ $order['currency'] }} {{ number_format($item['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                        <td>{{ $order['currency'] }} {{ number_format($order['subtotal'], 2) }}</td>
                    </tr>
                    @if($order['shipping_total'] > 0)
                        <tr>
                            <td colspan="3" class="text-right"><strong>Shipping:</strong></td>
                            <td>{{ $order['currency'] }} {{ number_format($order['shipping_total'], 2) }}</td>
                        </tr>
                    @endif
                    @if($order['total_tax'] > 0)
                        <tr>
                            <td colspan="3" class="text-right"><strong>Tax:</strong></td>
                            <td>{{ $order['currency'] }} {{ number_format($order['total_tax'], 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total:</strong></td>
                        <td><strong>{{ $order['currency'] }} {{ number_format($order['total'], 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>