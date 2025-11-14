<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>HJ | Hexa Skeleton</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('css/plugins/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('fonts/inter/inter.css') }}" id="main-font-link" />
    <link rel="stylesheet" href="{{ asset('fonts/tabler-icons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('fonts/feather.css') }}" />
    <link rel="stylesheet" href="{{ asset('fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('fonts/material.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" id="main-style-link" />
    <link rel="stylesheet" href="{{ asset('css/style-preset.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/dataTable.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('jstree/dist/themes/default/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/bootstrap-datepicker.standalone.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/buttons.dataTables.min.css') }}" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
@stack('scripts')
<body>
    @include('partials._loader')

    @include('components.sidebar')

    @include('partials._topbar')

    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
            @yield('content')
        </div>
    </div>
    <!-- [ Main Content ] end -->

    @include('partials._footer')

    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/fonts/custom-font.js') }}"></script>
    <script src="{{ asset('js/plugins/feather.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert.js') }}"></script>
    <script src="{{ asset('js/select2.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/pcoded.js') }}"></script>
    <script src="{{ asset('js/config.js') }}"></script>
    <script src="{{ asset('jstree/dist/jstree.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/datatable.button.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ResizeObserver to ensure header column widths are adjusted when the containing div resizes.
            // Not supported on IE
            var observer = window.ResizeObserver ? new ResizeObserver(function(entries) {
                entries.forEach(function(entry) {
                    $(entry.target).DataTable().columns.adjust();
                });
            }) : null;

            // Function to add a datatable to the ResizeObserver entries array
            resizeHandler = function($tables) {
                if (observer) {
                    $tables.each(function() {
                        observer.observe(this);
                    });
                }
            };

            // Initialize datepicker with clear button
            $('.column-search.datepicker').datepicker({
                format: "dd MM yyyy",
                autoclose: true,
                clearBtn: true,
                todayHighlight: true
            });
        });
    </script>
    @yield('scripts')
</body>

</html>