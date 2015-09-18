<?php
/*
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
        'input_options' => $array_of_options('type' => 'text', 'step' => '0.1', 'placeholder' => 'text', etc),
        'help_text' => 'optional help text'
    ])
    --------------------------------
    NOTE: if value_as_key is set, then matching value will be marked as selected instead.
*/
?>
<div class="form-group {{ $wrapper_classes or '' }}">
    @if (isset($label))
    <label class="{{ $label_classes or '' }}" for="{{ $input_name }}">{{ $label }}</label>
    @endif
    <input class="form-control" name="{{ $input_name }}" id="{{ $input_id or $input_name }}"
    <?php
        foreach ($input_options as $input_option_key => $input_option_value):
            echo "$input_option_key='$input_option_value' ";
        endforeach;
        if (isset($input_value) and $input_value != null):
            echo "value='$input_value'";
        endif;
    ?>
    ><!-- Input end -->
    @if (isset($help_text))
    <p class="help-block">{{ $help_text }}</p>
    @endif
</div>
