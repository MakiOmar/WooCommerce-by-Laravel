@if($order->comments->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Order Notes</h5>
        </div>
        <div class="card-body">
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
        </div>
    </div>
@endif