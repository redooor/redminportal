{{--
    Select form template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.form-input-email-typeahead', [
        'value' => 'optional value, defaults to null',
        'required' => 'optional if field is required, defaults to true'
    ])
    --------------------------------
--}}
<div class="form-group redmin-email-typeahead">
    <label for="email">{{ Lang::get('redminportal::forms.email') }}</label>
    <input class="form-control typeahead" name="email" id="email"
        @if (isset($required) and $required)
            required 
        @endif
        @if (isset($value) and $value != null)
            value="{{ $value }}"
        @endif
    >{{-- Input end --}}
</div>
{{-- Enqueue script to footer --}}
@section('footer')
    @parent
    @include('redminportal::plugins/emailtypeahead')
@stop