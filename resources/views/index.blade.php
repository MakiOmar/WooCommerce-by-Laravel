@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="card border-0 shadow-lg">
        <div class="card-body p-4">
            <h1 class="h4 font-weight-bold text-primary mb-4">WooCommerce Order Dashboard</h1>

            <!-- Debug Info -->
            @if(config('app.debug'))
                <div class="alert alert-info mb-4">
                    <h5>Debug Information:</h5>
                    <pre>{{ print_r($orders, true) }}</pre>
                </div>
            @endif

            <!-- Filters Section -->
            <form action="{{ route('woo.orders') }}" method="GET" class="mb-4">
                <div class="row">
                    <!-- Order ID -->
                    <div class="form-group col-md-3">
                        <label for="order_id" class="font-weight-600 text-muted small">Order ID</label>
                        <input type="number" name="order_id" id="order_id" value="{{ request('order_id') }}" 
                               class="form-control form-control-sm rounded-lg border-light">
                    </div>

                    <!-- Start Date -->
                    <div class="form-group col-md-3">
                        <label for="start_date" class="font-weight-600 text-muted small">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" 
                               class="form-control form-control-sm rounded-lg border-light">
                    </div>

                    <!-- End Date -->
                    <div class="form-group col-md-3">
                        <label for="end_date" class="font-weight-600 text-muted small">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" 
                               class="form-control form-control-sm rounded-lg border-light">
                    </div>

                    <!-- Status -->
                    <div class="form-group col-md-3">
                        <label for="status" class="font-weight-600 text-muted small">Status</label>
                        <select name="status" id="status" class="form-control form-control-sm rounded-lg border-light">
                            <option value="">All Statuses</option>
                            @foreach(config('woo-order-dashboard.order_statuses', []) as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Meta Key -->
                    <div class="form-group col-md-3">
                        <label for="meta_key" class="font-weight-600 text-muted small">Meta Key</label>
                        <select name="meta_key" id="meta_key" class="form-control form-control-sm rounded-lg border-light">
                            <option value="">Select Meta Key</option>
                            <option value="billing_phone" {{ request('meta_key') == 'billing_phone' ? 'selected' : '' }}>Billing Phone</option>
                            <option value="_wcpdf_invoice_number" {{ request('meta_key') == '_wcpdf_invoice_number' ? 'selected' : '' }}>Invoice Number</option>
                            <option value="odoo_order_number" {{ request('meta_key') == 'odoo_order_number' ? 'selected' : '' }}>Odoo Order Number</option>
                        </select>
                    </div>

                    <!-- Meta Value -->
                    <div class="form-group col-md-3">
                        <label for="meta_value" class="font-weight-600 text-muted small">Meta Value</label>
                        <input type="text" name="meta_value" id="meta_value" value="{{ request('meta_value') }}" 
                               class="form-control form-control-sm rounded-lg border-light">
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter mr-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('woo.orders') }}" class="btn btn-light">
                            <i class="fas fa-times mr-1"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>

            <!-- Orders Table -->
            <div class="table-responsive rounded-lg overflow-hidden border border-light">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 font-weight-600 text-muted small">Order ID</th>
                            <th class="border-0 font-weight-600 text-muted small">Date</th>
                            <th class="border-0 font-weight-600 text-muted small">Status</th>
                            <th class="border-0 font-weight-600 text-muted small">Total</th>
                            <th class="border-0 font-weight-600 text-muted small">Customer</th>
                            <th class="border-0 font-weight-600 text-muted small">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders['data'] ?? [] as $order)
                            <tr class="border-bottom border-light">
                                <td class="align-middle font-weight-bold">#{{ $order['id'] }}</td>
                                <td class="align-middle">{{ \Carbon\Carbon::parse($order['date_created'])->format(config('woo-order-dashboard.date_format.display')) }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-pill py-1 px-3 
                                        {{ $order['status'] == 'completed' ? 'badge-success bg-success-soft' : 
                                            ($order['status'] == 'processing' ? 'badge-primary bg-primary-soft' :
                                            ($order['status'] == 'cancelled' ? 'badge-danger bg-danger-soft' : 'badge-secondary bg-secondary-soft')) }}">
                                        {{ ucfirst($order['status']) }}
                                    </span>
                                </td>
                                <td class="align-middle font-weight-bold text-dark">${{ number_format($order['total'], 2) }}</td>
                                <td class="align-middle">{{ $order['billing']['first_name'] }} {{ $order['billing']['last_name'] }}</td>
                                <td class="align-middle">
                                    <a href="{{ route('woo.orders.show', $order['id']) }}" 
                                       class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">No orders found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($orders['data']) && $orders['data']->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $orders['data']->links('vendor.pagination.bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection