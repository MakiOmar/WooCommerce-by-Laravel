@if(!empty($order['meta_data']))
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Additional Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($order['meta_data'] as $meta)
                    @if($meta['display_key'] && $meta['display_value'])
                        <div class="col-md-6 mb-3">
                            <strong>{{ $meta['display_key'] }}:</strong>
                            <span>{{ $meta['display_value'] }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endif