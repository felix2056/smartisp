<!DOCTYPE html>
<html lang="es">
<head>
    <title>SmartISP</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="author" content="smartisp 2018">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/font-awesome/css/font-awesome.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/login-responsive.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/pace-theme-minimal.css') }}"/>
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.ico') }}">
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/pace/pace.min.js') }}"></script>
    <style type="text/css" media="screen">
        body.full-content {
            background: #152733;
            background-image: unset;
            padding-top: 0px;
        }

        .box-info {
            position: relative;
            padding: 15px;
            background: #fff;
            color: #5b5b5b;
            margin-bottom: 20px;
            -webkit-transition: All 0.4s ease;
            -moz-transition: All 0.4s ease;
            -o-transition: All 0.4s ease;
            border: none;
            -webkit-box-shadow: 0 1px 15px 1px rgba(62, 57, 107, 0.07);
            box-shadow: 0 1px 15px 1px rgba(62, 57, 107, 0.07);
            position: relative;
            display: -webkit-box;
            display: -webkit-flex;
            display: -moz-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -webkit-flex-direction: column;
            -moz-box-orient: vertical;
            -moz-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #FFFFFF;
            -webkit-background-clip: border-box;
            background-clip: border-box;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 0.35rem;
        }

        .btn-success {
            color: #fff;
            background-color: #23262f;
            border-color: #23262f;
            font-size: 18px;
        }

        .btn-success:hover {
            color: #fff;
            background-color: #23262f;
            border-color: #23262f;
            font-size: 18px;
        }

        .loginancho {
            max-width: 120px;
        }
    </style>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="assets/js/html5shiv.js"></script>
    <script src="assets/js/respond.min.js"></script>
    <![endif]-->
    <!-- WP Content Protection script by Rynaldo Stoltz Starts - http://yooplugins.com/ -->
    <div align="center">
        <noscript>
            <div style="position:fixed; top:0px; left:0px; z-index:3000; height:100%; width:100%; background-color:#FFFFFF">
                <div style="font-family: Trebuchet MS; font-size: 14px; background-color:#FFF000; padding: 10pt;">¡Oops!
                    Parece que se ha desactivado el Javascript. Para que usted pueda ver esta página, le pedimos que por
                    favor, vuelva a habilitar Javascript en su navegador.
                </div>
            </div>
        </noscript>
    </div>
    {{-- <script type="text/javascript">
        function disableSelection(e){if(typeof e.onselectstart!="undefined")e.onselectstart=function(){return false};else if(typeof e.style.MozUserSelect!="undefined")e.style.MozUserSelect="none";else e.onmousedown=function(){return false};e.style.cursor="default"}window.onload=function(){disableSelection(document.body)}
    </script>
    <script type="text/javascript">
        document.oncontextmenu=function(e){var t=e||window.event;var n=t.target||t.srcElement;if(n.nodeName!="A")return false};
        document.ondragstart=function(){return false};
    </script> --}}
    <style type="text/css">
        * :

        (
        input, textarea

        )
        {
            -webkit-touch-callout: none
        ;
            -webkit-user-select: none
        ;
        }
    </style>
    <style type="text/css">
        img {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
        }
    </style>
{{-- <script type="text/javascript">
	window.addEventListener("keydown",function(e){if(e.ctrlKey&&(e.which==65||e.which==66||e.which==67||e.which==70||e.which==73||e.which==80||e.which==83||e.which==85||e.which==86)){e.preventDefault()}});document.keypress=function(e){if(e.ctrlKey&&(e.which==65||e.which==66||e.which==70||e.which==67||e.which==73||e.which==80||e.which==83||e.which==85||e.which==86)){}return false}
</script>
<script type="text/javascript">
	document.onkeydown=function(e){e=e||window.event;if(e.keyCode==123||e.keyCode==18){return false}}
</script> --}}
<!-- WP Content Protection script by Rynaldo Stoltz Ends - http://yooplugins.com/ -->
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.ico') }}">
</head>

