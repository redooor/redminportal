@extends('redminportal::layouts.master')

@section('content')
    @if (isset($errors))
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
    @endif
    <div class="nav-controls text-right">
        @if (count($portfolios) > 0)
        <span class="label label-default pull-left">
            {{ $portfolios->firstItem() . ' to ' . $portfolios->lastItem() . ' ( total ' . $portfolios->total() . ' )' }}
        </span>
        @endif
        {!! HTML::link('admin/portfolios/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary')) !!}
    </div>
    
    @if (count($portfolios) > 0)
        <table class='table table-striped table-bordered'>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Short Description</th>
                    <th class='hide'>Long Description</th>
                    <th>Active</th>
                    <th class='hide'>Photos</th>
                    <th class='hide'>Updated</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($portfolios as $portfolio)
                <tr>
                    <td>{{ $portfolio->name }}</td>
                    <td>{{ $portfolio->category->name }}</td>
                    <td>{{ $portfolio->short_description }}</td>
                    <td class='hide'>{{ $portfolio->long_description }}</td>
                    <td>
                        @if ($portfolio->active)
                            <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td class='hide'>
                        @foreach( $portfolio->images as $image )
                        {{ $image->path }}<br/>
                        @endforeach
                    </td>
                    <td class='hide'>{{ $portfolio->updated_at }}</td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                Action <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>
                                    <a href="{{ URL::to('admin/portfolios/edit/' . $portfolio->id) }}">
                                        <i class="glyphicon glyphicon-edit"></i>Edit</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/portfolios/delete/' . $portfolio->id) }}" class="btn-confirm">
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
        {!! $portfolios->render() !!}
        </div>
    @else
        <div class="alert alert-info">No portfolio found</div>
    @endif
@stop
