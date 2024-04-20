<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@php echo str_replace("_", " ", env('APP_NAME')); @endphp</title>

    <link rel="stylesheet" href="{{ asset('fonts/all.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/sb-admin-2.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-select.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/select.dataTables.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/fixedColumns.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-datepicker3.css')}}">
    <link rel="stylesheet" href="{{ asset('css/fixedHeader.dataTables.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/responsive.dataTables.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/buttons.dataTables.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/print.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/pace-theme-default.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/style-additional.css')}}">
    <link rel="stylesheet" href="{{asset('plugin/select2/dist/css/select2.min.css')}}">

    @yield('css')

    <!-- <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet"> -->

    <!-- <script src="{{ asset('../resources/js/jquery.min.js')}}"></script> -->
    <!-- <script src="{{ asset('../resources/js/jquery-3.5.1.js')}}"></script> -->
    <script src="{{ asset('js/jquery-3.3.1.min.js')}}"></script>
    <script src="{{ asset('js/popper.min.js')}}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{ asset('js/jquery.easing.min.js')}}"></script>
    <script src="{{ asset('js/jquery.mask.min.js')}}"></script>
    <script src="{{ asset('js/jquery-ui.js')}}"></script>
    <!-- <script src="{{ asset('../resources/js/datepicker.min.js')}}"></script> -->
    <script src="{{ asset('js/sb-admin-2.min.js')}}"></script>
    <script src="{{ asset('js/jquery.dataTables.min.js')}}"></script>
    <script src="{{ asset('js/dataTables.bootstrap4.min.js')}}"></script>
    <script src="{{ asset('js/bootstrap-select.min.js')}}"></script>
    <script src="{{ asset('js/bootstrap-datepicker.min.js')}}"></script>
    <script src="{{ asset('js/dataTables.select.min.js')}}"></script>
    <script src="{{ asset('js/datatables-demo.js')}}"></script>
    <script src="{{ asset('js/dataTables.fixedColumns.min.js')}}"></script>
    <script src="{{ asset('js/dataTables.fixedHeader.min.js')}}"></script>
    <script src="{{ asset('js/dataTables.fixedHeader.min.js')}}"></script>
    {{-- <script src="{{ asset('js/Chart.min.js')}}"></script>
    <script src="{{ asset('js/chart-area-demo.js')}}"></script>
    <script src="{{ asset('js/chart-pie-demo.js')}}"></script> --}}
    <script src="{{ asset('js/sweatalert.js')}}"></script>
    <script src="{{ asset('js/dataTables.buttons.min.js')}}"></script>
    <script src="{{ asset('js/jszip.min.js')}}"></script>
    <script src="{{ asset('js/buttons.flash.min.js')}}"></script>
    <script src="{{ asset('js/buttons.html5.min.js')}}"></script>
    <script src="{{ asset('js/pace.min.js')}}"></script>
    <script src="{{ asset('js/moment.min.js')}}"></script>
    <script src="{{ asset('js/print.min.js')}}"></script>
    <script src="{{ asset('js/jquery.number.min.js')}}"></script>
    <script src="{{ asset('plugin/select2/dist/js/select2.min.js')}}"></script>
    {{-- <script src="{{ asset('../resources/js/jquery.number.min.js.map')}}"></script> --}}
    <script>
        setInterval(refreshToken, 3900000); // 65min

        function refreshToken() {
            $.get('get-csrf').done(function(data) {
                if (data != document.querySelector('meta[name="csrf-token"]').content) {
                    console.log("token expired");
                    window.location.href = "/promosi/public/login";
                } else {
                    console.log("token valid");
                }
            });
        }


    </script>
    <!-- <script>
        $(document).ready(function() {
            $("#sidebarToggle").click(function() {
                $("#accordionSidebar").toggleClass("toggled");
            });
        });
    </script> -->
    <style>
        body{
            padding: 0!important;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        @include('layouts.sidebar')

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                @include('layouts.header')

                @yield('content')

                @yield('modal')

                <!-- Modal Load-->
                <div class="modal fade" role="dialog" id="modal_loading" data-keyboard="false" data-backdrop="static" style="z-index: 2000">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body pt-0" style="background-color: #F5F7F9; border-radius: 6px;">
                            <div class="text-center">
                                <img style="border-radius: 4px; height: 140px;" src="{{ asset('img/loader_1.gif') }}" alt="Loading">
                                <h6 style="position: absolute; bottom: 10%; left: 37%;" class="pb-2">Mohon Tunggu..</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var currentURL;
        $(document).ready(function(){
            currentURL = window.location.href;
            if (currentURL.charAt(currentURL.length - 1) === '/') {
                currentURL = currentURL.slice(0, -1);
            }
        });

        $.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

        $('.select2').select2();

        function setDateNow(element){
            var today = moment().format('YYYY-MM-DD');
            $(element).val(today).trigger('change');
        }

        function fungsiRupiah(angka){
            var number_string = angka.toString().replace(/[^,\d]/g, '').toString(),
            split   		= number_string.split('.'),
            sisa     		= split[0].length % 3,
            rupiah     		= split[0].substr(0, sisa),
            ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);

            if(ribuan){
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
            }
            rupiah = split[1] != undefined ? rupiah + '.' + split[1] : rupiah + ',00';
            return rupiah;
        }

        function getUrlParameter(sParam) {
            var sPageURL = window.location.search.substring(1),
                sURLVariables = sPageURL.split('&'),
                sParameterName,
                i;

            for (i = 0; i < sURLVariables.length; i++) {
                sParameterName = sURLVariables[i].split('=');

                if (sParameterName[0] === sParam) {
                    return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
                }
            }
            return false;
        };
    </script>
    @stack('page-script')
</body>

</html>
