@php
    $cssPath = base_path('vendor/makiomar/woo-order-dashboard/resources/assets/css/woo-order-dashboard.css');
    $cssContent = file_exists($cssPath) ? file_get_contents($cssPath) : '/* CSS file not found */';
@endphp
<style>
{!! $cssContent !!}
</style> 