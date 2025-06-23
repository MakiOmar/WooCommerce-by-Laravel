@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Flash Messages -->
    @include('woo-order-dashboard::partials.flash-messages')

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-shopping-cart mr-2"></i>WooCommerce Orders
                        </h3>
                        <div class="bulk-actions" style="display: none;">
                            <div class="d-flex align-items-center">
                                <span class="mr-2 text-muted">
                                    <span id="selected-count">0</span> selected
                                </span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-danger dropdown-toggle" type="button" id="bulkActionDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Bulk Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="bulkActionDropdown">
                                        <a class="dropdown-item text-danger" href="#" id="bulk-delete">
                                            <i class="fas fa-trash-alt mr-2"></i>Delete Selected
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters Section -->
                    <form action="{{ route('orders.index') }}" method="GET" class="mb-4">
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
                                <a href="{{ route('orders.index') }}" class="btn btn-light">
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
                                @forelse($orders as $order)
                                    <tr>
                                        <td class="align-middle">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input order-checkbox" id="order-{{ $order->ID }}" value="{{ $order->ID }}">
                                                <label class="custom-control-label" for="order-{{ $order->ID }}"></label>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="font-weight-bold">#{{ $order->ID }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <i class="far fa-calendar-alt text-muted mr-1"></i>
                                            {{ \Carbon\Carbon::parse($order->post_date)->format('M d, Y H:i') }}
                                        </td>
                                        <td class="align-middle">
                                            @php
                                                $status_label = str_replace('wc-', '', $order->post_status);
                                                $status_class = 'default';
                                                if (isset(config('woo-order-dashboard.status_colors')[$status_label])) {
                                                    $status_class = config('woo-order-dashboard.status_colors')[$status_label];
                                                }
                                            @endphp
                                            <span class="badge badge-{{ $status_class }}">
                                                {{ ucwords($status_label) }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="font-weight-bold">${{ number_format($order->meta->where('meta_key', '_order_total')->first()->meta_value ?? 0, 2) }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex flex-column">
                                                <span class="font-weight-bold">
                                                    {{ $order->meta->where('meta_key', '_billing_first_name')->first()->meta_value ?? '' }} 
                                                    {{ $order->meta->where('meta_key', '_billing_last_name')->first()->meta_value ?? '' }}
                                                </span>
                                                <small class="text-muted">
                                                    <i class="far fa-envelope mr-1"></i>
                                                    {{ $order->meta->where('meta_key', '_billing_email')->first()->meta_value ?? 'N/A' }}
                                                </small>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="{{ route('orders.show', $order->ID) }}" class="btn btn-sm btn-info">
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

                    @if($orders->hasPages())
                        <div class="card-footer bg-light">
                            {{ $orders->links() }}
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

@push('styles')
<link href="{{ asset('css/woo-order-dashboard.css') }}" rel="stylesheet">
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        // Ensure all checkboxes are unchecked on page load
        $('.order-checkbox, #select-all').prop('checked', false);

        // Initialize Bootstrap dropdowns
        $('[data-toggle="dropdown"]').dropdown();

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

        function updateBulkActionsUI() {
            var count = selectedOrders.length;
            $('#selected-count').text(count);
            
            if (count > 0) {
                $('.bulk-actions').fadeIn('fast');
            } else {
                $('.bulk-actions').fadeOut('fast');
            }
        }

        $('#select-all').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('.order-checkbox').prop('checked', isChecked).trigger('change');
        });

        $(document).on('change', '.order-checkbox', function() {
            var orderId = $(this).val();
            
            if ($(this).is(':checked')) {
                if (!selectedOrders.includes(orderId)) {
                    selectedOrders.push(orderId);
                }
            } else {
                selectedOrders = selectedOrders.filter(id => id !== orderId);
            }
            
            var totalCheckboxes = $('.order-checkbox').length;
            var checkedCheckboxes = $('.order-checkbox:checked').length;
            
            $('#select-all').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
            
            updateBulkActionsUI();
        });

        $('#bulk-delete').on('click', function(e) {
            e.preventDefault();
            if (selectedOrders.length > 0) {
                $('#delete-count').text(selectedOrders.length);
                $('#bulkDeleteModal').modal('show');
            }
        });

        $('#confirm-bulk-delete').on('click', function() {
            var button = $(this);
            var originalText = button.html();
            
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Deleting...');
            
            $.ajax({
                url: '{{ route("woo.orders.bulk-delete") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_ids: selectedOrders
                },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    var message = 'An error occurred while deleting orders.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    alert('Error: ' + message);
                    button.prop('disabled', false).html(originalText);
                },
                complete: function() {
                    $('#bulkDeleteModal').modal('hide');
                }
            });
        });
    });
</script>
@endpush