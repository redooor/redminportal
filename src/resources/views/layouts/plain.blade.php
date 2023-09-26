<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title>RedminPortal by Redooor</title>
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/jquery-ui/themes/blitzer/jquery-ui.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/jasny-bootstrap.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/jasny-responsive.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/redmaterials.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/redminportal.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/datetimepicker/bootstrap-datetimepicker.min.css') }}">
        <!--[if lt IE 9]>
        <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        <link rel="shortcut icon" type="image/png" href="{{ URL::to('vendor/redooor/redminportal/img/favicon.png') }}"/>
        @section('head')
        @show
    </head>
    <body>
        <header id="header">
            <div class="navbar navbar-default navbar-fixed-top">
                <div class="container-fluid">
                    <div class="navbar-header">
                        @if (Auth::guard('redminguard')->check())
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                            <span class="glyphicon glyphicon-user"></span>
                        </button>
                        @endif
                        <a href="{{ URL::to('admin') }}" class="navbar-brand">
                            <img src="{{ URL::to('vendor/redooor/redminportal/img/favicon.png') }}" title="RedminPortal" class="redooor-nav-logo"> RedminPortal
                        </a>
                    </div>
                    <div class="navbar-collapse collapse">
                        @if (Auth::guard('redminguard')->check())
                        <ul class="nav navbar-nav navbar-right">
                            <li><a href="{{ URL::to('logout') }}" title="Lang::get('redminportal::menus.logout')"><span class="glyphicon glyphicon-log-out"></span> {{ Lang::get('redminportal::menus.logout') }}</a></li>
                        </ul>
                        @endif
                    </div><!--/.nav-collapse -->

                </div>
            </div>
        </header>

        <div id="main">
            <div class="container-fluid">
                <div class="col-sm-12 main">
                    @yield('content')
                </div>
            </div>
        </div><!--End main-->

        <script src="{{ URL::to('vendor/redooor/redminportal/js/jquery/jquery.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/moment/moment.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/datetimepicker/bootstrap-datetimepicker.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/redmaterials.min.js') }}"></script>

        @section('footer')
        @show
    </body>
</html>
