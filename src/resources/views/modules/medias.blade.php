@if (count($medias) > 0)
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>{{ Lang::get('redminportal::forms.name') }}</th>
                <th>{{ Lang::get('redminportal::forms.description') }}</th>
                @foreach ($memberships as $membership)
                    <th>{{ $membership->name }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($medias as $media)
            <tr>
                <td>{{ $media->name }}</td>
                <td>{{ $media->short_description }}</td>
                @foreach ($memberships as $membership)
                    @if (isset($modMediaMembership[$media->id][$membership->id]))
                    <td>{!! Form::checkbox('media_checkbox[]', $media->id . '_' . $membership->id, true) !!}</td>
                    @else
                    <td>{!! Form::checkbox('media_checkbox[]', $media->id . '_' . $membership->id, false) !!}</td>
                    @endif
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="well">{{ Lang::get('redminportal::messages.no_media_in_this_category') }}</div>
@endif
