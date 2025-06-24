<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-info-circle mr-2"></i>Order Information
        </h5>
        <div class="d-flex align-items-center">
            @php
                $status_label = \Makiomar\WooOrderDashboard\Helpers\Orders\StatusHelper::removeStatusPrefix($order->post_status);
                $status_class = 'secondary'; // default
                if (isset(config('woo-order-dashboard.status_colors')[$status_label])) {
                    $status_class = config('woo-order-dashboard.status_colors')[$status_label];
                }
            @endphp
            <span class="badge badge-{{ $status_class }} mr-2" id="current-status-badge">
                {{ $orderStatuses[$status_label] ?? ucwords($status_label) }}
            </span>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="statusDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-edit"></i> Change Status
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="statusDropdown">
                    @foreach($orderStatuses as $status_key => $status_label)
                        @php
                            $status_class = 'secondary';
                            if (isset(config('woo-order-dashboard.status_colors')[$status_key])) {
                                $status_class = config('woo-order-dashboard.status_colors')[$status_key];
                            }
                            $is_current = $status_key === \Makiomar\WooOrderDashboard\Helpers\Orders\StatusHelper::removeStatusPrefix($order->post_status);
                        @endphp
                        <a class="dropdown-item status-option {{ $is_current ? 'active' : '' }}" 
                           href="#" 
                           data-status="{{ \Makiomar\WooOrderDashboard\Helpers\Orders\StatusHelper::getStatusWithPrefix($status_key) }}"
                           data-status-key="{{ $status_key }}"
                           data-status-label="{{ $status_label }}">
                            <span class="badge badge-{{ $status_class }} mr-2">{{ $status_label }}</span>
                            @if($is_current)
                                <i class="fas fa-check text-success"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <small class="text-muted d-block">Order ID</small>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-hashtag text-primary mr-2"></i>
                        <strong>#{{ $order->ID }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Date Created</small>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt text-primary mr-2"></i>
                        {{ \Carbon\Carbon::parse($order->post_date)->format('M d, Y H:i') }}
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Payment Method</small>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-credit-card text-primary mr-2"></i>
                        {{ $order->meta->where('meta_key', '_payment_method_title')->first()->meta_value ?? 'N/A' }}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <small class="text-muted d-block">Total Items</small>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-boxes text-primary mr-2"></i>
                        <strong>{{ $order->items->where('order_item_type', 'line_item')->count() }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Currency</small>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-money-bill-wave text-primary mr-2"></i>
                        {{ $order->meta->where('meta_key', '_order_currency')->first()->meta_value ?? 'N/A' }}
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Total Amount</small>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-tag text-primary mr-2"></i>
                        <strong>
                            {{ $order->meta->where('meta_key', '_order_currency')->first()->meta_value ?? '' }} 
                            {{ number_format($order->meta->where('meta_key', '_order_total')->first()->meta_value ?? 0, 2) }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 