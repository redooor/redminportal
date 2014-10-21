@extends('redminportal::layouts.master')

@section('content')
    @if($errors->has())
    <div class='alert alert-danger'>
        We encountered the following errors:
        <ul>
            @foreach($errors->all() as $message)
            <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="nav-controls text-right">
        {{ HTML::link('admin/categories/create', 'Create New', array('class' => 'btn btn-primary')) }}
    </div>

    @if (count($categories) > 0)
        <div class="row">
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <ul class="redooor-hierarchy no-max-height">
                        @foreach ($categories as $item)
                            <li>{{ $item->printCategory(true) }}</li>
                        @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="rdpt-preloader"><img src="{{ URL::to('packages/redooor/redminportal/assets/img/Preloader_2.gif') }}" class="img-circle"/></div>
                <div id="category-detail">
                    <p><span class="label label-info"><span class="glyphicon glyphicon-chevron-left"></span> Select category to view detail</span></p>
                </div>
            </div>
        </div>
    @else
        <p>No category found</p>
    @endif
@stop

@section('footer')
    <script>
        !function ($) {
            $(function(){
                $(document).on('click', '.redooor-hierarchy a', function(e) {
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
