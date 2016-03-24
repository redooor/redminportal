@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li><a href="{{ URL::to('admin/portfolios') }}">{{ Lang::get('redminportal::menus.portfolios') }}</a></li>
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\PortfolioController@postStore', 'role' => 'form')) !!}

    <div class='row'>
        <div class="col-md-3 col-md-push-9">
            <div class="well">
                <div class='form-actions'>
                    {!! HTML::link('admin/portfolios', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                    {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm pull-right')) !!}
                </div>
            </div>
            <div class='well well-small'>
                <div class="form-group">
                    <div class="checkbox">
                        <label for="active-checker">
                            {!! Form::checkbox('active', true, true, array('id' => 'active-checker')) !!} {{ Lang::get('redminportal::forms.active') }}
                        </label>
                    </div>
                </div>
            </div>
            {{-- Load Select Category partial form --}}
            @include('redminportal::partials.form-select-category', [
                'select_category_selected_name' => 'category_id',
                'select_category_categories' => $categories,
                'select_category_required_field' => true
            ])
            <div>
                <div class="fileupload fileupload-new" data-provides="fileupload">
                  <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
                  <div>
                    <span class="btn btn-default btn-file"><span class="fileupload-new">{{ Lang::get('redminportal::forms.select_image') }}</span><span class="fileupload-exists">{{ Lang::get('redminportal::forms.change_image') }}</span>{!! Form::file('image') !!}</span>
                    <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">{{ Lang::get('redminportal::forms.remove_image') }}</a>
                  </div>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-md-pull-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ Lang::get('redminportal::forms.create_portfolio') }}</h4>
                </div>
                <div class="panel-body">
                    <ul class="nav nav-tabs" id="lang-selector">
                       @foreach(\Config::get('redminportal::translation') as $translation)
                       <li><a href="#lang-{{ $translation['lang'] }}">{{ $translation['name'] }}</a></li>
                       @endforeach
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="lang-en">
                            <div class="form-group">
                                {!! Form::label('name', Lang::get('redminportal::forms.title')) !!}
                                {!! Form::text('name', null, array('class' => 'form-control')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('short_description', Lang::get('redminportal::forms.summary')) !!}
                                {!! Form::text('short_description', null, array('class' => 'form-control')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('long_description', Lang::get('redminportal::forms.description')) !!}
                                {!! Form::textarea('long_description', null, array('class' => 'form-control', 'style' => 'height:200px')) !!}
                            </div>
                        </div>
                        @foreach(\Config::get('redminportal::translation') as $translation)
                            @if($translation['lang'] != 'en')
                            <div class="tab-pane" id="lang-{{ $translation['lang'] }}">
                                <div class="form-group">
                                    {!! Form::label($translation['lang'] . '_name', Lang::get('redminportal::forms.title')) !!}
                                    {!! Form::text($translation['lang'] . '_name', null, array('class' => 'form-control')) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label($translation['lang'] . '_short_description', Lang::get('redminportal::forms.summary')) !!}
                                    {!! Form::text($translation['lang'] . '_short_description', null, array('class' => 'form-control')) !!}
                                </div>

                                <div class="form-group">
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
    @parent
    <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap-fileupload.js') }}"></script>
    <script>
        !function ($) {
            $(function(){
                $('#lang-selector li').first().addClass('active');
                $('#lang-selector a').click(function (e) {
                    e.preventDefault();
                    $(this).tab('show');
                });
            })
        }(window.jQuery);
    </script>
    @include('redminportal::plugins/tinymce')
@stop
