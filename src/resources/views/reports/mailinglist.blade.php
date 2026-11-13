<html>
    @if (count($data))
    <table>
        <tr>
            <th>{{ Lang::get('redminportal::forms.email') }}</th>
            <th>{{ Lang::get('redminportal::forms.first_name') }}</th>
            <th>{{ Lang::get('redminportal::forms.last_name') }}</th>
            <th>{{ Lang::get('redminportal::forms.active') }}</th>
            <th>{{ Lang::get('redminportal::forms.updated') }}</th>
        </tr>
        @foreach ($data as $item)
            <tr>
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($item->email) }}</td>
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($item->first_name) }}</td>
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($item->last_name) }}</td>
                <td>@if($item->active){{ 'Yes' }}@else{{ 'No' }}@endif</td>
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($item->updated_at) }}</td>
            </tr>
        @endforeach
    </table>
    @endif
</html>
