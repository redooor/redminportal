<?php
/*
    Select form template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.form-select-option', [
        'wrapper_classes' => 'optional classes',
        'label' => 'optional label',
        'label_classes' => 'optional classes for label',
        'select_name' => 'mandatory-name',
        'select_id' => 'optional-id defaults to select_name if not set',
        'select_classes' => 'optional classes for select',
        'select_options' => $array_of_options,
        'value_as_key' => 'true if you want to set options value with $array_of_options value instead of key',
        'selected' => 'matching *key will be marked as selected',
        'help_text' => 'optional help text'
    ])
    --------------------------------
    *NOTE: if value_as_key is set, then matching value will be marked as selected instead.
*/
?>
<div class="form-group {{ $wrapper_classes or '' }}">
    @if (isset($label))
    <label class="{{ $label_classes or '' }}" for="{{ $select_name }}">{{ $label }}</label>
    @endif
    <select class="form-control {{ $select_classes or '' }}" name="{{ $select_name }}" id="{{ $select_id or $select_name }}">
        @foreach ($select_options as $select_option_key => $select_option_value)
            <?php $this_option_value = (isset($value_as_key) ? $select_option_value : $select_option_key); ?>
            @if (isset($selected) and ($selected == $this_option_value))
                <option value="{{ $this_option_value }}" selected>{{ $select_option_value }}</option>
            @else
                <option value="{{ $this_option_value }}">{{ $select_option_value }}</option>
            @endif
        @endforeach
    </select>
    @if (isset($help_text))
    <p class="help-block">{{ $help_text }}</p>
    @endif
</div>
