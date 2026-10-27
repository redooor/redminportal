@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/promotions') }}">{{ Lang::get('redminportal::menus.promotions') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\PromotionController@postStore', 'role' => 'form')) !!}

    	<div class='row'>
            <div class="col-lg-3 order-lg-last">
                <div class="card card-body bg-body-tertiary">
                    <div class='form-actions'>
                        {!! HTML::link('admin/promotions', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm float-end')) !!}
                    </div>
                </div>
                <div class='card card-body bg-body-tertiary p-2'>
                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="active-checker">
                                <input class="form-check-input" id="active-checker" checked="checked" name="active" type="checkbox" value="1"> {{ Lang::get('redminportal::forms.active') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card card-body bg-body-tertiary p-2">
                    <div class="mb-3">
                        {!! Form::label('start_date', Lang::get('redminportal::forms.start_date')) !!}
                        <div class="input-group" id='start-date'>
                            {!! Form::input('text', 'start_date', null, array('class' => 'form-control datepicker', 'readonly')) !!}
                            <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        {!! Form::label('end_date', Lang::get('redminportal::forms.end_date')) !!}
                        <div class="input-group" id='end-date'>
                            {!! Form::input('text', 'end_date', null, array('class' => 'form-control datepicker', 'readonly')) !!}
                            <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                      <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
                      <div>
                        <span class="btn btn-secondary btn-file"><span class="fileupload-new">{{ Lang::get('redminportal::forms.select_image') }}</span><span class="fileupload-exists">{{ Lang::get('redminportal::forms.change_image') }}</span><input name="image" type="file"></span>
                        <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">{{ Lang::get('redminportal::forms.remove_image') }}</a>
                      </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9 order-lg-first">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.create_promotion') }}</h4>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="lang-selector">
                           @foreach(\Config::get('redminportal::translation') as $translation)
                           <li class="nav-item"><a class="nav-link @if ($loop->first)active @endif" href="#lang-{{ $translation['lang'] }}" data-bs-toggle="tab" data-bs-target="#lang-{{ $translation['lang'] }}">{{ $translation['name'] }}</a></li>
                           @endforeach
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="lang-en">
                                <div class="mb-3">
                                    {!! Form::label('name', Lang::get('redminportal::forms.title')) !!}
                                    {!! Form::text('name', null, array('class' => 'form-control')) !!}
                                </div>

                                <div class="mb-3">
                                    {!! Form::label('short_description', Lang::get('redminportal::forms.summary')) !!}
                                    {!! Form::text('short_description', null, array('class' => 'form-control')) !!}
                                </div>

                                <div class="mb-3">
                                    {!! Form::label('long_description', Lang::get('redminportal::forms.description')) !!}
                                    {!! Form::textarea('long_description', null, array('class' => 'form-control', 'style' => 'height:200px')) !!}
                                </div>
                            </div>
                            @foreach(\Config::get('redminportal::translation') as $translation)
                                @if($translation['lang'] != 'en')
                                <div class="tab-pane fade" id="lang-{{ $translation['lang'] }}">
                                    <div class="mb-3">
                                        {!! Form::label($translation['lang'] . '_name', Lang::get('redminportal::forms.title')) !!}
                                        {!! Form::text($translation['lang'] . '_name', null, array('class' => 'form-control')) !!}
                                    </div>

                                    <div class="mb-3">
                                        {!! Form::label($translation['lang'] . '_short_description', Lang::get('redminportal::forms.summary')) !!}
                                        {!! Form::text($translation['lang'] . '_short_description', null, array('class' => 'form-control')) !!}
                                    </div>

                                    <div class="mb-3">
                                        {!! Form::label($translation['lang'] . '_long_description', Lang::get('redminportal::forms.description')) !!}
                                        {!! Form::textarea($translation['lang'] . '_long_description', null, array('class' => 'form-control', 'style' => 'height:200px')) !!}
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
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

                $( "#end-date .input-group-text" ).click( function() {
                    $( "#end_date" ).datepicker( "show" );
                });

                $( "#start-date .input-group-text" ).click( function() {
                    $( "#start_date" ).datepicker( "show" );
                });

            })
        }(window.jQuery);
    </script>
    @include('redminportal::plugins/tinymce')
@stop
