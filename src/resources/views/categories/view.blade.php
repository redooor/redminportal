@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::menus.categories') }}</span></li>
@stop

@section('content')

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-lg-12">
            <div class="nav-controls text-end">
                <a href="{{ url('admin/categories/create') }}" class="btn btn-primary btn-sm">{{ Lang::get('redminportal::buttons.create_new') }}</a>
            </div>
        </div>
    </div>

    @if (count($categories) > 0)
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <ul class="redmin-hierarchy no-max-height">
                        @foreach ($categories as $item)
                            <li>{!! $item->printCategory(true) !!}</li>
                        @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="rdpt-preloader"><img src="{{ URL::to('vendor/redooor/redminportal/img/Preloader_2.gif') }}" class="rounded-circle"/></div>
                <div id="category-detail">
                    <div class="alert alert-info">
                        <i class="fa-solid fa-chevron-left"></i> {{ Lang::get('redminportal::messages.select_category_to_view_detail') }}
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_category_found') }}</div>
    @endif
@stop

@section('footer')
    <script>
        !function ($) {
            $(function(){
                $(document).on('click', '.redmin-hierarchy a', function(e) {
                    e.preventDefault();
                    $('.rdpt-preloader').show();
                    $url = "{{ URL::to('admin/categories/detail') }}";
                    $detail_url = $url + '/' + $(this).attr('href');
                    $('#category-detail').empty().load($detail_url, function() {
                        $('.rdpt-preloader').hide();
                    });
                    return false;
                });
            })
        }(window.jQuery);
    </script>
@stop
