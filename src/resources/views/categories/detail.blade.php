<div class="card">
    <div class="card-header">
        <h4 class="card-title mb-0">
            {{ $category->name }}
            @if ($category->active)<span class="badge text-bg-success float-end">active</span>@else <span class="badge text-bg-danger float-end">inactive</span> @endif
        </h4>
    </div>
    <ul class="list-group">
        <li class="list-group-item"><strong>{{ Lang::get('redminportal::forms.updated') }}:</strong><br>{{ $category->updated_at }}</li>
        <li class="list-group-item"><strong>{{ Lang::get('redminportal::forms.parent_category') }}:</strong><br>@if($category->category_id > 0){{ $category->parentCategory->name }}@else{{ "None" }}@endif</li>
        <li class="list-group-item"><strong>{{ Lang::get('redminportal::forms.priority_order') }}:</strong><br>{{ $category->order }}</li>
        <li class="list-group-item"><strong>{{ Lang::get('redminportal::forms.summary') }}:</strong><br>{{ $category->short_description }}</li>
        <li class="list-group-item"><strong>{{ Lang::get('redminportal::forms.description') }}:</strong><br>{!! $category->long_description !!}</li>
        @if($category->images()->count() > 0)
        <li class="list-group-item">
            @foreach( $category->images as $image )
            {!! HTML::image($imagine->getUrl($image->path), $category->name, array('class' => 'img-thumbnail', 'alt' => $image->path)) !!}
            @endforeach
        </li>
        @endif
    </ul>
    <div class="card-footer text-end">
        @permitted('admin/categories/edit/' . $category->id)
        <a href="{{ URL::to('admin/categories/edit/' . $category->id) }}" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
        @endpermitted
        @permitted('admin/categories/delete/' . $category->id)
        <a href="{{ URL::to('admin/categories/delete/' . $category->id) }}" class="btn btn-danger btn-confirm"><i class="fa-solid fa-xmark"></i>{{ Lang::get('redminportal::buttons.delete') }}</a>
        @endpermitted
    </div>
</div>
