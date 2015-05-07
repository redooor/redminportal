@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li><a href="{{ URL::to('admin/promotions') }}">{{ Lang::get('redminportal::menus.promotions') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::forms.edit') }}</li>
            </ol>
        </div>
    </div>
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\PromotionController@postStore', 'role' => 'form')) !!}
        {!! Form::hidden('id', $promotion->id) !!}

    	<div class='row'>
    	    <div class="col-md-3 col-md-push-9">
                <div class="well">
                    <div class='form-actions'>
                        {!! HTML::link('admin/promotions', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.save'), array('class' => 'btn btn-primary pull-right')) !!}
                    </div>
                </div>
                <div class='well well-sm'>
                    <div class="form-group">
                        <div class="checkbox">
                            <label for="active-checker">
                                {!! Form::checkbox('active', $promotion->active, $promotion->active, array('id' => 'active-checker')) !!} Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="well well-sm">
                    <div class="form-group">
                        {!! Form::label('start_date', 'Start Date') !!}
                        <div class="input-group" id='start-date'>
                            {!! Form::input('text', 'start_date', $start_date->format('d/m/Y'), array('class' => 'form-control datepicker', 'readonly')) !!}
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('end_date', 'End Date') !!}
                        <div class="input-group" id='end-date'>
                            {!! Form::input('text', 'end_date', $end_date->format('d/m/Y'), array('class' => 'form-control datepicker', 'readonly')) !!}
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                        </div>
                    </div>
                </div>
                <div class="fileupload fileupload-new" data-provides="fileupload">
                    <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
                    <div>
                        <span class="btn btn-default btn-file"><span class="fileupload-new">Upload photo</span><span class="fileupload-exists">Change</span>{!! Form::file('image') !!}</span>
                        <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">Remove</a>
                    </div>
                </div>
            </div>
    	    <div class="col-md-9 col-md-pull-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.edit_promotion') }}</h4>
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-tabs" id="lang-selector">
                            <li class="active"><a href="#lang-en">English</a></li>
                            <li><a href="#lang-sc">Chinese</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="lang-en">
                                <div class="form-group">
                                    {!! Form::label('name', 'Title') !!}
                                    {!! Form::text('name', $promotion->name, array('class' => 'form-control')) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('short_description', 'Summary') !!}
                                    {!! Form::text('short_description', $promotion->short_description, array('class' => 'form-control')) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('long_description', 'Description') !!}
                                    {!! Form::textarea('long_description', $promotion->long_description, array('class' => 'form-control')) !!}
                                </div>
                            </div>
                            <div class="tab-pane" id="lang-sc">
                                <div class="form-group">
                                    {!! Form::label('cn_name', '标题') !!}
                                    {!! Form::text('cn_name', $promotion_cn->name, array('class' => 'form-control')) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('cn_short_description', '简介') !!}
                                    {!! Form::text('cn_short_description', $promotion_cn->short_description, array('class' => 'form-control')) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('cn_long_description', '内容') !!}
                                    {!! Form::textarea('cn_long_description', $promotion_cn->long_description, array('class' => 'form-control')) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if (count($promotion->images) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.uploaded_photos') }}</h4>
                    </div>
                    <div class="panel-body">
                        <div class='row'>
                            @foreach ($promotion->images as $image)
                             <div class='col-md-3'>
                                {!! HTML::image($imagine->getUrl($image->path), $promotion->name, array('class' => 'img-thumbnail', 'alt' => $image->path)) !!}
                                <br><br>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ URL::to('admin/promotions/imgremove/' . $image->id) }}" class="btn btn-danger btn-confirm">
                                        <span class="glyphicon glyphicon-remove"></span>
                                    </a>
                                    <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-primary btn-copy">
                                        <span class="glyphicon glyphicon-link"></span>
                                    </a>
                                    <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-info" target="_blank">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
    	</div>
    {!! Form::close() !!}
@stop

@section('footer')
    <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap-fileupload.js') }}"></script>
    <script>
        !function ($) {
            $(function(){
                $( ".datepicker" ).datepicker({ dateFormat: "dd/mm/yy" });

                $( "#end-date .input-group-addon" ).click( function() {
                    $( "#end_date" ).datepicker( "show" );
                });

                $( "#start-date .input-group-addon" ).click( function() {
                    $( "#start_date" ).datepicker( "show" );
                });

                $('#lang-selector a').click(function (e) {
                    e.preventDefault();
                    $(this).tab('show');
                });
            })
        }(window.jQuery);
    </script>
    @include('redminportal::plugins/tinymce')
@stop
