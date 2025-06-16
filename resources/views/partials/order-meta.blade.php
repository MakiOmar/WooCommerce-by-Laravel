@if(isset($order['meta_data']) && count($order['meta_data']))
    <div class="mb-8">
        <h2 class="text-lg font-semibold mb-4">Additional Information</h2>
        <div class="bg-gray-50 p-4 rounded">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($order['meta_data'] as $meta)
                    @if($meta['display_key'] && $meta['display_value'])
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ $meta['display_key'] }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $meta['display_value'] }}</dd>
                        </div>
                    @endif
                @endforeach
            </dl>
        </div>
    </div>
@endif