<html>
    @if (count($data))
    <table>
        <tr>
            <th>User</th>
            <th>Email</th>
            <th>Module Name</th>
            <th>Membership</th>
            <th>Paid</th>
            <th>Payment Status</th>
            <th>Transaction ID</th>
            <th>Purchased on</th>
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
                <td>{{ Rhelper::formatCurrency($purchase->paid, Lang::get('redminportal::currency.currency')) }}</td>
                <td>{{ str_replace(',', '.', $purchase->payment_status) }}</td>
                <td>{{ str_replace(',', '.', $purchase->transaction_id) }}</td>
                <td>{{ str_replace(',', '.', $purchase->created_at) }}</td>
            </tr>
        @endforeach
    </table>
    @endif
</html>
