{{--
    Input form template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.form-input', [
        'wrapper_classes' => 'optional classes',
        'label' => 'optional label',
        'label_classes' => 'optional classes for label',
        'input_name' => 'mandatory-name',
        'input_id' => 'optional-id defaults to input_id if not set',
        'input_value' => 'optionally set the value of the input',
        'input_options' => optional array_of_options('type' => 'text', 'step' => '0.1', 'placeholder' => 'text', etc),
        'help_text' => 'optional help text'
    ])
    --------------------------------
    NOTE: if value_as_key is set, then matching value will be marked as selected instead.
--}}
<div class="form-group {{ $wrapper_classes ?? '' }}">
    @if (isset($label))
    <label class="{{ $label_classes ?? '' }}" for="{{ $input_name }}">{{ $label }}</label>
    @endif
    <input class="form-control" name="{{ $input_name }}" id="{{ $input_id ?? $input_name }}"
        @if (isset($input_options))
            @foreach ($input_options as $input_option_key => $input_option_value)
                {{ $input_option_key }}="{{ $input_option_value }}"
            @endforeach
        @endif
        @if (isset($input_value) and $input_value != null)
            value="{{ $input_value }}"
        @endif
    >{{-- Input end --}}
    @if (isset($help_text))
    <p class="help-block">{{ $help_text }}</p>
    @endif
</div>
