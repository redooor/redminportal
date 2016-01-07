{{--
    Select form template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.form-select-category', [
        'select_category_title' => 'optional title, defaults to Category',
        'select_category_selected_name' => 'selected hidden form name',
        'select_category_selected_id' => 'selected category ID, defaults to 0',
        'select_category_categories' => 'list of categories',
        'select_category_footnote' => 'optional footnote',
        'select_category_default_text' => 'optional default text, defaults to Category',
        'select_category_required_field' => 'optional bool, defaults to false'
    ])
    --------------------------------
--}}
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="panel-title">{{ $select_category_title or trans('redminportal::forms.category') }}</div>
    </div>
    <div class="panel-body">
        <input type="hidden" 
               id="{{ $select_category_selected_name }}" 
               name="{{ $select_category_selected_name }}" 
               value="{{ $select_category_selected_id or '0' }}">
        <ul class="redmin-hierarchy">
        @if (!isset($select_category_required_field) or !$select_category_required_field)
            <li>
                <a href="0"><span class='glyphicon glyphicon-chevron-right'></span> 
                    {{ $select_category_default_text or trans('redminportal::forms.no_category') }}</a>
            </li>
        @endif
        @foreach ($select_category_categories as $item)
            <li>{!! $item->printCategory() !!}</li>
        @endforeach
        </ul>
    </div>
    @if (isset($select_category_footnote))
    <div class="panel-footer">
        <i><small>{{ $select_category_footnote }}</small></i>
    </div>
    @endif
</div>
{{-- Enqueue script to footer --}}
@section('footer')
    @parent
    <script>
        !function ($) {
            $(function(){
                var hidden_category_form = $('#{{ $select_category_selected_name }}');
                // On load, check if previous category exists for error message
                function checkCategory() {
                    $selected_val = hidden_category_form.val();
                    if ($selected_val == '') {
                        // Initialize No Parent
                        hidden_category_form.val(0);
                        $selected_val = '0';
                    }
                    $('.redmin-hierarchy a').each(function() {
                        if ($(this).attr('href') == $selected_val) {
                            $(this).addClass('active');
                        }
                    });
                }
                checkCategory();
                // Change selected category
                $(document).on('click', '.redmin-hierarchy a', function(e) {
                    e.preventDefault();
                    $selected = $(this).attr('href');
                    hidden_category_form.val($selected);
                    $('.redmin-hierarchy a.active').removeClass('active');
                    $(this).addClass('active');
                });
            })
        }(window.jQuery);
    </script>
@stop