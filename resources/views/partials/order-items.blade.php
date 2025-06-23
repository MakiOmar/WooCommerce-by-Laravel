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
                        <th class="border-0 text-center">Quantity</th>
                        <th class="border-0 text-right">Price</th>
                        <th class="border-0 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items->where('order_item_type', 'line_item') as $item)
                    <tr>
                        <td class="align-middle">
                            <h6 class="mb-0">{{ $item->order_item_name }}</h6>
                        </td>
                        <td class="align-middle text-center">
                            @php
                                $qty = $item->meta->where('meta_key', '_qty')->first()->meta_value ?? 1;
                            @endphp
                            <span class="badge badge-info">{{ $qty }}</span>
                        </td>
                        <td class="align-middle text-right">
                            @php
                                $total = $item->meta->where('meta_key', '_line_total')->first()->meta_value ?? 0;
                                $price = $qty > 0 ? $total / $qty : 0;
                                $currency = $order->meta->where('meta_key', '_order_currency')->first()->meta_value ?? '';
                            @endphp
                            {{ $currency }} {{ number_format($price, 2) }}
                        </td>
                        <td class="align-middle text-right">
                            <strong>{{ $currency }} {{ number_format($total, 2) }}</strong>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light">
                    @php
                        $subtotal = $order->items->where('order_item_type', 'line_item')->sum(function($item) {
                            return $item->meta->where('meta_key', '_line_total')->first()->meta_value ?? 0;
                        });
                        $shipping = $order->meta->where('meta_key', '_order_shipping')->first()->meta_value ?? 0;
                        $discount = $order->meta->where('meta_key', '_cart_discount')->first()->meta_value ?? 0;
                        $total = $order->meta->where('meta_key', '_order_total')->first()->meta_value ?? 0;
                        $currency = $order->meta->where('meta_key', '_order_currency')->first()->meta_value ?? '';
                    @endphp
                    <tr>
                        <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                        <td class="text-right">{{ $currency }} {{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @if($shipping > 0)
                    <tr>
                        <td colspan="3" class="text-right"><strong>Shipping:</strong></td>
                        <td class="text-right">{{ $currency }} {{ number_format($shipping, 2) }}</td>
                    </tr>
                    @endif
                    @if($discount > 0)
                    <tr>
                        <td colspan="3" class="text-right"><strong>Discount:</strong></td>
                        <td class="text-right text-danger">-{{ $currency }} {{ number_format($discount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total:</strong></td>
                        <td class="text-right"><strong>{{ $currency }} {{ number_format($total, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>