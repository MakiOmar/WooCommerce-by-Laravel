<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Order Summary</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Order ID:</dt>
                    <dd class="col-sm-8">{{ $order['id'] }}</dd>

                    <dt class="col-sm-4">Date Created:</dt>
                    <dd class="col-sm-8">{{ \Carbon\Carbon::parse($order['date_created'])->format('M d, Y H:i') }}</dd>

                    <dt class="col-sm-4">Status:</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-{{ $order['status'] === 'completed' ? 'success' : 'primary' }}">
                            {{ ucfirst($order['status']) }}
                        </span>
                    </dd>
                </dl>
            </div>
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Payment Method:</dt>
                    <dd class="col-sm-8">{{ $order['payment_method_title'] }}</dd>

                    <dt class="col-sm-4">Shipping Method:</dt>
                    <dd class="col-sm-8">{{ $order['shipping_method'] }}</dd>

                    <dt class="col-sm-4">Total Items:</dt>
                    <dd class="col-sm-8">{{ count($order['line_items']) }}</dd>

                    <dt class="col-sm-4">Total Amount:</dt>
                    <dd class="col-sm-8">{{ $order['currency'] }} {{ number_format($order['total'], 2) }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

