<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="author" content="">

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <!-- Viewport metatags -->
    <meta name="HandheldFriendly" content="true" />
    <meta name="MobileOptimized" content="320" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <!-- iOS webapp metatags -->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />

    <!-- iOS webapp icons --> 
    <link rel="apple-touch-icon-precomposed" href="{{ asset('panel/assets/images/ios/fickle-logo-72.png')}}" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{{ asset('panel/assets/images/ios/fickle-logo-72.png')}}" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{{ asset('panel/assets/images/ios/fickle-logo-114.png')}}" />

    <!-- TODO: Add a favicon -->
    <link rel="shortcut icon" href="{{ asset('panel/assets/images/ico/fab.ico')}}">

    <title>reset password</title>

    <!--Page loading plugin Start -->
    <link rel="stylesheet" href="{{ asset('panel/assets/css/plugins/pace.css')}}">
    <script src="{{ asset('panel/assets/js/pace.min.js')}}"></script>
    <!--Page loading plugin End   -->

    <!-- Plugin Css Put Here -->
    <link href="{{ asset('panel/assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('panel/assets/css/plugins/bootstrap-switch.min.css')}}">
    <link rel="stylesheet" href="{{ asset('panel/assets/css/plugins/ladda-themeless.min.css')}}">

    <link href="{{ asset('panel/assets/css/plugins/humane_themes/bigbox.css')}}" rel="stylesheet">
    <link href="{{ asset('panel/assets/css/plugins/humane_themes/libnotify.css')}}" rel="stylesheet">
    <link href="{{ asset('panel/assets/css/plugins/humane_themes/jackedup.css')}}" rel="stylesheet">

    <!-- Plugin Css End -->
    <!-- Custom styles Style -->
    <link href="{{ asset('panel/assets/css/style.css')}}" rel="stylesheet">
    <!-- Custom styles Style End-->

    <!-- Responsive Style For-->
    <link href="{{ asset('panel/assets/css/responsive.css')}}" rel="stylesheet">
    <!-- Responsive Style For-->

    <!-- Custom styles for this template -->


    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js')}}"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js')}}"></script>
    <![endif]-->
</head>
<body class="login-screen">
<section>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="login-box">
                    <div class="login-content">
                        <div class="login-user-icon">
                            <i class="glyphicon glyphicon-user"></i>

                        </div>
                        <h3>reset password</h3>
                        <div class="social-btn-login">
                            <ul>
                                {{-- <li><a href="javascript:void(0)"><i class="fa fa-facebook"></i></a></li>
                                <li><a href="javascript:void(0)"><i class="fa fa-twitter"></i></a></li>
                                <li><a href="javascript:void(0)"><i class="fa fa-linkedin"></i></a></li>
                                <li><a href="javascript:void(0)"><i class="fa fa-google-plus"></i></a></li>
                                <li><a href="javascript:void(0)"><i class="fa fa-github"></i></a></li>
                                <li><a href="javascript:void(0)"><i class="fa fa-bitbucket"></i></a></li> --}}
                            </ul>
                            <!--<button class="btn ls-dark-btn rounded"><i class="fa fa-facebook"></i></button>
                            <button class="btn ls-dark-btn rounded"><i class="fa fa-twitter"></i></button>
                            <button class="btn ls-dark-btn rounded"><i class="fa fa-linkedin"></i></button>
                            <button class="btn ls-dark-btn rounded"><i class="fa fa-google-plus"></i></button>
                            <button class="btn ls-dark-btn rounded"><i class="fa fa-github"></i></button>
                            <button class="btn ls-dark-btn rounded"><i class="fa fa-bitbucket"></i></button>-->
                        </div>
                    </div>

                    <div class="login-form">
                        <form id="form-login" action="{{ url('admin/paswordreset/'.$id.'/'.$token) }}" class="form-horizontal ls_form" method="POST">
                            {{ csrf_field() }}
                           
                            @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif  

                            <div class="input-group ls-group-input">

                                <input type="password" placeholder="Password" name="password"
                                       class="form-control" value="" minlength="6">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                            </div>
                            <div class="input-group ls-group-input">

                                <input type="password" placeholder="Password" name="password_confirmation"
                                       class="form-control" value="" minlength="6">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                            </div>
                            
                            <div class="input-group ls-group-input login-btn-box">
                                <button class="btn ls-dark-btn ladda-button col-md-12 col-sm-12 col-xs-12" data-style="slide-down">
                                    <span class="ladda-label"><i class="fa fa-key"></i></span>
                                </button>

                                
                            </div>
                        </form>
                    </div>
                   

                </div>
            </div>
        </div>
    </div>
    {{-- <p class="copy-right big-screen hidden-xs hidden-sm">
        <span>&#169;</span> Fickle <span class="footer-year">2014</span>
    </p> --}}
</section>

</body>
<script src="{{ asset('panel/assets/js/lib/jquery-2.1.1.min.js')}}"></script>
<script src="{{ asset('panel/assets/js/lib/jquery.easing.js')}}"></script>
<script src="{{ asset('panel/assets/js/bootstrap-switch.min.js')}}"></script>
<!--Script for notification start-->
<script src="{{ asset('panel/assets/js/loader/spin.js')}}"></script>
<script src="{{ asset('panel/assets/js/loader/ladda.js')}}"></script>
<script src="{{ asset('panel/assets/js/humane.min.js')}}"></script>
<!--Script for notification end-->

<script src="{{ asset('panel/assets/js/pages/login.js')}}"></script>
</html>

