@php
    $jsPath = base_path('vendor/makiomar/woo-order-dashboard/resources/assets/js/loading-utils.js');
    $jsContent = file_exists($jsPath) ? file_get_contents($jsPath) : '/* JS file not found */';
@endphp
<script>
{!! $jsContent !!}
</script> 