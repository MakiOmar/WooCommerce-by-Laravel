@extends('woo-order-dashboard::layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Order #{{ $order['number'] }}</h3>
                    <div>
                        <a href="{{ route('woo.orders') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                        @if($order['status'] !== 'completed')
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="fas fa-print"></i> Print
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Order Details</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Order ID:</strong> #{{ $order['number'] }}</p>
                                            <p><strong>Date Created:</strong> {{ \Carbon\Carbon::parse($order['date_created'])->format('Y-m-d H:i:s') }}</p>
                                            <p><strong>Status:</strong> 
                                                <span class="badge badge-{{ config('woo-order-dashboard.status_colors.' . $order['status'], 'secondary') }}">
                                                    {{ ucfirst($order['status']) }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Customer ID:</strong> {{ $order['customer_id'] ?? 'Guest' }}</p>
                                            <p><strong>Transaction ID:</strong> {{ $order['transaction_id'] ?? 'N/A' }}</p>
                                            <p><strong>Currency:</strong> {{ $order['currency'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Shipping Information</h4>
                                </div>
                                <div class="card-body">
                                    <p><strong>Shipping Method:</strong> {{ $order['shipping_method'] }}</p>
                                    <p><strong>Shipping Total:</strong> {{ $order['currency'] }} {{ number_format($order['shipping_total'], 2) }}</p>
                                    <p><strong>Shipping Tax:</strong> {{ $order['currency'] }} {{ number_format($order['shipping_tax'], 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Billing Address</h4>
                                </div>
                                <div class="card-body">
                                    <p>{{ $order['billing']['first_name'] }} {{ $order['billing']['last_name'] }}</p>
                                    <p>{{ $order['billing']['address_1'] }}</p>
                                    @if($order['billing']['address_2'])
                                        <p>{{ $order['billing']['address_2'] }}</p>
                                    @endif
                                    <p>{{ $order['billing']['city'] }}, {{ $order['billing']['state'] }} {{ $order['billing']['postcode'] }}</p>
                                    <p>{{ $order['billing']['country'] }}</p>
                                    <p>Email: {{ $order['billing']['email'] }}</p>
                                    <p>Phone: {{ $order['billing']['phone'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Shipping Address</h4>
                                </div>
                                <div class="card-body">
                                    <p>{{ $order['shipping']['first_name'] }} {{ $order['shipping']['last_name'] }}</p>
                                    <p>{{ $order['shipping']['address_1'] }}</p>
                                    @if($order['shipping']['address_2'])
                                        <p>{{ $order['shipping']['address_2'] }}</p>
                                    @endif
                                    <p>{{ $order['shipping']['city'] }}, {{ $order['shipping']['state'] }} {{ $order['shipping']['postcode'] }}</p>
                                    <p>{{ $order['shipping']['country'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Order Items</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>SKU</th>
                                                    <th>Quantity</th>
                                                    <th>Price</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($order['line_items'] as $item)
                                                    <tr>
                                                        <td>{{ $item['name'] }}</td>
                                                        <td>{{ $item['sku'] ?? 'N/A' }}</td>
                                                        <td>{{ $item['quantity'] }}</td>
                                                        <td>{{ $order['currency'] }} {{ number_format($item['price'], 2) }}</td>
                                                        <td>{{ $order['currency'] }} {{ number_format($item['total'], 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                                                    <td>{{ $order['currency'] }} {{ number_format($order['subtotal'], 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-right"><strong>Shipping:</strong></td>
                                                    <td>{{ $order['currency'] }} {{ number_format($order['shipping_total'], 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-right"><strong>Tax:</strong></td>
                                                    <td>{{ $order['currency'] }} {{ number_format($order['total_tax'], 2) }}</td>
                                                </tr>
                                                @if($order['discount_total'] > 0)
                                                    <tr>
                                                        <td colspan="4" class="text-right"><strong>Discount:</strong></td>
                                                        <td>-{{ $order['currency'] }} {{ number_format($order['discount_total'], 2) }}</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                                    <td><strong>{{ $order['currency'] }} {{ number_format($order['total'], 2) }}</strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!empty($order['order_notes']))
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Order Notes</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="timeline">
                                            @foreach($order['order_notes'] as $note)
                                                <div class="timeline-item">
                                                    <div class="timeline-header">
                                                        <strong>{{ $note['added_by'] }}</strong>
                                                        <small class="text-muted float-right">
                                                            {{ \Carbon\Carbon::parse($note['date_created'])->format('Y-m-d H:i:s') }}
                                                        </small>
                                                    </div>
                                                    <div class="timeline-body">
                                                        {{ $note['note'] }}
                                                    </div>
                                                    @if($note['is_customer_note'])
                                                        <div class="timeline-footer">
                                                            <span class="badge badge-info">Customer Note</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .btn, .card-header .btn {
            display: none !important;
        }
        .card {
            border: none !important;
        }
        .card-header {
            background: none !important;
            border-bottom: 1px solid #ddd !important;
        }
    }
    .timeline {
        position: relative;
        padding: 20px 0;
    }
    .timeline-item {
        position: relative;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    .timeline-header {
        margin-bottom: 10px;
    }
    .timeline-body {
        margin-bottom: 10px;
    }
    .timeline-footer {
        margin-top: 10px;
    }
</style>
@endpush
