@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-shopping-cart mr-2"></i>WooCommerce Orders
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Filters Section -->
                    <form action="{{ route('woo.orders') }}" method="GET" class="mb-4">
                        <div class="row">
                            <!-- Order ID -->
                            <div class="form-group col-md-3">
                                <label for="order_id" class="font-weight-bold">Order ID</label>
                                <input type="number" name="order_id" id="order_id" value="{{ request('order_id') }}" 
                                       class="form-control">
                            </div>

                            <!-- Start Date -->
                            <div class="form-group col-md-3">
                                <label for="start_date" class="font-weight-bold">Start Date</label>
                                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" 
                                       class="form-control">
                            </div>

                            <!-- End Date -->
                            <div class="form-group col-md-3">
                                <label for="end_date" class="font-weight-bold">End Date</label>
                                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" 
                                       class="form-control">
                            </div>

                            <!-- Status -->
                            <div class="form-group col-md-3">
                                <label for="status" class="font-weight-bold">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    @foreach(app(\Makiomar\WooOrderDashboard\Helpers\Orders\StatusHelper::class)->getAllStatuses() as $statusKey => $statusLabel)
                                        <option value="{{ $statusKey }}" {{ request('status') == $statusKey ? 'selected' : '' }}>
                                            {{ $statusLabel }}
                                        </option>
                                    @endforeach
                                </select>
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
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">Order ID</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Total</th>
                                    <th class="border-0">Customer</th>
                                    <th class="border-0 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders['data'] ?? [] as $order)
                                    <tr>
                                        <td class="align-middle">
                                            <span class="font-weight-bold">#{{ $order['id'] }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <i class="far fa-calendar-alt text-muted mr-1"></i>
                                            {{ \Carbon\Carbon::parse($order['date_created'])->format('M d, Y H:i') }}
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-{{ config('woo-order-dashboard.status_colors.' . $order['status'], 'default') }}">
                                                {{ app(\Makiomar\WooOrderDashboard\Helpers\Orders\StatusHelper::class)->getStatusLabel($order['status']) }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="font-weight-bold">${{ number_format($order['total'], 2) }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex flex-column">
                                                <span class="font-weight-bold">
                                                    {{ $order['billing']['first_name'] ?? '' }} {{ $order['billing']['last_name'] ?? '' }}
                                                </span>
                                                <small class="text-muted">
                                                    <i class="far fa-envelope mr-1"></i>
                                                    {{ $order['billing']['email'] ?? 'N/A' }}
                                                </small>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="{{ route('woo.orders.show', $order['id']) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-box-open fa-2x mb-2"></i>
                                                <p class="mb-0">No orders found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(isset($orders['data']) && $orders['data']->hasPages())
                        <div class="card-footer bg-light">
                            {{ $orders['data']->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('css/woo-order-dashboard.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Date range picker initialization
        $('#start_date, #end_date').on('change', function() {
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();
            
            if (startDate && endDate) {
                if (startDate > endDate) {
                    alert('Start date cannot be greater than end date');
                    $(this).val('');
                }
            }
        });
    });
</script>
@endpush