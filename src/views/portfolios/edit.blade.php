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

    {{ Form::open(array('files' => TRUE, 'action' => 'Redooor\Redminportal\PortfolioController@postStore', 'role' => 'form')) }}
        {{ Form::hidden('id', $portfolio->id)}}

    	<div class='row'>
            <div class="col-md-3 col-md-push-9">
                <div class='form-actions text-right'>
                    {{ HTML::link('admin/portfolios', 'Cancel', array('class' => 'btn btn-default'))}}
                    {{ Form::submit('Save Changes', array('class' => 'btn btn-primary')) }}
                </div>
                <hr>
                <div class='well well-small'>
                    <div class="form-group">
                        <label for="active" class="checkbox inline">
                            {{ Form::checkbox('active', $portfolio->active, $portfolio->active, array('id' => 'active-checker')) }} Active
                        </label>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel-title">Category</div>
                    </div>
                    <div class="panel-body">
                        {{ Form::hidden('category_id', $portfolio->category_id, array('id' => 'category_id'))}}
                        <ul class="redooor-hierarchy">
                        @foreach ($categories as $item)
                            <li>{{ $item->printCategory() }}</li>
                        @endforeach
                        </ul>
                    </div>
                </div>
                <div>
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                      <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
                      <div>
                        <span class="btn btn-default btn-file"><span class="fileupload-new">Select image</span><span class="fileupload-exists">Change</span>{{ Form::file('image') }}</span>
                        <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">Remove</a>
                      </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9 col-md-pull-3">
                <ul class="nav nav-tabs" id="lang-selector">
                    <li class="active"><a href="#lang-en">English</a></li>
                    <li><a href="#lang-sc">中文</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="lang-en">
                        <div class="form-group">
                            {{ Form::label('name', 'Title') }}
                            {{ Form::text('name', $portfolio->name, array('class' => 'form-control')) }}
                        </div>

                        <div class="form-group">
                            {{ Form::label('short_description', 'Summary') }}
                            {{ Form::text('short_description', $portfolio->short_description, array('class' => 'form-control')) }}
                        </div>

                        <div class="form-group">
                            {{ Form::label('long_description', 'Description') }}
                            {{ Form::textarea('long_description', $portfolio->long_description, array('class' => 'form-control')) }}
                        </div>
                    </div>
                    <div class="tab-pane" id="lang-sc">
                        <div class="form-group">
                            {{ Form::label('cn_name', '标题') }}
                            {{ Form::text('cn_name', $portfolio_cn->name, array('class' => 'form-control')) }}
                        </div>

                        <div class="form-group">
                            {{ Form::label('cn_short_description', '简介') }}
                            {{ Form::text('cn_short_description', $portfolio_cn->short_description, array('class' => 'form-control')) }}
                        </div>

                        <div class="form-group">
                            {{ Form::label('cn_long_description', '内容') }}
                            {{ Form::textarea('cn_long_description', $portfolio_cn->long_description, array('class' => 'form-control')) }}
                        </div>
                    </div>
                </div>
                <h4>Uploaded Photos</h4>
                <div class='row'>
                    @foreach( $portfolio->images as $image )
                    <div class='col-md-3'>
                        <a href="{{ URL::to('admin/portfolios/imgremove/' . $image->id) }}" class="btn-confirm">
                        {{ HTML::image($imagine->getUrl($image->path), $portfolio->name, array('class' => 'img-thumbnail', 'alt' => $image->path)) }}
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    {{ Form::close() }}
@stop

@section('footer')
    <script src="{{ URL::to('packages/redooor/redminportal/assets/js/bootstrap-fileupload.js') }}"></script>
    <script>
        !function ($) {
            $(function(){
                $('#lang-selector a').click(function (e) {
                    e.preventDefault();
                    $(this).tab('show');
                });
                // On load, check if previous category exists for error message
                function checkCategory() {
                    $selected_val = $('#category_id').val();
                    if ($selected_val != '') {
                        $('.redooor-hierarchy a').each(function() {
                            if ($(this).attr('href') == $selected_val) {
                                $(this).addClass('active');
                            }
                        });
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
                });
            })
        }(window.jQuery);
    </script>
    @include('redminportal::plugins/tinymce')
@stop
