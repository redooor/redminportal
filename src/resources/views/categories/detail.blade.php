<div class="panel panel-default">
    <div class="panel-heading">
        {{ $category->name }}
        <div class="btn-group pull-right">
            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                Action <span class="caret"></span>
            </button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>
                    <a href="{{ URL::to('admin/categories/edit/' . $category->id) }}">
                        <i class="glyphicon glyphicon-edit"></i>Edit</a>
                </li>
                <li>
                    <a href="{{ URL::to('admin/categories/delete/' . $category->id) }}" class="btn-confirm">
                        <i class="glyphicon glyphicon-remove"></i>Delete</a>
                </li>
            </ul>
        </div>
    </div>
    <ul class="list-group">
        <li class="list-group-item">@if ($category->active)<span class="label label-success">active</span>@else <span class="label label-danger">inactive</span> @endif</li>
        <li class="list-group-item"><strong>Updated at:</strong><br>{{ $category->updated_at }}</li>
        <li class="list-group-item"><strong>Parent:</strong><br>@if($category->category_id > 0){{ $category->parentCategory->name }}@else{{ "None" }}@endif</li>
        <li class="list-group-item"><strong>Order number:</strong><br>{{ $category->order }}</li>
        <li class="list-group-item"><strong>Short Description:</strong><br>{{ $category->short_description }}</li>
        <li class="list-group-item"><strong>Long Description:</strong><br>{!! $category->long_description !!}</li>
        @if($category->images()->count() > 0)
        <li class="list-group-item">
            @foreach( $category->images as $image )
            {!! HTML::image($imageUrl . $image->path, $category->name, array('class' => 'img-thumbnail', 'alt' => $image->path)) !!}
            @endforeach
        </li>
        @endif
    </ul>
</div>
