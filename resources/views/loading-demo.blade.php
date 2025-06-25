@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-spinner mr-2"></i>Loading Indicators Demo
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Input Loading Demo -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Input Loading Indicators</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Product Search (with loading)</label>
                                        <div class="search-input-container">
                                            <input type="text" class="form-control" id="demo-product-search" placeholder="Search for products..." autocomplete="off">
                                            <div class="loading-indicator">
                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Customer Search (with loading)</label>
                                        <div class="search-input-container">
                                            <input type="text" class="form-control" id="demo-customer-search" placeholder="Search for customers..." autocomplete="off">
                                            <div class="loading-indicator">
                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="button" class="btn btn-primary" id="demo-input-loading">
                                        <i class="fas fa-play mr-1"></i> Test Input Loading
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Button Loading Demo -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Button Loading States</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-primary" id="demo-btn-primary">
                                            <i class="fas fa-save mr-1"></i> Save Data
                                        </button>
                                    </div>
                                    
                                    <div class="form-group">
                                        <button type="button" class="btn btn-success" id="demo-btn-success">
                                            <i class="fas fa-check mr-1"></i> Submit Order
                                        </button>
                                    </div>
                                    
                                    <div class="form-group">
                                        <button type="button" class="btn btn-danger" id="demo-btn-danger">
                                            <i class="fas fa-trash mr-1"></i> Delete Items
                                        </button>
                                    </div>
                                    
                                    <button type="button" class="btn btn-secondary" id="demo-btn-reset">
                                        <i class="fas fa-undo mr-1"></i> Reset All
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Overlay Loading Demo -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Overlay Loading</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Full page overlay loading for major operations.</p>
                                    
                                    <div class="form-group">
                                        <button type="button" class="btn btn-warning" id="demo-overlay-short">
                                            <i class="fas fa-clock mr-1"></i> Show Overlay (3s)
                                        </button>
                                    </div>
                                    
                                    <div class="form-group">
                                        <button type="button" class="btn btn-info" id="demo-overlay-long">
                                            <i class="fas fa-hourglass-half mr-1"></i> Show Overlay (5s)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table Row Loading Demo -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Table Row Loading</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr id="demo-row-1">
                                                    <td>1</td>
                                                    <td>Sample Item 1</td>
                                                    <td><span class="badge badge-success">Active</span></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-primary demo-row-load" data-row="demo-row-1">
                                                            <i class="fas fa-spinner mr-1"></i> Load
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr id="demo-row-2">
                                                    <td>2</td>
                                                    <td>Sample Item 2</td>
                                                    <td><span class="badge badge-warning">Pending</span></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-primary demo-row-load" data-row="demo-row-2">
                                                            <i class="fas fa-spinner mr-1"></i> Load
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Usage Examples -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Usage Examples</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>JavaScript Usage:</h6>
                                            <pre><code>// Show input loading
loadingManager.showInputLoading('#product-search');

// Hide input loading
loadingManager.hideInputLoading('#product-search');

// Show button loading
loadingManager.showButtonLoading('#submit-btn', 'Saving...');

// Hide button loading
loadingManager.hideButtonLoading('#submit-btn');

// Show overlay
loadingManager.showOverlay('Processing...');

// Hide overlay
loadingManager.hideOverlay();

// Show row loading
loadingManager.showRowLoading('#row-1');

// Hide row loading
loadingManager.hideRowLoading('#row-1');</code></pre>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>HTML Structure:</h6>
                                            <pre><code>&lt;!-- Input with loading --&gt;
&lt;div class="search-input-container"&gt;
    &lt;input type="text" class="form-control"&gt;
    &lt;div class="loading-indicator"&gt;
        &lt;span class="spinner-border spinner-border-sm"&gt;&lt;/span&gt;
    &lt;/div&gt;
&lt;/div&gt;

&lt;!-- Button with loading --&gt;
&lt;button type="button" class="btn btn-primary" id="my-btn"&gt;
    &lt;i class="fas fa-save"&gt;&lt;/i&gt; Save
&lt;/button&gt;</code></pre>
                                        </div>
                                    </div>
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
<link href="{{ asset('vendor/woo-order-dashboard/css/woo-order-dashboard.css') }}" rel="stylesheet">
@endsection

@section('scripts')
@if(config('woo-order-dashboard.js_mode', 'inline'))
    @include('woo-order-dashboard::partials.woo-order-dashboard-inline-js')
@else
    <script src="{{ asset('vendor/woo-order-dashboard/js/loading-utils.js') }}"></script>
@endif
<script>
$(document).ready(function() {
    // Input loading demo
    $('#demo-input-loading').on('click', function() {
        loadingManager.showInputLoading('#demo-product-search');
        loadingManager.showInputLoading('#demo-customer-search');
        
        setTimeout(function() {
            loadingManager.hideInputLoading('#demo-product-search');
            loadingManager.hideInputLoading('#demo-customer-search');
        }, 3000);
    });

    // Button loading demos
    $('#demo-btn-primary').on('click', function() {
        loadingManager.showButtonLoading('#demo-btn-primary', 'Saving...');
        setTimeout(function() {
            loadingManager.hideButtonLoading('#demo-btn-primary');
        }, 2000);
    });

    $('#demo-btn-success').on('click', function() {
        loadingManager.showButtonLoading('#demo-btn-success', 'Submitting...');
        setTimeout(function() {
            loadingManager.hideButtonLoading('#demo-btn-success');
        }, 2500);
    });

    $('#demo-btn-danger').on('click', function() {
        loadingManager.showButtonLoading('#demo-btn-danger', 'Deleting...');
        setTimeout(function() {
            loadingManager.hideButtonLoading('#demo-btn-danger');
        }, 1800);
    });

    // Reset all buttons
    $('#demo-btn-reset').on('click', function() {
        loadingManager.hideAll();
    });

    // Overlay demos
    $('#demo-overlay-short').on('click', function() {
        loadingManager.showOverlay('Processing data...');
        setTimeout(function() {
            loadingManager.hideOverlay();
        }, 3000);
    });

    $('#demo-overlay-long').on('click', function() {
        loadingManager.showOverlay('Performing complex operation...');
        setTimeout(function() {
            loadingManager.hideOverlay();
        }, 5000);
    });

    // Row loading demos
    $('.demo-row-load').on('click', function() {
        var rowId = $(this).data('row');
        loadingManager.showRowLoading('#' + rowId);
        
        setTimeout(function() {
            loadingManager.hideRowLoading('#' + rowId);
        }, 2000);
    });
});
</script>
@endsection 