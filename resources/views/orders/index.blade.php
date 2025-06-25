@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Flash Messages -->
    @include('woo-order-dashboard::partials.flash-messages')

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-filter mr-2"></i>Filter Orders
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('orders.index') }}" method="GET" class="form-horizontal">
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
                                    <input type="text" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date" class="font-weight-bold">End Date</label>
                                    <input type="text" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status" class="font-weight-bold">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">All Statuses</option>
                                        @foreach($orderStatuses ?? [] as $statusKey => $statusLabel)
                                            <option value="{{ $statusKey }}" {{ request('status') == $statusKey ? 'selected' : '' }}>
                                                {{ $statusLabel }}
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
                                        @foreach(config('woo-order-dashboard.meta_keys', []) as $key => $label)
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
                                        <button type="submit" class="btn btn-primary" id="filter-btn">
                                            <i class="fas fa-search mr-1"></i> Filter
                                        </button>
                                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">
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
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-shopping-cart mr-2"></i>Orders
                        </h3>
                        <div class="bulk-actions" style="display: none;">
                            <div class="d-flex align-items-center">
                                <span class="mr-2 text-muted">
                                    <span id="selected-count">0</span> selected
                                </span>
                                <div class="dropdown">
                                    <button class="btn btn-outline-danger dropdown-toggle" type="button" id="bulkActionDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Bulk Actions
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="bulkActionDropdown">
                                        <a class="dropdown-item text-danger" href="#" id="bulk-delete">
                                            <i class="fas fa-trash mr-2"></i>Delete Selected
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0" style="width: 40px;">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="select-all">
                                            <label class="custom-control-label" for="select-all"></label>
                                        </div>
                                    </th>
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
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input order-checkbox" id="order-{{ $order['id'] }}" value="{{ $order['id'] }}">
                                                <label class="custom-control-label" for="order-{{ $order['id'] }}"></label>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="font-weight-bold">#{{ $order['id'] }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <i class="far fa-calendar-alt text-muted mr-1"></i>
                                            {{ \Carbon\Carbon::parse($order['date_created'])->format('M d, Y H:i') }}
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-{{ config('woo-order-dashboard.status_colors.' . $order['status'], 'secondary') }}">
                                                {{ $orderStatuses[$order['status']] ?? ucfirst($order['status']) }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="font-weight-bold">{{ $order['currency'] }} {{ number_format($order['total'], 2) }}</span>
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
                                            <a href="{{ route('orders.show', $order['id']) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
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
                            {{ $orders['data']->links('woo-order-dashboard::vendor.pagination.bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" role="dialog" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger mr-2"></i>Confirm Bulk Delete
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong><span id="delete-count">0</span> selected orders</strong>?</p>
                <p class="text-danger mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    This action cannot be undone. All order data including items, notes, and meta information will be permanently deleted.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-bulk-delete">
                    <i class="fas fa-trash mr-1"></i>Delete Orders
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<link href="{{ asset('vendor/woo-order-dashboard/css/woo-order-dashboard.css') }}" rel="stylesheet">
@endsection

@section('scripts')
@if(config('woo-order-dashboard.js_mode', 'inline'))
    @include('woo-order-dashboard::partials.woo-order-dashboard-inline-js')
@else
    <script src="{{ asset('vendor/woo-order-dashboard/js/loading-utils.js') }}"></script>
@endif
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    $(document).ready(function() {
        // Filter form loading state
        $('form[action="{{ route("orders.index") }}"]').on('submit', function() {
            loadingManager.showButtonLoading('#filter-btn', 'Filtering...');
        });

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

        // Bulk actions functionality
        var selectedOrders = [];

        // Handle select all checkbox
        $('#select-all').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('.order-checkbox').prop('checked', isChecked);
            
            if (isChecked) {
                selectedOrders = $('.order-checkbox').map(function() {
                    return $(this).val();
                }).get();
            } else {
                selectedOrders = [];
            }
            
            updateBulkActions();
        });

        // Handle individual order checkboxes
        $(document).on('change', '.order-checkbox', function() {
            var orderId = $(this).val();
            
            if ($(this).is(':checked')) {
                if (selectedOrders.indexOf(orderId) === -1) {
                    selectedOrders.push(orderId);
                }
            } else {
                selectedOrders = selectedOrders.filter(function(id) {
                    return id !== orderId;
                });
            }
            
            // Update select all checkbox
            var totalCheckboxes = $('.order-checkbox').length;
            var checkedCheckboxes = $('.order-checkbox:checked').length;
            
            if (checkedCheckboxes === 0) {
                $('#select-all').prop('indeterminate', false).prop('checked', false);
            } else if (checkedCheckboxes === totalCheckboxes) {
                $('#select-all').prop('indeterminate', false).prop('checked', true);
            } else {
                $('#select-all').prop('indeterminate', true);
            }
            
            updateBulkActions();
        });

        // Update bulk actions visibility and count
        function updateBulkActions() {
            var count = selectedOrders.length;
            $('#selected-count').text(count);
            $('#delete-count').text(count);
            
            if (count > 0) {
                $('.bulk-actions').show();
            } else {
                $('.bulk-actions').hide();
            }
        }

        // Handle bulk delete
        $('#bulk-delete').on('click', function(e) {
            e.preventDefault();
            if (selectedOrders.length > 0) {
                $('#bulkDeleteModal').modal('show');
            }
        });

        // Confirm bulk delete
        $('#confirm-bulk-delete').on('click', function() {
            var button = $(this);
            
            // Show loading state using loading manager
            loadingManager.showButtonLoading('#confirm-bulk-delete', 'Deleting Orders...');
            
            $.ajax({
                url: '{{ route("orders.bulk-delete") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_ids: selectedOrders
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                            '<i class="fas fa-check-circle"></i> ' + response.message +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                            '<span aria-hidden="true">&times;</span></button></div>')
                            .insertAfter('.container-fluid .row:first-child');
                        
                        // Reload the page to show updated data
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    var message = 'An error occurred while deleting orders.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    alert('Error: ' + message);
                },
                complete: function() {
                    // Hide loading state
                    loadingManager.hideButtonLoading('#confirm-bulk-delete');
                    $('#bulkDeleteModal').modal('hide');
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        flatpickr('#start_date', {
            dateFormat: "Y-m-d",
            allowInput: true
        });
        flatpickr('#end_date', {
            dateFormat: "Y-m-d",
            allowInput: true
        });
    });
</script>
@endsection 