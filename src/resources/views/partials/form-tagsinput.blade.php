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
<div class="card">
    <div class="card-header">
        <div class="card-title mb-0">{{ $title ?? trans('redminportal::forms.tags') }}</div>
    </div>
    <div class="card-body">
        {!! Redminportal::form()->inputer('tags', $value) !!}
    </div>
    <div class="card-footer">
        <i><small>{{ $footnote ?? trans('redminportal::forms.tags_footnote') }}</small></i>
    </div>
</div>
{{-- Enqueue script to footer --}}
@section('footer')
    @parent
    @include('redminportal::plugins/tagsinput')
@stop