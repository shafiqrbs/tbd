
<!-- WebFont.js -->
<script>
    WebFontConfig = {
        google: { families: ['Poppins:400,500,600,700,800'] }
    };
    (function (d) {
        var wf = d.createElement('script'), s = d.scripts[0];
        wf.src = '{{ asset('assets/frontend/js/webfont.js') }}';
        wf.async = true;
        s.parentNode.insertBefore(wf, s);
    })(document);
</script>

<link rel="preload" href="{{ asset('assets/frontend/vendor/fontawesome-free/webfonts/fa-regular-400.woff2') }}" as="font" type="font/woff2"
      crossorigin="anonymous">
<link rel="preload" href="{{ asset('assets/frontend/vendor/fontawesome-free/webfonts/fa-solid-900.woff2') }}" as="font" type="font/woff2"
      crossorigin="anonymous">
<link rel="preload" href="{{ asset('assets/frontend/vendor/fontawesome-free/webfonts/fa-brands-400.woff2') }}" as="font" type="font/woff2"
      crossorigin="anonymous">
<link rel="preload" href="{{ asset('assets/frontend/fonts/wolmart.woff?png09e') }}" as="font" type="font/woff" crossorigin="anonymous">

<!-- Vendor CSS -->
<link rel="stylesheet" type="text/css" href="{{ asset('assets/frontend/vendor/fontawesome-free/css/all.min.css') }}">

<!-- Plugins CSS -->
<!-- <link rel="stylesheet" href="assets/vendor/swiper/swiper-bundle.min.css"> -->
<link rel="stylesheet" type="text/css" href="{{ asset('assets/frontend/vendor/animate/animate.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/frontend/vendor/magnific-popup/magnific-popup.min.css') }}">
<!-- Link Swiper's CSS -->
<link rel="stylesheet" href="{{ asset('assets/frontend/vendor/swiper/swiper-bundle.min.css') }}">

<!-- Default CSS -->
<link rel="stylesheet" type="text/css" href="{{ asset('assets/frontend/css/demo1.min.css') }}">
