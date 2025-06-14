@extends('woo-order-dashboard::layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6">WooCommerce Order Dashboard</h1>

        <!-- Filters Section -->
        <div class="mb-8">
            <form action="{{ route('woo.orders') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Order ID Filter -->
                <div>
                    <label for="order_id" class="block text-sm font-medium text-gray-700">Order ID</label>
                    <input type="number" name="order_id" id="order_id" value="{{ request('order_id') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Date Range Filter -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Meta Key Filter -->
                <div>
                    <label for="meta_key" class="block text-sm font-medium text-gray-700">Meta Key</label>
                    <select name="meta_key" id="meta_key"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Meta Key</option>
                        <option value="billing_phone" {{ request('meta_key') == 'billing_phone' ? 'selected' : '' }}>Billing Phone</option>
                        <option value="_wcpdf_invoice_number" {{ request('meta_key') == '_wcpdf_invoice_number' ? 'selected' : '' }}>Invoice Number</option>
                        <option value="odoo_order_number" {{ request('meta_key') == 'odoo_order_number' ? 'selected' : '' }}>Odoo Order Number</option>
                    </select>
                </div>

                <!-- Meta Value Filter -->
                <div>
                    <label for="meta_value" class="block text-sm font-medium text-gray-700">Meta Value</label>
                    <input type="text" name="meta_value" id="meta_value" value="{{ request('meta_value') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Submit Button -->
                <div class="md:col-span-2 lg:col-span-4">
                    <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Apply Filters
                    </button>
                    <a href="{{ route('woo.orders') }}"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Reset Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders['data'] as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $order['id'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($order['date_created'])->format(config('woo-order-dashboard.date_format.display')) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $order['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($order['status'] == 'processing' ? 'bg-blue-100 text-blue-800' : 
                                   ($order['status'] == 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($order['status']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order['total'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order['billing']['first_name'] }} {{ $order['billing']['last_name'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('woo.orders.show', $order['id']) }}"
                                class="text-indigo-600 hover:text-indigo-900">View Details</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No orders found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(isset($orders['headers']['X-WP-TotalPages']))
        <div class="mt-4">
            {{ $orders['data']->links() }}
        </div>
        @endif
    </div>
</div>
@endsection 