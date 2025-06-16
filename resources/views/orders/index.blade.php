@extends('woo-order-dashboard::layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Orders</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('woo.orders') }}" method="GET" class="form-horizontal">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="order_id">Order ID</label>
                                    <input type="text" class="form-control" id="order_id" name="order_id" value="{{ request('order_id') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Status</label>
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
                                    <label for="meta_key">Meta Key</label>
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
                                    <label for="meta_value">Meta Value</label>
                                    <input type="text" class="form-control" id="meta_value" name="meta_value" value="{{ request('meta_value') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="per_page">Per Page</label>
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
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="{{ route('woo.orders') }}" class="btn btn-secondary">Reset</a>
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
                <div class="card-header">
                    <h3 class="card-title">Orders</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Customer</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders['data'] as $order)
                                    <tr>
                                        <td>#{{ $order['number'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($order['date_created'])->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            <span class="badge badge-{{ config('woo-order-dashboard.status_colors.' . $order['status'], 'secondary') }}">
                                                {{ ucfirst($order['status']) }}
                                            </span>
                                        </td>
                                        <td>{{ $order['currency'] }} {{ number_format($order['total'], 2) }}</td>
                                        <td>
                                            {{ $order['billing']['first_name'] }} {{ $order['billing']['last_name'] }}
                                            <br>
                                            <small>{{ $order['billing']['email'] }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('woo.orders.show', $order['id']) }}" class="btn btn-sm btn-info">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No orders found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
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