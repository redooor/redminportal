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

    <div class="row">
        <div class="col-md-12">
            <h3>Add New Purchase</h3>
            <hr>
        </div>
    </div>
    {{ Form::open(array('files' => TRUE, 'action' => 'Redooor\Redminportal\PurchaseController@postStore', 'role' => 'form')) }}
    <div class='row'>
        <div class="col-md-3 col-md-push-9">
            <div class="well well-small">
                <div class='form-actions text-right'>
                    {{ HTML::link('admin/purchases', 'Cancel', array('class' => 'btn btn-default'))}}
                    {{ Form::submit('Create', array('class' => 'btn btn-primary')) }}
                </div>
            </div>
        </div>
        <div class="col-md-9 col-md-pull-3">
            <div class="form-group scrollable-dropdown-menu">
                {{ Form::label('email', 'User Email') }}
                {{ Form::text('email', Input::old('email'), array('class' => 'form-control typeahead', 'required')) }}
            </div>
            <div class="form-group">
                {{ Form::label('pricelist_id', 'Module, Membership') }}
                {{ Form::select('pricelist_id', $pricelists_select, Input::old('pricelist_id'), array('class' => 'form-control')) }}
            </div>
            <div class="form-group">
                {{ Form::label('payment_status', 'Payment Status') }}
                {{ Form::select('payment_status', $payment_statuses, Input::old('payment_status'), array('class' => 'form-control')) }}
            </div>
            <div class="form-group">
                {{ Form::label('paid', 'Paid') }}
                <div class="input-group">
                    <span class="input-group-addon">$</span>
                    {{ Form::text('paid', Input::old('paid'), array('class' => 'form-control', 'required')) }}
                </div>
            </div>
            <div class="form-group">
                {{ Form::label('transaction_id', 'Transaction ID') }}
                {{ Form::text('transaction_id', Input::old('transaction_id'), array('class' => 'form-control', 'required')) }}
            </div>
        </div>
    </div>
    {{ Form::close() }}
@stop

@section('footer')
{{ HTML::script('packages/redooor/redminportal/assets/js/typeahead.bundle.js') }}
<script>
    (function ($){
        $(function() {
            var substringMatcher = function(strs) {
                return function findMatches(q, cb) {
                    var matches, substrRegex;

                    // an array that will be populated with substring matches
                    matches = [];

                    // regex used to determine if a string contains the substring `q`
                    substrRegex = new RegExp(q, 'i');

                    // iterate through the pool of strings and for any string that
                    // contains the substring `q`, add it to the `matches` array
                    $.each(strs, function(i, str) {
                        if (substrRegex.test(str)) {
                            // the typeahead jQuery plugin expects suggestions to a
                            // JavaScript object, refer to typeahead docs for more info
                            matches.push({ value: str });
                        }
                    });

                    cb(matches);
                };
            };

            $.get( "{{ URL::to('admin/purchases/emails') }}", function( data ) {
                var emails = data;
                
                $('.typeahead').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 1
                },
                {
                    name: 'emails',
                    displayKey: 'value',
                    source: substringMatcher(emails)
                });
            });
        });
    })(window.jQuery);
</script>
@stop