<style type="text/css" media="screen">
    .left_content {
        width: 35%;
        display: inline-block;
    }

    .derecha_cont {
        width: 65%;
        display: inline-block;
        background: #fff;
        margin-left: -4px;
        height: 100vh;
        padding-top: 25px;
    }

    .login-wrap {
        margin: 0 4% 20px 4%;
        text-align: left;
        padding-bottom: 6px;
    }

    .full-content-center {
        width: 100%;
        max-width: 450px;
        margin: 5% auto;
        text-align: center;
    }

    .footer_izq {
        position: absolute;
        bottom: 0;
        padding: 15px;
    }

    .img_f {
        width: 50px;
        margin-right: 40px;
    }

    .content_log {
        margin-left: 60px;
    }

    .title_left {
        color: #fff;
        font-size: 28px;
        position: absolute;
        top: 0;
        padding: 30px 30px;
        text-align: center;
        margin-left: 10px;
    }

    @media handheld, only screen and (max-width: 1200px) {
        .title_left {
            color: #fff;
            font-size: 16px !important;

        }

        .content_log {
            margin-left: 10px;
        }

        .img_f {
            width: 50px;
            margin-right: 10px;
        }
    }

    @media handheld, only screen and (max-width: 992px) {
        .left_content {
            display: none;
        }

        .derecha_cont {
            width: 100%;
            background: #343434;
        }
    }

    .img_f2 {
        width: 163px;
        margin-left: 20px;
    }

    .img_f3 {
        width: 116px;
        margin-right: 74px;
        margin-top: 34px;
    }
</style>
<body class="tooltips full-content" style="width: 100%;">

<div class="left_content" id>

    <div class="title_left">
        @lang('app.ISPBillingAndManagement')
    </div>
    <div style="position: absolute;top: 25%;">
        <img style="max-width: 400px;margin-left: 65px;" src="{{ asset('assets/images/Billing.png') }}" alt="">
    </div>


    <div style="position: absolute;bottom:0;">
        {{-- <img class="" style="widows: 80%;max-width: 300px;margin-left: 30%;padding-bottom: 30px;" src="{{ asset('assets/images/Mikro_Logo.png') }}" alt=""> --}}
    </div>

    {{-- 	<div class="footer_izq">
            <div class="row content_log">
                <div class="col-md-12">
                    <img class="" style="widows: 80%;" src="{{ asset('assets/images/Mikro_Logo.png') }}" alt="">
                </div>
                <div class="col-md-4">
                    <img class="img_f2" src="{{ asset('assets/images/fiber.png') }}" alt="">
                </div>
                <div class="col-md-4">
                    <img class="img_f" src="{{ asset('assets/images/Cambium_Logo.png') }}" alt="">
                </div>
            </div>
        </div> --}}

</div>

<div class="derecha_cont">
    <div class="full-content-center animated fadeInDownBig"
         style="border-radius:15px !important;background: #152733;margin-top: 80px;">
        <h2 class="text-center" style="padding-top: 20px;"><strong
                    style="color: #fff;">@lang('app.systemAccess')</strong></h2><br>
        <div class="login-wrap">

            <div class="box-info">
                <center>
                    <a href="#"><img class="loginancho" src="{{ asset('assets/img/logo.png') }}" alt=""></a>

                </center>
                <br>

                <h2 class="text-center">@lang('app.logIn')</h2>
                @if(Session::has('login_errors'))
                    <div class="alert alert-danger" role="alert"><i
                                class="fa fa-exclamation-triangle"></i> @lang('app.rrrorAccessingInvalidData').
                    </div>
                @endif
                @if (Session::has('block_user'))
                    <div class="alert alert-danger" role="alert"><i
                                class="fa fa-exclamation-triangle"></i> @lang('app.theUserIsLocked').
                    </div>
                @endif

                @if (Session::has('licencia_expirada'))
                    <div class="alert alert-danger" role="alert"><i
                                class="fa fa-exclamation-triangle"></i> @lang('app.licenseExpiredPleaseMakeDuePayment').
                    </div>
                @endif
                <form role="form" action="{{ route('auth') }}" method="post" novalidate>
                    {{ csrf_field()}}
                    <div class="form-group login-input">
                        <i class="fa fa-sign-in overlay"></i>
                        <input type="text" class="form-control text-input" required placeholder="@lang('app.username')"
                               name="user" autofocus autocomplete="off">
                    </div>
                    <div class="form-group login-input">
                        <i class="fa fa-key overlay"></i>
                        <input type="password" class="form-control text-input" required
                               placeholder="@lang('app.password')" name="password" autocomplete="off">
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" value="1" name="remember"> @lang('app.doNotSignOut')
                        </label>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-success btn-block"> @lang('app.ingresar') </button>
                        </div>
                    </div>
                </form>
            </div>
            {{-- <p class="text-center"> <br />
                <p style="color:#FFF">&copy; 2018 SmartISP Todos los derechos reservados</p> --}}
        </div>
    </div>
</div>
</body>
</html>
