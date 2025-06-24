@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Order #{{ $order->ID }}</h3>
                    <div>
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                        @if($order->post_status !== 'completed')
                            <button onclick="window.print()" class="btn btn-primary ml-2">
                                <i class="fas fa-print"></i> Print
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Status Change Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-edit mr-2"></i>Order Status
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <small class="text-muted d-block">Current Status</small>
                                            @php
                                                $status_label = \Makiomar\WooOrderDashboard\Helpers\Orders\StatusHelper::removeStatusPrefix($order->post_status);
                                                $status_class = 'secondary'; // default
                                                if (isset(config('woo-order-dashboard.status_colors')[$status_label])) {
                                                    $status_class = config('woo-order-dashboard.status_colors')[$status_label];
                                                }
                                            @endphp
                                            <span class="badge badge-{{ $status_class }} badge-lg" id="current-status-badge">
                                                {{ $orderStatuses[$status_label] ?? ucwords($status_label) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="dropdown">
                                                <button class="btn btn-primary dropdown-toggle" type="button" id="statusDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs" id="orderTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="order-info-tab" data-toggle="tab" href="#order-info" role="tab" aria-controls="order-info" aria-selected="true">
                                <i class="fas fa-info-circle mr-1"></i>Order Info & Items
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="customer-info-tab" data-toggle="tab" href="#customer-info" role="tab" aria-controls="customer-info" aria-selected="false">
                                <i class="fas fa-user mr-1"></i>Customer Info
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="order-notes-tab" data-toggle="tab" href="#order-notes" role="tab" aria-controls="order-notes" aria-selected="false">
                                <i class="fas fa-sticky-note mr-1"></i>Order Notes
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="orderTabsContent">
                        <!-- Tab 1: Order Info & Items -->
                        <div class="tab-pane fade show active" id="order-info" role="tabpanel" aria-labelledby="order-info-tab">
                            <div class="mt-4">
                                @include('woo-order-dashboard::partials.order-items', ['order' => $order])
                                @include('woo-order-dashboard::partials.order-meta', ['order' => $order])
                            </div>
                        </div>

                        <!-- Tab 2: Customer Info -->
                        <div class="tab-pane fade" id="customer-info" role="tabpanel" aria-labelledby="customer-info-tab">
                            <div class="mt-4">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading customer information...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Order Notes -->
                        <div class="tab-pane fade" id="order-notes" role="tabpanel" aria-labelledby="order-notes-tab">
                            <div class="mt-4">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading order notes...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link href="{{ asset('css/woo-order-dashboard.css') }}" rel="stylesheet">
<!-- Bootstrap 4 and jQuery dependencies -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    .nav-tabs {
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 0;
        background-color: #f8f9fa;
        padding: 0 1.25rem;
        padding-top: 1.25rem;
    }
    .nav-tabs .nav-item {
        margin-bottom: -2px;
    }
    .nav-tabs .nav-link {
        color: #495057 !important;
        background-color: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 0.75rem 1.25rem;
        font-weight: 500;
        transition: all 0.2s ease-in-out;
        text-decoration: none;
    }
    .nav-tabs .nav-link:hover {
        color: #007bff !important;
        background-color: transparent;
        border-color: transparent;
        border-bottom-color: #dee2e6;
        text-decoration: none;
    }
    .nav-tabs .nav-link.active {
        color: #007bff !important;
        background-color: #fff;
        border-color: transparent;
        border-bottom-color: #007bff;
        font-weight: 600;
        text-decoration: none;
    }
    .nav-tabs .nav-link.active:hover {
        color: #007bff !important;
        background-color: #fff;
        border-color: transparent;
        border-bottom-color: #007bff;
        text-decoration: none;
    }
    .tab-content {
        background-color: #fff;
        border: 1px solid #e9ecef;
        border-top: none;
        border-radius: 0 0 0.375rem 0.375rem;
        padding: 1.5rem;
        min-height: 400px;
    }
    .tab-pane {
        min-height: 400px;
    }
    .tab-pane.fade {
        opacity: 0;
        transition: opacity 0.15s linear;
    }
    .tab-pane.fade.show {
        opacity: 1;
    }

    .card-body .tab-content {
        margin: 0;
        border-left: none;
        border-right: none;
        border-bottom: none;
        border-radius: 0;
    }
    .nav-tabs .nav-link {
        color: #495057 !important;
        background-color: transparent !important;
    }
    .nav-tabs .nav-link.active {
        color: #007bff !important;
        background-color: #fff !important;
    }
    .nav-tabs .nav-link:hover {
        color: #007bff !important;
        background-color: transparent !important;
    }
    .nav-tabs {
        display: flex !important;
        flex-wrap: wrap !important;
        list-style: none !important;
        margin: 0 !important;
        padding: 0 1.25rem !important;
        padding-top: 1.25rem !important;
        background-color: #f8f9fa !important;
    }
    .nav-tabs .nav-item {
        display: block !important;
    }
    .nav-tabs .nav-link {
        display: block !important;
        padding: 0.75rem 1.25rem !important;
        text-decoration: none !important;
    }
    .tab-content {
        display: block !important;
    }
    .tab-pane {
        display: none !important;
    }
    .tab-pane.show {
        display: block !important;
    }
    .tab-pane.active {
        display: block !important;
    }
    
    /* Status dropdown styles */
    .dropdown-item.status-option {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 1rem;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        text-decoration: none;
        color: #495057;
    }
    
    .dropdown-item.status-option:hover {
        background-color: #f8f9fa;
        color: #495057;
        text-decoration: none;
    }
    
    .dropdown-item.status-option.active {
        background-color: #e3f2fd;
        color: #1976d2;
        font-weight: 500;
    }
    
    .dropdown-item.status-option.active:hover {
        background-color: #e3f2fd;
        color: #1976d2;
    }
    
    .dropdown-item.status-option .badge {
        font-size: 0.75rem;
    }
    
    .dropdown-item.status-option i.fa-check {
        margin-left: auto;
    }
    
    /* Alert styles */
    .alert {
        margin-bottom: 1rem;
        border-radius: 0.375rem;
    }
    
    .alert-dismissible .close {
        padding: 0.75rem 1.25rem;
    }
    
    /* Loading spinner for button */
    .btn .fa-spin {
        margin-right: 0.25rem;
    }
    
    /* Dropdown menu improvements */
    .dropdown-menu {
        border: 1px solid #dee2e6;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-radius: 0.375rem;
        min-width: 200px;
    }
    
    /* Status badge improvements */
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    .badge-lg {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
    
    /* Status section card */
    .card.border-primary {
        border-width: 2px;
    }
    
    .card.border-primary .card-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-bottom: none;
    }
    
    .card.border-primary .card-header .card-title {
        color: white;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .card.border-primary .card-body {
        background-color: #f8f9fa;
        padding: 1.5rem;
    }
    
    /* Status display section */
    .status-display {
        background-color: white;
        border-radius: 0.375rem;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Responsive adjustments for status section */
    @media (max-width: 768px) {
        .card.border-primary .card-body .d-flex {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .card.border-primary .card-body .d-flex > div:last-child {
            margin-top: 1rem;
            width: 100%;
        }
        
        .card.border-primary .card-body .dropdown {
            width: 100%;
        }
        
        .card.border-primary .card-body .dropdown .btn {
            width: 100%;
        }
    }
    
    /* SweetAlert2 customizations */
    .swal2-popup {
        border-radius: 0.5rem;
        font-family: inherit;
    }
    
    .swal2-title {
        color: #495057;
        font-weight: 600;
    }
    
    .swal2-html-container {
        color: #6c757d;
    }
    
    .swal2-confirm {
        background-color: #007bff !important;
        border-color: #007bff !important;
    }
    
    .swal2-confirm:hover {
        background-color: #0056b3 !important;
        border-color: #0056b3 !important;
    }
    
    .swal2-cancel {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
    }
    
    .swal2-cancel:hover {
        background-color: #545b62 !important;
        border-color: #545b62 !important;
    }
    
    .swal2-icon.swal2-error {
        border-color: #dc3545;
        color: #dc3545;
    }
    
    .swal2-icon.swal2-success {
        border-color: #28a745;
        color: #28a745;
    }
    
    .swal2-icon.swal2-question {
        border-color: #007bff;
        color: #007bff;
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
$(document).ready(function() {
    var loadedTabs = {};
    var orderId = {{ $order->ID }};
    
    // Handle tab switching with AJAX loading of partials
    $('#orderTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        var tabName = target.replace('#', '');
        
        // Load tab content via AJAX if not already loaded
        if (!loadedTabs[tabName] && tabName !== 'order-info') {
            loadTabContent(tabName);
        }
    });
    
    // Function to load tab content via AJAX using partials
    function loadTabContent(tabName) {
        var $tabPane = $('#' + tabName);
        
        $.ajax({
            url: '{{ route("orders.tab-content", $order->ID) }}',
            method: 'GET',
            data: { tab: tabName },
            beforeSend: function() {
                $tabPane.html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div><p class="mt-2 text-muted">Loading...</p></div>');
            },
            success: function(response) {
                if (response.html) {
                    $tabPane.html('<div class="mt-4">' + response.html + '</div>');
                    loadedTabs[tabName] = true;
                } else {
                    $tabPane.html('<div class="text-center py-5"><p class="text-danger">Error loading content</p></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading tab content:', error);
                $tabPane.html('<div class="text-center py-5"><p class="text-danger">Error loading content. Please try again.</p></div>');
            }
        });
    }
    
    // Ensure "Order Info & Items" tab is always active by default
    $('#orderTabs a[href="#order-info"]').tab('show');
    
    // Mark first tab as loaded
    loadedTabs['order-info'] = true;
    
    // Handle status change
    $('.status-option').on('click', function(e) {
        e.preventDefault();
        
        var $this = $(this);
        var newStatus = $this.data('status');
        var statusKey = $this.data('status-key');
        var statusLabel = $this.data('status-label');
        var orderId = {{ $order->ID }};
        
        // Don't update if it's the current status
        if ($this.hasClass('active')) {
            return;
        }
        
        // Show SweetAlert2 confirmation dialog
        Swal.fire({
            title: 'Change Order Status?',
            html: `Are you sure you want to change the order status to <strong>"${statusLabel}"</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                updateOrderStatus(newStatus, statusKey, statusLabel, $this);
            }
        });
    });
    
    // Function to update order status
    function updateOrderStatus(newStatus, statusKey, statusLabel, $this) {
        // Show loading state
        var $dropdown = $('#statusDropdown');
        var originalText = $dropdown.html();
        $dropdown.html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        $dropdown.prop('disabled', true);
        
        // Make AJAX request
        $.ajax({
            url: '{{ route("orders.update-status", $order->ID) }}',
            method: 'PATCH',
            data: {
                status: newStatus,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Update the status badge
                    var statusClass = 'secondary';
                    @foreach(config('woo-order-dashboard.status_colors', []) as $key => $color)
                        if ('{{ $key }}' === statusKey) {
                            statusClass = '{{ $color }}';
                        }
                    @endforeach
                    
                    $('#current-status-badge')
                        .removeClass()
                        .addClass('badge badge-' + statusClass + ' badge-lg')
                        .text(statusLabel);
                    
                    // Update dropdown items
                    $('.status-option').removeClass('active');
                    $this.addClass('active');
                    
                    // Show success message with SweetAlert2
                    Swal.fire({
                        title: 'Success!',
                        text: 'Order status updated successfully!',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Refresh the page after success
                        location.reload();
                    });
                } else {
                    showErrorAlert('Failed to update order status: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error updating order status:', error);
                var errorMessage = 'Failed to update order status.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showErrorAlert(errorMessage);
            },
            complete: function() {
                // Restore original button state
                $dropdown.html(originalText);
                $dropdown.prop('disabled', false);
            }
        });
    }
    
    // Function to show error alerts with SweetAlert2
    function showErrorAlert(message) {
        Swal.fire({
            title: 'Error!',
            text: message,
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    }
});
</script>
@endsection 