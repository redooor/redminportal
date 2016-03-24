@extends('redminportal::layouts.bare')

@section('head')
<style>
    .revision-limit-view {
        max-width: 200px;
        max-height: 200px;
        overflow: scroll;
        display: block;
    }
</style>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    @if ($model->revisions()->count() > 0)
    <div class="table-responsive">
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
                    <td class="revision-user">{{ $revision->user->first_name or '' }} {{ $revision->user->last_name or '' }}</td>
                    <td class="revision-date">{{ $revision->created_at }}</td>
                    <td class="revision-field">{{ $revision->showAttribute() }}</td>
                    <td class="revision-old-value"><span class="revision-limit-view">{{ $revision->old_value }}</span></td>
                    <td class="revision-new-value"><span class="revision-limit-view">{{ $revision->new_value }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-info">{{ trans('redminportal::messages.no_revision_found') }}</div>
    @endif
@stop
