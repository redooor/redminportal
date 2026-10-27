<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title>RedminPortal by Redooor</title>
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/jquery-ui/themes/blitzer/jquery-ui.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/fontawesome.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/redmaterials.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/redminportal.min.css') }}">
        <link rel="shortcut icon" type="image/png" href="{{ URL::to('vendor/redooor/redminportal/img/favicon.png') }}"/>
        @section('head')
        @show
    </head>
    <body>
        <header id="header">
            <nav class="navbar navbar-expand-md navbar-light bg-body fixed-top">
                <div class="container-fluid">
                    <a href="#" class="navbar-brand sidebar-toggle" aria-label="Toggle sidebar">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </a>
                    <ul class="navbar-nav navbar-breadcrumb d-none d-md-flex me-auto">
                        <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/dashboard') }}">{{ Lang::get('redminportal::menus.dashboard') }}</a></li>
                        @section('navbar-breadcrumb')
                        @show
                    </ul>
                    <button type="button" class="navbar-toggler ms-auto" data-bs-toggle="collapse" data-bs-target="#navbar-user" aria-controls="navbar-user" aria-expanded="false" aria-label="Toggle user menu">
                        <i class="fa-solid fa-user"></i>
                    </button>
                    <div class="collapse navbar-collapse flex-grow-0" id="navbar-user">
                        <ul class="navbar-nav d-md-none">
                            <li class="nav-item"><span class="nav-link disabled">Signed in as {{ Auth::guard('redminguard')->user()->first_name }} {{ Auth::guard('redminguard')->user()->last_name }}</span></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('myaccount') }}"><i class="fa-solid fa-gear"></i> {{ Lang::get('redminportal::menus.my_account') }}</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ URL::to('logout') }}" title="{{ Lang::get('redminportal::menus.logout') }}"><i class="fa-solid fa-right-from-bracket"></i> {{ Lang::get('redminportal::menus.logout') }}</a></li>
                        </ul>
                        <ul class="navbar-nav d-none d-md-flex">
                            <li class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false"><i class="fa-solid fa-user"></i>&nbsp;&nbsp; {{ Auth::guard('redminguard')->user()->first_name }} {{ Auth::guard('redminguard')->user()->last_name }}</a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ url('myaccount') }}"><i class="fa-solid fa-gear"></i> {{ Lang::get('redminportal::menus.my_account') }}</a></li>
                                    <li><a class="dropdown-item" href="{{ URL::to('logout') }}" title="{{ Lang::get('redminportal::menus.logout') }}"><i class="fa-solid fa-right-from-bracket"></i> {{ Lang::get('redminportal::menus.logout') }}</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
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
            '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . Lang::get('redminportal::buttons.delete_no') . '</button><a href="#" id="confirm-modal-proceed" class="btn btn-danger">' . Lang::get('redminportal::buttons.delete_yes') . '</a>'
        ) !!}
        <!-- End of modal window -->
        
        <script src="{{ URL::to('vendor/redooor/redminportal/js/jquery/jquery.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/redmaterials.min.js') }}"></script>
        <script>
            !function ($) {
                $(function(){
                    $(document).on('click', '.btn-confirm', function(e) {
                        e.preventDefault();
                        $delete_url = $(this).attr('href');
                        $('#confirm-modal-proceed').attr('href', $delete_url);
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('confirm-modal')).show();
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
