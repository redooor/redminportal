<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title>RedminPortal by Redooor</title>
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/jquery-ui/themes/blitzer/jquery-ui.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/redmaterials.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/redminportal.min.css') }}">
        <link rel="shortcut icon" type="image/png" href="{{ URL::to('vendor/redooor/redminportal/img/favicon.png') }}"/>
        <style>
            body {
                padding-top: 20px;
                padding-bottom: 20px;
            }
        </style>
        @section('head')
        @show
    </head>
    <body>
        
        <div id="main">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div><!--End main-->

        <script src="{{ URL::to('vendor/redooor/redminportal/js/jquery/jquery.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/redmaterials.min.js') }}"></script>

        @section('footer')
        @show
    </body>
</html>
