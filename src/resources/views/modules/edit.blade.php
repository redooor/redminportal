@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li><a href="{{ URL::to('admin/modules') }}">{{ Lang::get('redminportal::menus.modules') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::forms.edit') }}</li>
            </ol>
        </div>
    </div>
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\ModuleController@postStore', 'role' => 'form')) !!}
        {!! Form::hidden('id', $module->id) !!}

    	<div class='row'>
            <div class="col-md-3 col-md-push-9">
                <div class="well">
                    <div class='form-actions'>
                        {!! HTML::link('admin/modules', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.save'), array('class' => 'btn btn-primary btn-sm pull-right')) !!}
                    </div>
                </div>
                <div class='well well-small'>
                    <div class="form-group">
                        <div class="checkbox">
                            <label for="featured-checker">
                                {!! Form::checkbox('featured', $module->featured, $module->featured, array('id' => 'featured-checker')) !!} Featured
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label for="active-checker">
                                {!! Form::checkbox('active', $module->active, $module->active, array('id' => 'active-checker')) !!} Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel-title">Category</div>
                    </div>
                    <div class="panel-body">
                        {!! Form::hidden('category_id', $module->category_id, array('id' => 'category_id')) !!}
                        <ul class="redooor-hierarchy">
                        @foreach ($categories as $item)
                            <li>{!! $item->printCategory() !!}</li>
                        @endforeach
                        </ul>
                    </div>
                </div>
                <div>
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                      <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
                      <div>
                        <span class="btn btn-default btn-file"><span class="fileupload-new">Select image</span><span class="fileupload-exists">Change</span>{!! Form::file('image') !!}</span>
                        <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">Remove</a>
                      </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9 col-md-pull-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.edit_module') }}</h4>
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
                                    {!! Form::label('name', 'Title') !!}
                                    {!! Form::text('name', $module->name, array('class' => 'form-control')) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('short_description', 'Summary') !!}
                                    {!! Form::text('short_description', $module->short_description, array('class' => 'form-control')) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('long_description', 'Description') !!}
                                    {!! Form::textarea('long_description', $module->long_description, array('class' => 'form-control')) !!}
                                </div>
                            </div>
                            @foreach(\Config::get('redminportal::translation') as $translation)
                                @if($translation['lang'] != 'en')
                                <div class="tab-pane" id="lang-{{ $translation['lang'] }}">
                                    <div class="form-group">
                                        {!! Form::label($translation['lang'] . '_name', 'Title') !!}
                                        {!! Form::text($translation['lang'] . '_name', $module_cn->name, array('class' => 'form-control')) !!}
                                    </div>

                                    <div class="form-group">
                                        {!! Form::label($translation['lang'] . '_short_description', 'Summary') !!}
                                        {!! Form::text($translation['lang'] . '_short_description', $module_cn->short_description, array('class' => 'form-control')) !!}
                                    </div>

                                    <div class="form-group">
                                        {!! Form::label($translation['lang'] . '_long_description', 'Description') !!}
                                        {!! Form::textarea($translation['lang'] . '_long_description', $module_cn->long_description, array('class' => 'form-control')) !!}
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.module_properties') }}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            {!! Form::label('sku', 'SKU') !!}
                            {!! Form::text('sku', $module->sku, array('class' => 'form-control')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('tags', 'Tags (separated by comma)') !!}
                            {!! Form::text('tags', $tagString, array('class' => 'form-control')) !!}
                        </div>
                    </div>
                </div>
                @if (count($module->images) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.uploaded_photos') }}</h4>
                    </div>
                    <div class="panel-body">
                        <div class='row'>
                            @foreach ($module->images as $image)
                            <div class='col-md-3'>
                                {!! HTML::image($imagine->getUrl($image->path), $module->name, array('class' => 'img-thumbnail', 'alt' => $image->path)) !!}
                                <br><br>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ URL::to('admin/modules/imgremove/' . $image->id) }}" class="btn btn-danger btn-confirm">
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
                @if (isset($pricelists))
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.price_list') }}</h4>
                    </div>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Membership</th>
                                <th>Price</th>
                                <th>Enabled</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($pricelists as $pricelist)
                            <tr>
                                <td>{{ $pricelist['name'] }}</td>
                                <td>{!! Form::text('price_' . $pricelist['id'], $pricelist['price'], array('class' => 'form-control')) !!}</td>
                                <td>
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label for="{{ 'price_active_' . $pricelist['id'] }}">
                                                {!! Form::checkbox('price_active_' . $pricelist['id'], $pricelist['active'], $pricelist['active'], array('id' => 'price_active_' . $pricelist['id'])) !!}
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.medias') }}</h4>
                    </div>
                    <div id="media-wrapper"></div>
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
                $('#lang-selector li').first().addClass('active');
                $('#lang-selector a').click(function (e) {
                    e.preventDefault();
                    $(this).tab('show');
                });
                // Load medias base on selected category
                function loadMedia() {
                    $selected_val = $('#category_id').val();
                    $('#media-wrapper').empty().load('../editmedias/' + $selected_val + '/' + {{ $module->id }});
                }
                // On load, check if previous category exists for error message
                function checkCategory() {
                    $selected_val = $('#category_id').val();
                    if ($selected_val != '') {
                        $('.redooor-hierarchy a').each(function() {
                            if ($(this).attr('href') == $selected_val) {
                                $(this).addClass('active');
                            }
                        });
                        loadMedia();
                    }
                }
                checkCategory();
                // Change selected category
                $(document).on('click', '.redooor-hierarchy a', function(e) {
                    e.preventDefault();
                    $selected = $(this).attr('href');
                    $('#category_id').val($selected);
                    $('.redooor-hierarchy a.active').removeClass('active');
                    $(this).addClass('active');
                    // Load medias
                    loadMedia();
                });
            })
        }(window.jQuery);
    </script>
    @include('redminportal::plugins/tinymce')
@stop
