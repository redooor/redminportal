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
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/font-awesome.min.css') }}">
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
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                            <span class="glyphicon glyphicon-user"></span>
                        </button>
                        <a href="#" class="navbar-brand sidebar-toggle">
                            <span class="glyphicon glyphicon-option-vertical"></span>
                        </a>
                    </div>
                    <ul class="nav navbar-nav navbar-breadcrumb hidden-xs">
                        <li><a href="{{ URL::to('admin/dashboard') }}">{{ Lang::get('redminportal::menus.dashboard') }}</a></li>
                        @section('navbar-breadcrumb')
                        @show
                    </ul>
                    <ul class="nav navbar-nav navbar-right hidden-xs">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp; {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ url('myaccount') }}"><span class="glyphicon glyphicon-cog"></span> {{ Lang::get('redminportal::menus.my_account') }}</a></li>
                                <li><a href="{{ URL::to('logout') }}" title="Lang::get('redminportal::menus.logout')"><span class="glyphicon glyphicon-log-out"></span> {{ Lang::get('redminportal::menus.logout') }}</a></li>
                            </ul>
                        </li>
                    </ul>
                    <div class="navbar-collapse collapse">
                        <ul class="nav navbar-nav navbar-right visible-xs">
                            <li class="disabled"><a>Signed in as {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</a></li>
                            <li><a href="#"><span class="glyphicon glyphicon-cog"></span> {{ Lang::get('redminportal::menus.my_account') }}</a></li>
                            <li><a href="{{ URL::to('logout') }}" title="Lang::get('redminportal::menus.logout')"><span class="glyphicon glyphicon-log-out"></span> {{ Lang::get('redminportal::menus.logout') }}</a></li>
                        </ul>
                    </div><!--/.nav-collapse -->
                </div>
            </div>
        </header>

        <div id="main">
            <div class="container-fluid">
                <div id="sidebar-wrapper">
                    <div id="sidebar" class="shadow-depth-1">
                        <div id="sidebar-title">
                            <a href="{{ URL::to('admin') }}" class="redooor-nav-logo"><img src="{{ URL::to('vendor/redooor/redminportal/img/favicon.png') }}" title="RedminPortal"> RedminPortal</a>
                        </div>
                        {!! Redminportal::html()->printMenu(config('redminportal::menu'), 'nav nav-sidebar') !!}
                    </div>
                    <div id="sidebar-overlay" class="sidebar-toggle"></div>
                    <div class="main-content">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div><!--End main-->
        
        <!-- Modal confirmation window -->
        {!! Redminportal::html()->modalWindow(
            'confirm-modal',
            Lang::get('redminportal::messages.confirm_delete'),
            Lang::get('redminportal::messages.are_you_sure_you_want_to_delete'),
            '<button type="button" class="btn btn-default" data-dismiss="modal">' . Lang::get('redminportal::buttons.delete_no') . '</button><a href="#" id="confirm-modal-proceed" class="btn btn-danger">' . Lang::get('redminportal::buttons.delete_yes') . '</a>'
        ) !!}
        <!-- End of modal window -->
        
        <script src="{{ URL::to('vendor/redooor/redminportal/js/jquery/jquery.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/moment/moment.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/datetimepicker/bootstrap-datetimepicker.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/redmaterials.min.js') }}"></script>
        <script>
            !function ($) {
                $(function(){
                    $(document).on('click', '.btn-confirm', function(e) {
                        e.preventDefault();
                        $delete_url = $(this).attr('href');
                        $('#confirm-modal-proceed').attr('href', $delete_url);
                        $('#confirm-modal').modal('show');
                    });
                    $(document).on('click touchstart', '.sidebar-toggle', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if ($('#sidebar-wrapper').hasClass('active')) {
                            $('#sidebar-wrapper').removeClass('active');
                        } else {
                            $('#sidebar-wrapper').addClass('active');
                        }
                    });
                })
            }(window.jQuery);
        </script>

        @section('footer')
        @show
    </body>
</html>
