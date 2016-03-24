{{--
    Select form template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.form-tagsinput', [
        'value' => 'optional value, defaults to null',
        'title' => 'optional title, defaults to Tags',
        'footnote' => 'optional footnote'
    ])
    --------------------------------
--}}
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="panel-title">{{ $title or trans('redminportal::forms.tags') }}</div>
    </div>
    <div class="panel-body">
        {!! Redminportal::form()->inputer('tags', $value) !!}
    </div>
    <div class="panel-footer">
        <i><small>{{ $footnote or trans('redminportal::forms.tags_footnote') }}</small></i>
    </div>
</div>
{{-- Enqueue script to footer --}}
@section('footer')
    @parent
    @include('redminportal::plugins/tagsinput')
@stop