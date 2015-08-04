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
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a href="#" class="navbar-brand sidebar-toggle hidden-xs">
                            <span class="glyphicon glyphicon-menu-hamburger"></span>
                        </a>
                        <a href="{{ URL::to('admin') }}" class="navbar-brand visible-xs"><img src="{{ URL::to('vendor/redooor/redminportal/img/favicon.png') }}" title="RedminPortal" class="redooor-nav-logo"> RedminPortal</a>
                    </div>
                    <div class="navbar-collapse collapse">
                        {{ \Redooor\Redminportal\App\Helpers\Rhelper::printMenu(config('redminportal::menu'), 'nav navbar-nav hidden-lg hidden-md hidden-sm') }}
                        <ul class="nav navbar-nav navbar-right">
                            <li><a class="btn btn-link hidden-xs" href="{{ URL::to('logout') }}" title="Lang::get('redminportal::menus.logout')">{{ Lang::get('redminportal::menus.logout') }} <i class="glyphicon glyphicon-log-out"></i></a></li>
                            <li><a class="visible-xs" href="{{ URL::to('logout') }}" title="Lang::get('redminportal::menus.logout')">{{ Lang::get('redminportal::menus.logout') }}</a></li>
                        </ul>
                    </div><!--/.nav-collapse -->

                </div>
            </div>
        </header>

        <div id="main">
            <div class="container-fluid">
                <div id="sidebar-wrapper">
                    <div id="sidebar">
                        <a href="{{ URL::to('admin') }}" class="redooor-nav-logo"><img src="{{ URL::to('vendor/redooor/redminportal/img/favicon.png') }}" title="RedminPortal"> RedminPortal</a>
                        <hr>
                        {{ \Redooor\Redminportal\App\Helpers\Rhelper::printMenu(config('redminportal::menu'), 'nav nav-sidebar') }}
                    </div>
                    <div id="sidebar-overlay" class="sidebar-toggle"></div>
                    <div class="main-content">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div><!--End main-->

        <div id="confirm-modal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">{{ Lang::get('redminportal::messages.confirm_delete') }}</h4>
                    </div>
                    <div class="modal-body">
                        <p>{{ Lang::get('redminportal::messages.are_you_sure_you_want_to_delete') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ Lang::get('redminportal::buttons.delete_no') }}</button>
                        <a href="#" id="confirm-delete" class="btn btn-danger">{{ Lang::get('redminportal::buttons.delete_yes') }}</a>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
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
                        $('#confirm-delete').attr('href', $delete_url);
                        $('#confirm-modal').modal('show');
                    });
                    $(document).on('click', '.sidebar-toggle', function(e) {
                        e.preventDefault();
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
