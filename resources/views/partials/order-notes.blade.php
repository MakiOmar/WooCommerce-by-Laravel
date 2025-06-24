@if($order->comments->count() > 0)
    <div class="timeline">
        @foreach($order->comments as $note)
            <div class="timeline-item">
                <div class="timeline-badge bg-primary">
                    <i class="fas fa-store"></i>
                </div>
                <div class="timeline-content">
                    <p class="mb-1">{!! nl2br(e($note->comment_content)) !!}</p>
                    <small class="text-muted">
                        {{ $note->comment_author }} - {{ \Carbon\Carbon::parse($note->comment_date)->format('M d, Y H:i') }}
                    </small>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-4">
        <i class="fas fa-sticky-note fa-2x text-muted mb-2"></i>
        <p class="text-muted">No order notes found.</p>
    </div>
@endif