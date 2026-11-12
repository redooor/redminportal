@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::menus.medias') }}</span></li>
@stop

@section('content')
    @include('redminportal::partials.confirm-delete')

    @include('redminportal::partials.errors')

    <div class="row">
        <div class="col-lg-12">
            <div class="nav-controls text-end">
                <div class="btn-group" role="group">
                @if (count($models) > 0)
                    <a href="" class="btn btn-secondary btn-sm disabled btn-text">{{ trans('redminportal::messages.list_from_to', ['firstItem' => $models->firstItem(), 'lastItem' => $models->lastItem(), 'totalItem' => $models->total()]) }}</a>
                @endif
                <a id="rd-media-get-all-duration" class="btn btn-secondary btn-sm">{{ Lang::get('redminportal::buttons.get_durations') }}</a>
                @permitted('admin/medias/create')
                <a href="{{ url('admin/medias/create') }}" class="btn btn-primary btn-sm">{{ Lang::get('redminportal::buttons.create_new') }}</a>
                @endpermitted
            </div>
            </div>
        </div>
    </div>

    @if (count($models) > 0)
        <div class="table-responsive">
        <table class='table table-striped table-sm'>
            <thead>
                <tr>
                    <th>{!! Redminportal::html()->sorter('admin/medias', 'name', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/medias', 'category_name', $sortBy, $orderBy, trans('redminportal::forms.category')) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/medias', 'sku', $sortBy, $orderBy) !!}</th>
                    <th>{{ Lang::get('redminportal::forms.summary') }}</th>
                    <th>{{ Lang::get('redminportal::forms.tags') }}</th>
                    <th>{!! Redminportal::html()->sorter('admin/medias', 'featured', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/medias', 'active', $sortBy, $orderBy) !!}</th>
                    <th>{{ Lang::get('redminportal::forms.media_file') }}</th>
                    <th>{{ Lang::get('redminportal::forms.duration') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($models as $media)
                <tr>
                    <td>{{ $media->name }}</td>
                    <td>{{ $media->category->name }}</td>
                    <td>{{ $media->sku }}</td>
                    <td>{{ $media->short_description }}</td>
                    <td>
                        @foreach( $media->tags as $tag)
                        <span class="badge text-bg-info">{{ $tag->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if ($media->featured)
                            <span class="badge text-bg-success"><span class='fa-solid fa-check'></span></span>
                        @else
                            <span class="badge text-bg-danger"><span class='fa-solid fa-xmark'></span></span>
                        @endif
                    </td>
                    <td>
                        @if ($media->active)
                            <span class="badge text-bg-success"><span class='fa-solid fa-check'></span></span>
                        @else
                            <span class="badge text-bg-danger"><span class='fa-solid fa-xmark'></span></span>
                        @endif
                    </td>
                    <td>
                        @if (file_exists(public_path() . '/assets/medias/' . $media->category_id . '/' . $media->id . '/' . $media->path))
                            <a class="bs-tooltip" data-bs-toggle="tooltip" data-bs-placement="left" title="{{ $media->path }}" href="{{ url('/assets/medias/' . $media->category_id . '/' . $media->id . '/' . $media->path) }}" target="_blank"><span class='fa-solid fa-file'></span> {{ $media->mimetype }}</a>
                        @endif
                    </td>
                    <td>
                        <span class="rd-media-duration"
                            data-url="{{ URL::to('admin/medias/duration/' . $media->id) }}">@if(isset(json_decode($media->options)->duration)){{ json_decode($media->options)->duration }}@endif</span>
                    </td>
                    <td class="table-actions text-end">
                        @permitted('admin/medias/uploadform/' . $media->id, 'admin/medias/edit/' . $media->id, 'admin/medias/delete/' . $media->id)
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
								<i class="fa-solid fa-ellipsis"></i>
							</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @permitted('admin/medias/uploadform/' . $media->id)
                                <li>
                                    <a class="dropdown-item" href="{{ URL::to('admin/medias/uploadform/' . $media->id) }}">
                                        <i class="fa-solid fa-upload"></i>Upload Media</a>
                                </li>
                                @endpermitted
                                @permitted('admin/medias/edit/' . $media->id)
                                <li>
                                    <a class="dropdown-item" href="{{ URL::to('admin/medias/edit/' . $media->id) }}">
                                        <i class="fa-solid fa-pen-to-square"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                </li>
                                @endpermitted
                                @permitted('admin/medias/delete/' . $media->id)
                                <li>
                                    <a class="dropdown-item btn-confirm" href="{{ URL::to('admin/medias/delete/' . $media->id) }}">
                                        <i class="fa-solid fa-xmark"></i>{{ Lang::get('redminportal::buttons.delete') }}</a>
                                </li>
                                @endpermitted
                            </ul>
                        </div>
                        @endpermitted
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div class="text-center">
        {!! $models->render('pagination::bootstrap-5') !!}
        </div>
    @else
        @if ($models->total() > 0)
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_record_page_empty') }}</div>
        <a href="{{ $models->url($models->lastPage()) }}" class="btn btn-secondary"><i class="fa-solid fa-chevron-left"></i> {{ Lang::get('redminportal::buttons.previous_page') }}</a>
        @else
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_media_found') }}</div>
        @endif
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
                            $.post(url, { _token: window.redminCsrfToken() }, function(data) {
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
