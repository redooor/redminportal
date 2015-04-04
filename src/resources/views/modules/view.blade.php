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
        @if (count($modules) > 0)
        <span class="label label-default pull-left">
            {{ $modules->firstItem() . ' to ' . $modules->lastItem() . ' ( total ' . $modules->total() . ' )' }}
        </span>
        @endif
        {{ HTML::link('admin/modules/create', 'Create New', array('class' => 'btn btn-primary')) }}
    </div>

    @if (count($modules) > 0)
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
                    <th class='hide'>Photos</th>
                    <th class='hide'>Updated</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($modules as $module)
                <tr>
                    <td>{{ $module->name }}</td>
                    <td>{{ $module->category->name }}</td>
                    <td>{{ $module->sku }}</td>
                    <td>{{ $module->short_description }}</td>
                    <td class='hide'>{{ $module->long_description }}</td>
                    <td>
                        @foreach( $module->tags as $tag)
                        <span class="label label-info">{{ $tag->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if ($module->featured)
                            <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td>
                        @if ($module->active)
                            <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td class='hide'>
                        @foreach( $module->images as $image )
                        {{ $image->path }}<br/>
                        @endforeach
                    </td>
                    <td class='hide'>{{ $module->updated_at }}</td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                Action <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>
                                    <a href="{{ URL::to('admin/modules/edit/' . $module->id) }}">
                                        <i class="glyphicon glyphicon-edit"></i>Edit</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/modules/delete/' . $module->id) }}" class="btn-confirm">
                                        <i class="glyphicon glyphicon-remove"></i>Delete</a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="text-center">
        {{ $modules->render() }}
        </div>
    @else
        <div class="alert alert-info">No module found</div>
    @endif
@stop
