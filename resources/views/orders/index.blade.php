@extends('woo-order-dashboard::layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-filter mr-2"></i>Filter Orders
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('woo.orders') }}" method="GET" class="form-horizontal">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="order_id" class="font-weight-bold">Order ID</label>
                                    <input type="text" class="form-control" id="order_id" name="order_id" value="{{ request('order_id') }}" placeholder="Enter order ID">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date" class="font-weight-bold">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date" class="font-weight-bold">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status" class="font-weight-bold">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">All Statuses</option>
                                        @foreach(config('woo-order-dashboard.order_statuses') as $status)
                                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="meta_key" class="font-weight-bold">Meta Key</label>
                                    <select class="form-control" id="meta_key" name="meta_key">
                                        <option value="">Select Meta Key</option>
                                        @foreach(config('woo-order-dashboard.meta_keys') as $key => $label)
                                            <option value="{{ $key }}" {{ request('meta_key') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="meta_value" class="font-weight-bold">Meta Value</label>
                                    <input type="text" class="form-control" id="meta_value" name="meta_value" value="{{ request('meta_value') }}" placeholder="Enter meta value">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="per_page" class="font-weight-bold">Per Page</label>
                                    <select class="form-control" id="per_page" name="per_page">
                                        @foreach([15, 25, 50, 100] as $value)
                                            <option value="{{ $value }}" {{ request('per_page', 15) == $value ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search mr-1"></i> Filter
                                        </button>
                                        <a href="{{ route('woo.orders') }}" class="btn btn-secondary">
                                            <i class="fas fa-redo mr-1"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-shopping-cart mr-2"></i>Orders
                    </h3>
                </div>
                <div class="card-body p-0">
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
                                @forelse($orders['data'] as $order)
                                    <tr>
                                        <td class="align-middle">
                                            <span class="font-weight-bold">#{{ $order['id'] }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <i class="far fa-calendar-alt text-muted mr-1"></i>
                                            {{ \Carbon\Carbon::parse($order['date_created'])->format('M d, Y H:i') }}
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-{{ config('woo-order-dashboard.status_colors.' . $order['status'], 'secondary') }}">
                                                {{ ucfirst($order['status']) }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="font-weight-bold">{{ $order['currency'] }} {{ number_format($order['total'], 2) }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex flex-column">
                                                <span class="font-weight-bold">
                                                    {{ $order['billing']['first_name'] }} {{ $order['billing']['last_name'] }}
                                                </span>
                                                <small class="text-muted">
                                                    <i class="far fa-envelope mr-1"></i>
                                                    {{ $order['billing']['email'] }}
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

                    <div class="card-footer bg-light">
                        {{ $orders['data']->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

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

        // Meta key value filter visibility
        $('#meta_key').on('change', function() {
            var metaKey = $(this).val();
            if (metaKey) {
                $('#meta_value').closest('.form-group').show();
            } else {
                $('#meta_value').closest('.form-group').hide();
            }
        }).trigger('change');
    });
</script>
@endpush 