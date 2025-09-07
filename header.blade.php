<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('pageTitle')</title>
    <meta name="description" content="Elisyam is a Web App and Admin Dashboard Template built with Bootstrap 4">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="https://shl.com.bd/public/assets/img/payplusbn.png" />

    <link rel="stylesheet" href="{{ asset('assets/css/roboto.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/jquery-ui/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/jquery-ui/jquery-ui.theme.min.css') }}">
    <link href="{{ asset('assets/vendors/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/vendors/simple-line-icons/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/flags-icon/css/flag-icon.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/vendors/datatable/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatable/buttons/css/buttons.bootstrap4.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/vendors/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap4-toggle/css/bootstrap4-toggle.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/customized.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/customizedBlWebview.css') }}">


    <link rel="stylesheet" href="{{ asset('assets/vendors/select2/css/select2.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/vendors/select2/css/select2-bootstrap.min.css') }}"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/vendors/select2/css/select2-bootstrap.min.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/vendors/sweetalert/sweetalert.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/toastr/toastr.min.css') }}">


    @stack('styles')
    {{-- <script src="{{ asset('assets/vendors/jquery-ui/jquery-ui.min.js') }}"></script> --}}
    <script src="{{ asset('assets/vendors/jquery/jquery-3.3.1.min.js') }}"></script>
</head>

<body id="main-container" class="default">
    <div class="container-fluid site-width">
        <div class="row">
            <div class="col-sm-12 col-12 bg-bl">
                <div class="bl-top-bar">
                    <input type="hidden" id="page" value="@yield('prev_page')" />
                    {{-- <a type="button" href="@yield('prev_page')" class="take-back float-left">
                        <img src="{{ asset('assets/img/bl-icons/arrow_back-1.svg') }}" width="auto" height="25px">
                    </a> --}}
                    <span class="font-size-16">@yield('pageTitle')</span>
                </div>
            </div>
        </div>
    </div>
    <div class="block-ui clear">
        <div class="loading-info">
            <div class="loading-text loading">
                <div class="text"> Please wait...</div>
                <div class="block-loader" role="progressbar">
                    <span class="rect rect1"></span>
                    <span class="rect rect2"></span>
                    <span class="rect rect3"></span>
                    <span class="rect rect4"></span>
                    <span class="rect rect5"></span>
                </div>
            </div>
        </div>
    </div>
    @yield('content')
    <div class="bl-bottom-gap"></div>
    <footer class="site-footer-bl">
        Powered by : <b>Service Hub Ltd.</b>
    </footer>
    @stack('scripts')

    <script src="{{ asset('assets/vendors/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendors/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}" type="text/javascript">
    </script>
    <script src="{{ asset('assets/vendors/slimscroll/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/vendors/bootstrap4-toggle/js/bootstrap4-toggle.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/sweetalert/sweetalert.min.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/select2.script.js') }}"></script> --}}
    <script src="{{ asset('assets/vendors/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('assets/js/toastr.script.js') }}"></script>
    <script type="text/javascript">
        $(function() {
            window.addEventListener("pageshow", function(event) {
                var page = $('#page').val();
                var historyTraversal = event.persisted ||
                    (typeof window.performance != "undefined" &&
                        window.performance.getEntriesByType("navigation")[0].type === "back_forward");
                if (historyTraversal && page != '#') {
                    // Handle page restore.
                    blockUI(1);
                    window.location.reload();
                }
            });

            $('.form-select-2').select2({
                // container:'body',
                // matcher: 'matchCustom',
            });
            $('button.reset-date').click(function() {
                var remove = $(this).attr('remove');
                $('#' + remove).val('');
            });

            var date = new Date();
            // date.setDate(date.getDate() - 1);



            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                startDate: date,
            });


            $('.month-date-picker').datepicker({
                format: "yyyy-mm",
                viewMode: "months",
                minViewMode: "months",
            });


            $('.current-date-picker').datepicker({
                format: 'yyyy-mm-dd',
                minDate: '0',
                startDate: date,
            });


            $('.datepicker2').datepicker({
                format: 'dd MM yyyy',
                autoclose: true,
                todayHighlight: true,
                startDate: date,
            });

        });

        function blockUI(stat) {
            var blockUi = $('.block-ui');
            if (stat == 1) {
                if (blockUi.hasClass('clear')) {
                    blockUi.removeClass('clear');
                }
            } else {
                if (!blockUi.hasClass('clear')) {
                    blockUi.addClass('clear');
                }
            }
        }
    </script>
</body>

</html>
