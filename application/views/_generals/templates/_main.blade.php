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
    <script src="{{ asset('custom/js/notification.js') }}"></script>

    <!-- CDN -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- <style>
        .swal2-customCss {
            z-index: 20000;
        }
    </style> -->

    <style>
       body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        .file-container {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .file-info {
            display: flex;
            align-items: center;
        }
        .file-icon {
            width: 30px;
            height: 30px;
            margin-right: 15px;
        }
        .file-name {
            font-weight: bold;
            font-size: 16px;
        }
        .progress-bar {
            flex-grow: 1;
            height: 12px;
            background-color: #e0e0e0;
            border-radius: 6px;
            overflow: hidden;
            margin-right: 10px;
            position: relative;
        }
        .progress-bar-inner {
            height: 100%;
            background-color: #007bff;
            transition: width 0.4s ease;
        }
        .progress-text {
            font-size: 12px;
            color: #555;
        }
        .delete-icon {
            font-size: 18px;
            color: #ff4d4d;
            cursor: pointer;
            transition: color 0.3s;
        }
        .delete-icon:hover {
            color: #d11a2a;
        }
    </style>
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

            showNotiPanel(dataPanel);

            showClock('currentTime', {
                timeFormat: '12',
                lang: 'en', // or my for malay
                showSeconds: true,
                showDate: true
            });
        });

        function dataPanel() {
            return Array.from({
                length: 10
            }, (_, i) => {
                const fileName = `File_${i + 1}.${Math.random() > 0.5 ? 'csv' : 'xls'}`;
                const percentage = Math.floor(Math.random() * 101);

                const progressBar = `
                    <div class="progress-bar" aria-label="Progress for ${fileName}">
                        <div class="progress-bar-inner" style="width: ${percentage}%;"></div>
                    </div>`;

                const deleteIcon = percentage === 100 ?
                    `<span class="delete-icon" title="Delete">&#10060;</span>` :
                    '';

                return `
                    <div class="file-container">
                        <div class="file-info">
                            <img src="https://via.placeholder.com/30/007bff/ffffff?text=F" class="file-icon" alt="File Icon">
                            <span class="file-name">${fileName}</span>
                        </div>
                        <div class="file-progress">
                            ${percentage === 100 ? '' : progressBar}
                            <span class="progress-text">${percentage}%</span>
                        </div>
                        ${deleteIcon}
                    </div>`;
            }).join('');
        }
    </script>

</body>

</html>