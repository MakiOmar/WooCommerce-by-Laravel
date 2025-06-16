@if(!empty($order['order_notes']))
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Order Notes</h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                @foreach($order['order_notes'] as $note)
                    <div class="timeline-item">
                        <div class="timeline-badge {{ $note['is_customer_note'] ? 'bg-info' : 'bg-primary' }}">
                            <i class="fas {{ $note['is_customer_note'] ? 'fa-user' : 'fa-store' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <p class="mb-1">{{ $note['note'] }}</p>
                            <small class="text-muted">
                                {{ $note['author'] }} - {{ \Carbon\Carbon::parse($note['date_created'])->format('M d, Y H:i') }}
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif