@if (count($medias) > 0)
    <table class="table table-striped">
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
                    <td><input type="checkbox" name="media_checkbox[]" value="{{ $media->id . '_' . $membership->id }}" @checked(in_array($media->id . '_' . $membership->id, (array) (session()->hasOldInput() ? old('media_checkbox', []) : [$media->id . '_' . $membership->id])))></td>
                    @else
                    <td><input type="checkbox" name="media_checkbox[]" value="{{ $media->id . '_' . $membership->id }}" @checked(in_array($media->id . '_' . $membership->id, (array) (session()->hasOldInput() ? old('media_checkbox', []) : [])))></td>
                    @endif
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="card card-body bg-body-tertiary">{{ Lang::get('redminportal::messages.no_media_in_this_category') }}</div>
@endif
