<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title> {{ $title }} | {{ env('APP_NAME') }} </title>
    <base href="{{ base_url() }}">
    <meta name="base_url" content="{{ base_url() }}" />

    <!-- Custom JS (Fahmy Izwan) -->
    <script src="{{ asset('public/custom/js/axios.min.js') }}"></script>
    <script src="{{ asset('custom/js/js-cookie.js') }}"></script>
    <script src="{{ asset('custom/js/helper.js') }}"></script>
    <script src="{{ asset('custom/js/validationJS.js') }}"></script>
    <script src="{{ asset('custom/js/block-ui.js') }}"></script>
    <script src="{{ asset('custom/js/extended-ui-blockui.js') }}"></script>

    <!-- CDN -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

    <!-- <style>
        .swal2-customCss {
            z-index: 20000;
        }
    </style> -->
</head>

<body>

    @includeif('_generals.templates._header')
    @includeif('_generals.templates._menu')


    <!-- ADD GENERAL FUNCTION -->
    @includeif('_generals.php.common')
    @includeif('_generals._modalGeneral')
    @includeif('_generals._modalLogout')

    <script>
        $(document).ready(function() {
            clock();
        });

        function clock() {
            let today = new Date().toLocaleDateString('en-GB', {
                month: '2-digit',
                day: '2-digit',
                year: 'numeric'
            });

            $("#currentTime").html(getClock('12', 'en', true) + ' | ' + today);
            setTimeout(clock, 1000);
        }
    </script>

</body>

</html>