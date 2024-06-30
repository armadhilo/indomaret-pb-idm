<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@php echo str_replace("_", " ", env('APP_NAME')); @endphp</title>

    <link rel="stylesheet" href="fonts/all.min.css">
    <link rel="stylesheet" href="css/sb-admin-2.min.css">
    <link rel="stylesheet" href="css/bootstrap-select.min.css">
    <link rel="stylesheet" href="css/pace-theme-default.min.css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    {{-- <script src="../resources/js/login.js"></script> --}}
    <script src="{{ url('js/login.js?time=') . rand() }}"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/sweatalert.js"></script>
    <script src="js/pace.min.js"></script>
</head>

<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center" style="margin-top:15%">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block"
                                style="background:url('img/bg.jpg'); background-position:center; background-norepeat:no-repeat;">
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome <br><strong>@php
                                            echo env('APP_NAME');
                                        @endphp</strong></h1>
                                    </div>
                                    <div class="card-alert">
                                    </div>

                                    <form class="form-login user" method="post">
                                        {{ csrf_field() }}
                                        <div class="form-group">
                                            <label for="select-branch">Select Branch</label>
                                            <select class="selectpicker form-control" id="select-branch"
                                                data-live-search="true" title="Select Branch">
                                                <option value="22">22 - IGRSMG</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="select-server">Select Server</label>
                                            <select class="selectpicker form-control" id="select-server"
                                                data-live-search="true">
                                                <option value="PRODUCTION">PRODUCTION</option>
                                                <option value="SIMULASI">SIMULASI</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="select-server">Mode Program</label>
                                            <select class="selectpicker form-control" id="select-pil-mode" data-live-search="true">
                                                @foreach ($pilMode as $item)
                                                    <option value="{{ $item }}">{{ $item }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user" name="username"
                                                id="input-username"  placeholder="Username">
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                name="password" id="input-password" placeholder="Password">
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="customCheck">
                                            </div>
                                        </div>
                                        <input type="text" id="flagFTZ" value="{{ $flagFTZ }}" hidden>
                                        <input type="text" id="flagIGR" value="{{ $flagIGR }}" hidden>
                                        <input type="text" id="flagSPI" value="{{ $flagSPI }}" hidden>
                                        <input type="text" id="flagHHSPI" value="{{ $flagHHSPI }}" hidden>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">Login</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

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
</body>
