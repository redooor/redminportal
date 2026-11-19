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
            @if (isset($purchase->pricelist))
            <tr>
                @if ($purchase->user != null)
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($purchase->user->first_name) }} {{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($purchase->user->last_name) }}</td>
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($purchase->user->email) }}</td>
                @else
                <td>User deleted</td>
                <td>User deleted</td>
                @endif
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($purchase->pricelist->module->name) }}</td>
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($purchase->pricelist->membership->name) }}</td>
                <td>{{ Redooor\Redminportal\App\Helpers\RHelper::formatCurrency($purchase->paid, Lang::get('redminportal::currency.currency')) }}</td>
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($purchase->payment_status) }}</td>
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($purchase->transaction_id) }}</td>
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($purchase->created_at) }}</td>
            </tr>
            @endif
        @endforeach
    </table>
    @endif
</html>
