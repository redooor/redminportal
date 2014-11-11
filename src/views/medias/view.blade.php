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
        @if (count($medias) > 0)
        <span class="label label-default pull-left">
            {{ $medias->getFrom() . ' to ' . $medias->getTo() . ' ( total ' . $medias->getTotal() . ' )' }}
        </span>
        @endif
        <a id="rd-media-get-all-duration" class="btn btn-default">Get Durations</a>
        {{ HTML::link('admin/medias/create', 'Create New', array('class' => 'btn btn-primary')) }}
    </div>

    @if (count($medias) > 0)
        <table class='table table-striped table-bordered'>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>SKU</th>
                    <th>Short Description</th>
                    <th class='hide'>Long Description</th>
                    <th>Tags</th>
                    <th>Featured</th>
                    <th>Active</th>
                    <th>Media File</th>
                    <th>Duration</th>
                    <th class='hide'>Photos</th>
                    <th class='hide'>Updated</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($medias as $media)
                <tr>
                    <td>{{ $media->name }}</td>
                    <td>{{ $media->category->name }}</td>
                    <td>{{ $media->sku }}</td>
                    <td>{{ $media->short_description }}</td>
                    <td class='hide'>{{ $media->long_description }}</td>
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
                    <td class='hide'>
                        @foreach( $media->images as $image )
                        {{ $image->path }}<br/>
                        @endforeach
                    </td>
                    <td class='hide'>{{ $media->updated_at }}</td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                Action <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>
                                    <a href="{{ URL::to('admin/medias/uploadform/' . $media->id) }}">
                                        <i class="glyphicon glyphicon-upload"></i>Upload Media</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/medias/edit/' . $media->id) }}">
                                        <i class="glyphicon glyphicon-edit"></i>Edit</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/medias/delete/' . $media->id) }}" class="btn-confirm">
                                        <i class="glyphicon glyphicon-remove"></i>Delete</a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $medias->links() }}
    @else
        <div class="alert alert-info">No media found</div>
    @endif
@stop

@section('footer')
    <script>
        !function ($) {
            $(function(){
                // Add pagination class to ul
                $('div.pagination > ul').addClass('pagination');
                $('div.pagination').removeClass('pagination').addClass('text-center');
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
