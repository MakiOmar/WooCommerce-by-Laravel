<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Customer Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Name:</strong> {{ $order['billing']['first_name'] }} {{ $order['billing']['last_name'] }}</p>
                <p><strong>Email:</strong> {{ $order['billing']['email'] }}</p>
                <p><strong>Phone:</strong> {{ $order['billing']['phone'] }}</p>
                @if($order['customer_id'])
                    <p><strong>Customer ID:</strong> {{ $order['customer_id'] }}</p>
                @endif
            </div>
        </div>
    </div>
</div> 