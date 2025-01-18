<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title> {{ $title }} | {{ env('APP_NAME') }} </title>
    <base href="{{ base_url() }}">
    <meta name="base_url" content="{{ base_url() }}" />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('custom/css/toastr.min.css') }}" rel="stylesheet">

    <!-- Custom JS (Fahmy Izwan) -->
    <script src="{{ asset('custom/js/axios.min.js') }}"></script>
    <script src="{{ asset('custom/js/jquery.min.js') }}"></script>
    <script src="{{ asset('custom/js/js-cookie.js') }}"></script>
    <script src="{{ asset('custom/js/helper.js') }}"></script>
    <script src="{{ asset('custom/js/validationJS.js') }}"></script>
    <script src="{{ asset('custom/js/block-ui.js') }}"></script>
    <script src="{{ asset('custom/js/extended-ui-blockui.js') }}"></script>
    <script src="{{ asset('custom/js/toastr.min.js') }}"></script>

    <!-- CDN -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- <style>
        .swal2-customCss {
            z-index: 20000;
        }
    </style> -->
</head>

<body>

    <div class="container py-5">
        @includeif('_generals.templates._header')
        @includeif('_generals.templates._menu')

        @yield('content')
    </div>

    <!-- ADD GENERAL FUNCTION -->
    @includeif('_generals.php.common')
    @includeif('_generals._modalGeneral')
    @includeif('_generals._modalLogout')

    <script>
        $(document).ready(function() {
            clock();
        });

        function clock() {
            $("#currentTime").html(getClock('12', 'en', true) + ' | ' + date('d/m/Y'));
            setTimeout(clock, 1000);
        }
    </script>

</body>

</html>