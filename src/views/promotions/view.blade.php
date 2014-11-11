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
        @if (count($promotions) > 0)
        <span class="label label-default pull-left">
            {{ $promotions->getFrom() . ' to ' . $promotions->getTo() . ' ( total ' . $promotions->getTotal() . ' )' }}
        </span>
        @endif
        {{ HTML::link('admin/promotions/create', 'Create New', array('class' => 'btn btn-primary')) }}
    </div>
    
    @if (count($promotions) > 0)
        <table class='table table-striped table-bordered'>
            <thead>
                <tr>
                    <th>Promotions and Events</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Active</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($promotions as $promotion)
                <tr>
                    <td>
                        {{ $promotion->name }}
                        <span class="hide">
                            {{ $promotion->short_description }}
                            {{ $promotion->long_description }}
                            @foreach( $promotion->images as $image )
                            {{ $image->path }}<br/>
                            @endforeach
                        </span>
                        <span class="hide">
                            {{ $promotion->updated_at }}
                        </span>
                    </td>
                    <td>{{ $promotion->start_date }}</td>
                    <td>{{ $promotion->end_date }}</td>
                    <td>
                        @if ($promotion->active)
                            <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td>
						<div class="btn-group">
						  	<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
						    	Action <span class="caret"></span>
						  	</button>
						  	<ul class="dropdown-menu pull-right" role="menu">
						        <li>
						            <a href="{{ URL::to('admin/promotions/edit/' . $promotion->id) }}">
						                <i class="glyphicon glyphicon-edit"></i>Edit</a>
						        </li>
						        <li>
						            <a href="{{ URL::to('admin/promotions/delete/' . $promotion->id) }}" class="btn-confirm">
						                <i class="glyphicon glyphicon-remove"></i>Delete</a>
						        </li>
						  	</ul>
						</div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $promotions->links() }}
    @else
        <div class="alert alert-info">No promotion found</div>
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