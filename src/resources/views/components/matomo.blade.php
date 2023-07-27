@if (
    \Backpack\Settings\app\Models\Setting::get('matomo_enable') == 1 &&
        \Backpack\Settings\app\Models\Setting::get('matomo_url') &&
        \Backpack\Settings\app\Models\Setting::get('matomo_site_id'))
    @php
        $MATOMO_URL = \Backpack\Settings\app\Models\Setting::get('matomo_url');
        $MATOMO_SITE_ID = \Backpack\Settings\app\Models\Setting::get('matomo_site_id');
    @endphp

    <!-- Matomo -->
    <script type="text/javascript">
        var _paq = window._paq = window._paq || [];
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u = "//{{ $MATOMO_URL }}/";
            _paq.push(['setTrackerUrl', u + 'matomo.php']);
            _paq.push(['setSiteId',
                '{{ $MATOMO_SITE_ID }}'
            ]);
            var d = document,
                g = d.createElement('script'),
                s = d.getElementsByTagName('script')[0];
            g.type = 'text/javascript';
            g.async = true;
            g.src = u + 'matomo.js';
            s.parentNode.insertBefore(g, s);
        })();
    </script>
    <!-- End Matomo Code -->
@endif
