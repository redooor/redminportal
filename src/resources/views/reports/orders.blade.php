<html>
    @if (count($data))
    <table>
        <tr>
            <th>User</th>
            <th>{{ Lang::get('redminportal::forms.email') }}</th>
            <th>Paid</th>
            <th>Payment Status</th>
            <th>Transaction ID</th>
            <th>Purchased on</th>
            <th>Product</th>
            <th>SKU</th>
        </tr>
        @foreach($data as $order)
            @foreach($order->products as $product)
                <tr>
                    @if ($order->user != null)
                    <td>{{ str_replace(',', '.', $order->user->first_name) }} {{ str_replace(',', '.', $order->user->last_name) }}</td>
                    <td>{{ str_replace(',', '.', $order->user->email) }}</td>
                    @else
                    <td>User deleted</td>
                    <td>User deleted</td>
                    @endif
                    <td>{{ Redooor\Redminportal\App\Helpers\RHelper::formatCurrency($order->paid, Lang::get('redminportal::currency.currency')) }}</td>
                    <td>{{ str_replace(',', '.', $order->payment_status) }}</td>
                    <td>{{ str_replace(',', '.', $order->transaction_id) }}</td>
                    <td>{{ str_replace(',', '.', $order->created_at) }}</td>
                    <td>{{ str_replace(',', '.', $product->name) }}</td>
                    <td>{{ str_replace(',', '.', $product->sku) }}</td>
                </tr>
            @endforeach
        @endforeach
    </table>
    @endif
</html>
