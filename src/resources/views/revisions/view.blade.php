@extends('redminportal::layouts.bare')

@section('content')
    
    @include('redminportal::partials.errors')

    @if ($model->revisions()->count() > 0)
    <table class="table table-condensed table-striped">
        <thead>
            <tr>
                <th>{{ trans('redminportal::forms.revision_edited_by') }}</th>
                <th>{{ trans('redminportal::forms.revision_edited_on') }}</th>
                <th>{{ trans('redminportal::forms.revision_edited_field') }}</th>
                <th>{{ trans('redminportal::forms.revision_edited_from') }}</th>
                <th>{{ trans('redminportal::forms.revision_edited_to') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($model->revisions as $revision)
            <tr>
                <td class="revision-user">{{ $revision->user->first_name }} {{ $revision->user->last_name }}</td>
                <td class="revision-date">{{ $revision->created_at }}</td>
                <td class="revision-field">{{ $revision->showAttribute() }}</td>
                <td class="revision-old-value">{{ $revision->old_value }}</td>
                <td class="revision-new-value">{{ $revision->new_value }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="alert alert-info">{{ trans('redminportal::messages.no_revision_found') }}</div>
    @endif
@stop
