@if (
    \Backpack\Settings\app\Models\Setting::get('google_analytics_enable') == 1 &&
        \Backpack\Settings\app\Models\Setting::get('google_analytics_tracking_id'))
    @php
        $GA_TRACKING_ID = \Backpack\Settings\app\Models\Setting::get('google_analytics_tracking_id');
    @endphp
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{$GA_TRACKING_ID}}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', '{{$GA_TRACKING_ID}}');
    </script>
@endif
