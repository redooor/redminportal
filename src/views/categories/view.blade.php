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
        @if ($categories)
        <span class="label label-default pull-left">
            {{ $categories->getFrom() . ' to ' . $categories->getTo() . ' ( total ' . $categories->getTotal() . ' )' }}
        </span>
        @endif
        {{ HTML::link('admin/categories/create', 'Create New', array('class' => 'btn btn-primary')) }}
    </div>
    
    @if ($categories)
        <table class='table table-striped table-bordered'>
            <thead>
                <tr>
                    <th>Priority</th>
                    <th>Name</th>
                    <th>Short Description</th>
                    <th>Long Description</th>
                    <th>Active</th>
                    <th class='hide'>Photos</th>
                    <th>Updated</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($categories as $category)
                <tr>
                    <td>{{ $category->order }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->short_description }}</td>
                    <td>{{ $category->long_description }}</td>
                    <td>
                        @if ($category->active)
                            <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td class='hide'>
                        @foreach( $category->images as $image )
                        {{ $image->path }}<br/>
                        @endforeach
                    </td>
                    <td>{{ $category->updated_at }}</td>
                    <td>
                        <div class="btn-group">
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
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $categories->links() }}
    @else
        <p>No category found</p>
    @endif
@stop

@section('footer')
    <script>
        !function ($) {
            $(function(){
                // Add pagination class to ul
                $('div.pagination > ul').addClass('pagination');
                $('div.pagination').removeClass('pagination').addClass('text-center');
            })
        }(window.jQuery);
    </script>
@stop