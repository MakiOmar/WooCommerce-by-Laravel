@if(isset($order['meta_data']) && count($order['meta_data']) > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-tags mr-2"></i>Additional Information
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($order['meta_data'] as $meta)
                    @if($meta['display_key'] && $meta['display_value'])
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 mr-3">
                                    <i class="fas fa-info-circle text-primary"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">{{ $meta['display_key'] }}</small>
                                    <strong>{{ $meta['display_value'] }}</strong>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endif