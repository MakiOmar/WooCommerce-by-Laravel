<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-info-circle mr-2"></i>Order Information
        </h5>
        <span class="badge badge-{{ $order['status'] === 'completed' ? 'success' : 
            ($order['status'] === 'processing' ? 'primary' : 
            ($order['status'] === 'cancelled' ? 'danger' : 'secondary')) }}">
            <i class="fas {{ $order['status'] === 'completed' ? 'fa-check-circle' : 
                ($order['status'] === 'processing' ? 'fa-cog' : 
                ($order['status'] === 'cancelled' ? 'fa-times-circle' : 'fa-clock')) }} mr-1"></i>
            {{ ucfirst($order['status']) }}
        </span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <small class="text-muted d-block">Date Created</small>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt text-primary mr-2"></i>
                        {{ \Carbon\Carbon::parse($order['date_created'])->format('M d, Y H:i') }}
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Payment Method</small>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-credit-card text-primary mr-2"></i>
                        {{ $order['payment_method_title'] }}
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Transaction ID</small>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-receipt text-primary mr-2"></i>
                        {{ $order['transaction_id'] ?? 'N/A' }}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <small class="text-muted d-block">Shipping Method</small>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-truck text-primary mr-2"></i>
                        {{ $order['shipping_method'] }}
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Currency</small>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-money-bill-wave text-primary mr-2"></i>
                        {{ $order['currency'] }}
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Total Amount</small>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-tag text-primary mr-2"></i>
                        <strong>{{ $order['currency'] }} {{ number_format($order['total'], 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 