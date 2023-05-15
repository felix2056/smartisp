<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8"/>
    <title>Portal @lang('app.client') - @lang('app.access')</title>

    <meta name="description" content="User login page"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.ico') }}">
    <!-- bootstrap & fontawesome -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/font-awesome/css/font-awesome.min.css') }}"/>

    <!-- text fonts -->
    <link rel="stylesheet" href="{{ asset('assets/css/ace-fonts.css') }}"/>

    <!-- ace styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/ace.min.css') }}"/>

    <!--[if lte IE 9]>
    <link rel="stylesheet" href="{{ asset('assets/css/ace-part2.min.css')}}"/>
    <![endif]-->
    <link rel="stylesheet" href="{{ asset('assets/css/ace-rtl.min.css') }}"/>

    <style type="text/css" media="screen">
        .login-layout {
            background-color: #152733;
        }

        #id-company-text {
            color: #fff !important;
            font-size: 25px;
            font-weight: bold;
            padding-top: 21px;
            padding-bottom: 15px;
        }

        .login-layout .widget-box .widget-main {
            padding: 16px 36px 36px;
            background: #fff;
        }

        .login-layout .widget-box {
            visibility: hidden;
            position: fixed;
            z-index: -5;
            border-bottom: none;
            box-shadow: none;
            padding: 6px;
            background-color: #fff;
            -moz-transform: scale(0, 1) translate(-150px);
            -webkit-transform: scale(0, 1) translate(-150px);
            -o-transform: scale(0, 1) translate(-150px);
            -ms-transform: scale(0, 1) translate(-150px);
            transform: scale(0, 1) translate(-150px);
            border-radius: 0.35rem;
            -webkit-box-shadow: 0 1px 15px 1px rgba(62, 57, 107, 0.07);
            box-shadow: 0 1px 15px 1px rgba(62, 57, 107, 0.07);
        }

        .loginancho {
            max-width: 120px;
        }

        .header.blue {
            border-bottom-color: #fff;
        }

        .texto_login {
            color: #000;
            font-weight: 500;
        }

        .texto_login {
            color: #393939 !important;
        }

        .btn_logi, .btn_logi:hover {
            display: block;
            width: 100% !important;
            margin-top: 10px;
            border-radius: 5px;
            background-color: #23262f !important;
            border-color: #23262f !important;
            font-size: 17px;
        }

        input[type=email]:focus, input[type=url]:focus, input[type=search]:focus, input[type=tel]:focus, input[type=color]:focus, input[type=text]:focus, input[type=password]:focus, input[type=datetime]:focus, input[type=datetime-local]:focus, input[type=date]:focus, input[type=month]:focus, input[type=time]:focus, input[type=week]:focus, input[type=number]:focus, textarea:focus {
            -webkit-box-shadow: none;
            box-shadow: none;
            color: #696969;
            border-color: #1e9ff2;
            background-color: #fff;
            outline: 0;
        }

        #content_cuadro {
            background: #FF9800;
            margin-bottom: -16px;
            padding-top: 3px;
            margin-top: 36px;
            padding-bottom: 7px;
            border-top-right-radius: 10px;
            border-top-left-radius: 10px;
        }
    </style>
</head>

<body class="login-layout">
<div class="main-container">
    <div class="main-content">
        <div class="row">
            <div class="col-sm-10 col-sm-offset-1">
                <div class="login-container">
                    <div class="center">
                        <br><br>
                        <div id="content_cuadro">
                            <h2 class="text-center"><strong style="color: #fff">@lang('app.cashdeskPortal')</strong></h2>

                            <h4 class="blue" id="id-company-text">{{$company}}</h4>

                            @if(Session::has('login_errors'))
                                <div class="alert alert-danger" role="alert"><i
                                            class="fa fa-exclamation-triangle"></i> @lang('app.theUsernameAndPasswordYouEnteredDoNotMatch')
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="space-6"></div>

                    <div class="position-relative">
                        <div id="login-box" class="login-box visible widget-box no-border">
                            <div class="widget-body">
                                <div class="widget-main">
                                    <h4 class="header blue lighter bigger texto_login">
                                        @lang('app.enterYourInformation')
                                    </h4>

                                    <div class="space-6"></div>

                                    <form method="post" action="{{ route('cashdesk.login') }}">
                                        {{ csrf_field()}}
                                        <fieldset>
                                            <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="text" name="user" class="form-control"
                                                                   placeholder="Email o DNI" required autofocus/>
															<i class="ace-icon fa fa-user"></i>
														</span>
                                            </label>

                                            <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="password" name="password" class="form-control"
                                                                   required placeholder="@lang('app.password')"/>
															<i class="ace-icon fa fa-lock"></i>
														</span>
                                            </label>

                                            <div class="space"></div>

                                            <div class="clearfix">
                                                <label class="inline">
                                                    <input type="checkbox" value="1" name="remember" class="ace"/>
                                                    <span class="lbl"> @lang('app.rememberMe')</span>
                                                </label>

                                                <button type="submit" class="width-35 btn btn-sm btn-primary btn_logi">
                                                    <span class="bigger-110">@lang('app.ingresar')</span>
                                                </button>
                                            </div>

                                            <div class="space-4"></div>
                                        </fieldset>
                                    </form>
                                </div><!-- /.widget-main -->
                            </div><!-- /.widget-body -->
                        </div><!-- /.login-box -->
                    </div><!-- /.position-relative -->
                </div>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.main-content -->
</div><!-- /.main-container -->

<!-- basic scripts -->

<!--[if !IE]> -->
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>

<!-- <![endif]-->

<!--[if IE]>
<script src="{{ asset('assets/js/libs/jquery1.11/jquery.min.js')}}"></script>
<![endif]-->

<!--[if !IE]> -->
<script type="text/javascript">
    window.jQuery || document.write("<script src='{{ asset('assets/js/jquery.min.js') }}'>" + "<" + "/script>");
</script>

<!-- <![endif]-->

<!--[if IE]>
<script type="text/javascript">
    window.jQuery || document.write("<script src='{{ asset('assets/js/jquery1x.min.js') }}'>" + "<" + "/script>");
</script>
<![endif]-->
<script type="text/javascript">
    if ('ontouchstart' in document.documentElement) document.write("<script src='{{ asset('assets/js/jquery.mobile.custom.min.js') }}'>" + "<" + "/script>");
</script>

<!-- inline scripts related to this page -->
<script type="text/javascript">
    jQuery(function ($) {
        $(document).on('click', '.toolbar a[data-target]', function (e) {
            e.preventDefault();
            var target = $(this).data('target');
            $('.widget-box.visible').removeClass('visible');//hide others
            $(target).addClass('visible');//show target
        });
    });

    //you don't need this, just used for changing background
    jQuery(function ($) {
        $('#btn-login-dark').on('click', function (e) {
            $('body').attr('class', 'login-layout');
            $('#id-text2').attr('class', 'white');
            $('#id-company-text').attr('class', 'blue');

            e.preventDefault();
        });
        $('#btn-login-light').on('click', function (e) {
            $('body').attr('class', 'login-layout light-login');
            $('#id-text2').attr('class', 'grey');
            $('#id-company-text').attr('class', 'blue');

            e.preventDefault();
        });
        $('#btn-login-blur').on('click', function (e) {
            $('body').attr('class', 'login-layout blur-login');
            $('#id-text2').attr('class', 'white');
            $('#id-company-text').attr('class', 'light-blue');

            e.preventDefault();
        });

    });
</script>
</body>
</html>
