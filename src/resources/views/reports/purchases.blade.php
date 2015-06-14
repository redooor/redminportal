<html>
    @if (count($data))
    <table>
        <tr>
            <th>User</th>
            <th>{{ Lang::get('redminportal::forms.email') }}</th>
            <th>{{ Lang::get('redminportal::forms.module_name') }}</th>
            <th>{{ Lang::get('redminportal::forms.membership') }}</th>
            <th>{{ Lang::get('redminportal::forms.paid') }}</th>
            <th>{{ Lang::get('redminportal::forms.payment_status') }}</th>
            <th>{{ Lang::get('redminportal::forms.transaction_id') }}</th>
            <th>{{ Lang::get('redminportal::forms.purchased_on') }}</th>
        </tr>
        @foreach($data as $purchase)
            <tr>
                @if ($purchase->user != null)
                <td>{{ str_replace(',', '.', $purchase->user->first_name) }} {{ str_replace(',', '.', $purchase->user->last_name) }}</td>
                <td>{{ str_replace(',', '.', $purchase->user->email) }}</td>
                @else
                <td>User deleted</td>
                <td>User deleted</td>
                @endif
                <td>{{ str_replace(',', '.', $purchase->pricelist->module->name) }}</td>
                <td>{{ str_replace(',', '.', $purchase->pricelist->membership->name) }}</td>
                <td>{{ Redooor\Redminportal\App\Helpers\RHelper::formatCurrency($purchase->paid, Lang::get('redminportal::currency.currency')) }}</td>
                <td>{{ str_replace(',', '.', $purchase->payment_status) }}</td>
                <td>{{ str_replace(',', '.', $purchase->transaction_id) }}</td>
                <td>{{ str_replace(',', '.', $purchase->created_at) }}</td>
            </tr>
        @endforeach
    </table>
    @endif
</html>
