<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title>RedminPortal by Redooor</title>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/blitzer/jquery-ui.css" />
        {{ HTML::style('packages/redooor/redminportal/assets/css/jasny-bootstrap.css') }}
        {{ HTML::style('packages/redooor/redminportal/assets/css/jasny-responsive.css') }}
        {{ HTML::style('packages/redooor/redminportal/assets/css/redminportal.css') }}
        <!--[if lt IE 9]>
        <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        <link rel="shortcut icon" type="image/png" href="{{ URL::to('packages/redooor/redminportal/assets/img/favicon.png') }}"/>
        @section('head')
        @show
    </head>
    <body>
        <header id="header">
            <div class="navbar navbar-default navbar-fixed-top">
                <div class="container">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a href="{{ URL::to('admin') }}" class="navbar-brand">
                            {{ HTML::image( URL::to('packages/redooor/redminportal/assets/img/redminportal_logo.png'), 'RedminPortal', array('class' => 'redooor-nav-logo') )}} RedminPortal
                        </a>
                    </div>
                    <div class="navbar-collapse collapse">
                        @if(Sentry::check())
                            @if(Sentry::getUser()->hasAccess('admin'))
                            <ul class="nav navbar-nav">
                                <li class="dropdown" id="navbar-menu-dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-th"></span> {{ Lang::get('redminportal::menus.menu') }} <b class="caret"></b></a>
                                    <ul class="dropdown-menu">
                                        @foreach(\Config::get('redminportal::menu') as $menu)
                                            @if(!$menu['hide'])
                                                @if(Request::is($menu['path'])) <li class="active"> @else <li> @endif {{ HTML::link($menu['path'], Lang::get('redminportal::menus.' . $menu['name'])) }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </li>
                                <li class="navbar-text navbar-menu-current hidden-xs">
                                    {{ Lang::get('redminportal::menus.location') }} <span class="glyphicon glyphicon-chevron-right"></span> <span id="navbar-menu-current-text">{{ Lang::get('redminportal::menus.home') }}</span>
                                </li>
                            </ul>
                            @endif
                            <ul class="nav navbar-nav navbar-right">
                                <li>{{ HTML::link('logout', Lang::get('redminportal::menus.logout')) }}</li>
                            </ul>
                        @endif
                    </div><!--/.nav-collapse -->

                </div>
            </div>
        </header>

        <div id="main">
            <div class="container">
                @yield('content')
            </div>
        </div><!--End main-->

        <div id="confirm-modal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Confirm delete?</h4>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, no!</button>
                        <a href="#" id="confirm-delete" class="btn btn-danger">Yes, delete</a>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <script src="{{ URL::to('packages/redooor/redminportal/assets/js/vendor/jquery.js') }}"></script>
        <script src="{{ URL::to('packages/redooor/redminportal/assets/js/bootstrap.min.js') }}"></script>
        <script>
            !function ($) {
                $(function(){
                    //$('input[type=file]').bootstrapFileInput();
                    $('.btn-confirm').click( function(e) {
                        e.preventDefault();
                        $delete_url = $(this).attr('href');
                        $('#confirm-delete').attr('href', $delete_url);
                        $('#confirm-modal').modal('show');
                    });
                    var currentLocation = $("#navbar-menu-dropdown ul.dropdown-menu li.active a").html();
                    $("#navbar-menu-current-text").html(currentLocation);
                })
            }(window.jQuery);
        </script>

        @section('footer')
        @show
    </body>
</html>
