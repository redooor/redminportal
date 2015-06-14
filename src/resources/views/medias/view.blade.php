@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::menus.medias') }}</li>
            </ol>
        </div>
    </div>

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-md-12">
            <div class="nav-controls text-right">
                <div class="btn-group" role="group">
                @if (count($medias) > 0)
                <a href="" class="btn btn-default btn-sm disabled btn-text">{{ $medias->firstItem() . ' to ' . $medias->lastItem() . ' of ' . $medias->total() }}</a>
                @endif
                <a id="rd-media-get-all-duration" class="btn btn-default btn-sm">{{ Lang::get('redminportal::buttons.get_durations') }}</a>
                {!! HTML::link('admin/medias/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary btn-sm')) !!}
            </div>
            </div>
        </div>
    </div>

    @if (count($medias) > 0)
        <table class='table table-striped table-bordered table-condensed'>
            <thead>
                <tr>
                    <th>{{ Lang::get('redminportal::forms.name') }}</th>
                    <th>{{ Lang::get('redminportal::forms.category') }}</th>
                    <th>{{ Lang::get('redminportal::forms.sku') }}</th>
                    <th>{{ Lang::get('redminportal::forms.summary') }}</th>
                    <th>{{ Lang::get('redminportal::forms.tags') }}</th>
                    <th>{{ Lang::get('redminportal::forms.featured') }}</th>
                    <th>{{ Lang::get('redminportal::forms.active') }}</th>
                    <th>{{ Lang::get('redminportal::forms.media_file') }}</th>
                    <th>{{ Lang::get('redminportal::forms.duration') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($medias as $media)
                <tr>
                    <td>{{ $media->name }}</td>
                    <td>{{ $media->category->name }}</td>
                    <td>{{ $media->sku }}</td>
                    <td>{{ $media->short_description }}</td>
                    <td>
                        @foreach( $media->tags as $tag)
                        <span class="label label-info">{{ $tag->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if ($media->featured)
                            <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td>
                        @if ($media->active)
                            <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td>
                        @if (file_exists(public_path() . '/assets/medias/' . $media->category_id . '/' . $media->id . '/' . $media->path))
                            <span class="label label-success bs-tooltip" data-toggle="tooltip" data-placement="left" title="{{ $media->path }}"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td>
                        <span class="rd-media-duration"
                            data-url="{{ URL::to('admin/medias/duration/' . $media->id) }}">@if(isset(json_decode($media->options)->duration)){{ json_decode($media->options)->duration }}@endif</span>
                    </td>
                    <td class="table-actions text-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
								<span class="glyphicon glyphicon-option-horizontal"></span>
							</button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>
                                    <a href="{{ URL::to('admin/medias/uploadform/' . $media->id) }}">
                                        <i class="glyphicon glyphicon-upload"></i>Upload Media</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/medias/edit/' . $media->id) }}">
                                        <i class="glyphicon glyphicon-edit"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/medias/delete/' . $media->id) }}" class="btn-confirm">
                                        <i class="glyphicon glyphicon-remove"></i>{{ Lang::get('redminportal::buttons.delete') }}</a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="text-center">
        {!! $medias->render() !!}
        </div>
    @else
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_media_found') }}</div>
    @endif
@stop

@section('footer')
    <script>
        !function ($) {
            $(function(){
                // Tooltip
                $('.bs-tooltip').tooltip();
                // Get all media durations if duration is empty
                $(document).on('click', '#rd-media-get-all-duration', function(e) {
                    e.preventDefault();
                    // Loop through all media's duration
                    $('.rd-media-duration').each(function() {
                        var obj = $(this);
                        if (obj.text() == '') {
                            var url = obj.attr('data-url');
                            obj.text('Fetching');
                            $.get(url, function(data) {
                                if (data.status == 'success') {
                                    obj.text(data.data);
                                } else {
                                    obj.text('Error');
                                }
                            })
                            .fail(function() {
                                obj.text('Failed connection')
                            });
                        }
                    });
                });
            })
        }(window.jQuery);
    </script>
@stop
