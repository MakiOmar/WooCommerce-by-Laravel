<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Order Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($order['date_created'])->format('M d, Y H:i') }}</p>
                <p><strong>Payment Method:</strong> {{ $order['payment_method_title'] }}</p>
                <p><strong>Transaction ID:</strong> {{ $order['transaction_id'] ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Shipping Method:</strong> {{ $order['shipping_method'] }}</p>
                <p><strong>Currency:</strong> {{ $order['currency'] }}</p>
                <p><strong>Status:</strong> 
                    <span class="badge badge-{{ $order['status'] === 'completed' ? 'success' : 
                        ($order['status'] === 'processing' ? 'primary' : 
                        ($order['status'] === 'cancelled' ? 'danger' : 'secondary')) }}">
                        {{ ucfirst($order['status']) }}
                    </span>
                </p>
            </div>
        </div>
    </div>
</div> 