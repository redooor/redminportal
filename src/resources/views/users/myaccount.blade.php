@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.myaccount') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    @if (session('success_message'))
    <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-success">{{ session('success_message') }}</div>
        </div>
    </div>
    @endif

    {!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\MyaccountController@postStore', 'role' => 'form', 'id' => 'account-form')) !!}
    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.myaccount') }} <small>: {{ $user->email }}</small></h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        {!! Form::label('first_name', Lang::get('redminportal::forms.first_name')) !!}
                        {!! Form::text('first_name', $user->first_name, array('class' => 'form-control', 'required')) !!}
                    </div>

                    <div class="mb-3">
                        {!! Form::label('last_name', Lang::get('redminportal::forms.last_name')) !!}
                        {!! Form::text('last_name', $user->last_name, array('class' => 'form-control', 'required')) !!}
                    </div>
                    
                    <div class="mb-3">
                        {!! Form::label('password', Lang::get('redminportal::forms.new_password')) !!}
                        {!! Form::password('password', array('class' => 'form-control')) !!}
                        <p class="form-text">{{ Lang::get('redminportal::messages.leave_password_empty_to_keep_existing_password') }}</p>
                    </div>

                    <div class="mb-3">
                        {!! Form::label('password_confirmation', Lang::get('redminportal::forms.reenter_password')) !!}
                        {!! Form::password('password_confirmation', array('class' => 'form-control')) !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card card-body bg-body-tertiary">
                <div class='form-actions text-end'>
                    <a id="btn-submit" href="#" class="btn btn-primary btn-sm">{{ trans('redminportal::buttons.save') }}</a>
                </div>
            </div>
        </div>
    </div>
    {!! Redminportal::html()->modalWindow(
        'modal_verify_password',
        trans('redminportal::messages.enter_existing_password_to_proceed'),
        Redminportal::form()->inputer('old_password', null, [
            'id' => 'old_password',
            'type' => 'password',
            'help_text' => Lang::get('redminportal::messages.warning_new_password_logout')
        ]),
        '<a href="#" id="btn-proceed" class="btn btn-primary">Proceed</a>'
    ) !!}
	{!! Form::close() !!}
@stop

@section('footer')
<script>
    !function ($) {
        $(function(){
            $('#modal_verify_password').on('shown.bs.modal', function (e) {
                $('#old_password').focus();
            });
            $('#btn-submit').click(function (e) {
                e.preventDefault();
                $('#modal_verify_password').modal('show');
            })
            $('#btn-proceed').click(function (e) {
                e.preventDefault();
                $('#account-form').submit();
            });
            $(document).on("keypress", "#old_password", function (event) {
                if (event.keyCode == 13) {
                    $('#btn-proceed').click();
                }
            });
        })
    }(window.jQuery);
</script>
@stop