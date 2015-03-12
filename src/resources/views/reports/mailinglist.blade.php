<html>
    @if (count($data))
    <table>
        <tr>
            <th>Email</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Active</th>
            <th>Updated</th>
        </tr>
        @foreach ($data as $item)
            <tr>
                <td>{{ str_replace(',', '.', $item->email) }}</td>
                <td>{{ str_replace(',', '.', $item->first_name) }}</td>
                <td>{{ str_replace(',', '.', $item->last_name) }}</td>
                <td>@if($item->active){{ 'Yes' }}@else{{ 'No' }}@endif</td>
                <td>{{ str_replace(',', '.', $item->updated_at) }}</td>
            </tr>
        @endforeach
    </table>
    @endif
</html>
