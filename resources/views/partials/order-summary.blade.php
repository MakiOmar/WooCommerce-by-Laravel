<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Order Summary</h5>
    </div>
    <div class="card-body">
        @php
            $status_label = str_replace('wc-', '', $order->post_status);
            $status_class = 'secondary'; // default
            if (isset(config('woo-order-dashboard.status_colors')[$status_label])) {
                $status_class = config('woo-order-dashboard.status_colors')[$status_label];
            }
            $currency = $order->meta->where('meta_key', '_order_currency')->first()->meta_value ?? '';
            $total = $order->meta->where('meta_key', '_order_total')->first()->meta_value ?? 0;
            $payment_method = $order->meta->where('meta_key', '_payment_method_title')->first()->meta_value ?? 'N/A';
        @endphp
        <div class="row">
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Order ID:</dt>
                    <dd class="col-sm-8">{{ $order->ID }}</dd>

                    <dt class="col-sm-4">Date Created:</dt>
                    <dd class="col-sm-8">{{ \Carbon\Carbon::parse($order->post_date)->format('M d, Y H:i') }}</dd>

                    <dt class="col-sm-4">Status:</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-{{ $status_class }}">
                            {{ ucwords($status_label) }}
                        </span>
                    </dd>
                </dl>
            </div>
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Payment Method:</dt>
                    <dd class="col-sm-8">{{ $payment_method }}</dd>

                    <dt class="col-sm-4">Total Items:</dt>
                    <dd class="col-sm-8">{{ $order->items->where('order_item_type', 'line_item')->count() }}</dd>

                    <dt class="col-sm-4">Total Amount:</dt>
                    <dd class="col-sm-8">{{ $currency }} {{ number_format($total, 2) }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

