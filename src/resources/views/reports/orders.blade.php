<html>
    @if (count($data))
    <table>
        <tr>
            <th>{{ Lang::get('redminportal::forms.user') }}</th>
            <th>{{ Lang::get('redminportal::forms.email') }}</th>
            <th>{{ Lang::get('redminportal::forms.total_price') }}</th>
            <th>{{ Lang::get('redminportal::forms.total_discount') }}</th>
            <th>{{ Lang::get('redminportal::forms.paid') }}</th>
            <th>{{ Lang::get('redminportal::forms.payment_status') }}</th>
            <th>{{ Lang::get('redminportal::forms.transaction_id') }}</th>
            <th>{{ Lang::get('redminportal::forms.ordered_on') }}</th>
            <th>{{ Lang::get('redminportal::forms.coupons') }}</th>
            <th>{{ Lang::get('redminportal::forms.items') }}</th>
        </tr>
        @foreach($data as $order)
            <tr>
                @if ($order->user != null)
                <td>{{ str_replace(',', '.', $order->user->first_name) }} {{ str_replace(',', '.', $order->user->last_name) }}</td>
                <td>{{ str_replace(',', '.', $order->user->email) }}</td>
                @else
                <td>User deleted</td>
                <td>User deleted</td>
                @endif
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::formatCurrency($order->getTotalprice(), Lang::get('redminportal::currency.currency')) }}</td>
                @if (count($order->getDiscounts()) > 0)
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::formatCurrency(collect($order->getDiscounts())->sum('value'), Lang::get('redminportal::currency.currency')) }}</td>
                @else
                <td></td>
                @endif
                <td>{{ Redooor\Redminportal\App\Helpers\RHelper::formatCurrency($order->paid, Lang::get('redminportal::currency.currency')) }}</td>
                <td>{{ str_replace(',', '.', $order->payment_status) }}</td>
                <td>{{ str_replace(',', '.', $order->transaction_id) }}</td>
                <td>{{ str_replace(',', '.', $order->created_at) }}</td>
                <td>
                    @foreach ($order->coupons as $coupon)
                        {{ str_replace(',', '.', $coupon->code) }}:{{ $coupon->amount }}@if($coupon->is_percent){{ "%" }}@else{{ "(fixed)" }}@endif{{ ", " }}
                    @endforeach
                </td>
                <td>
                    @foreach($order->products as $product)
                        {{ str_replace(',', '.', $product->name) }} ({{ str_replace(',', '.', $product->sku) }}), 
                    @endforeach
                    @foreach($order->bundles as $bundle)
                        {{ str_replace(',', '.', $bundle->name) }} ({{ str_replace(',', '.', $bundle->sku) }}), 
                    @endforeach
                    @foreach ($order->pricelists as $pricelist)
                        {{ str_replace(',', '.', $pricelist->module->name) }}/{{ str_replace(',', '.', $pricelist->membership->name) }} ({{ str_replace(',', '.', $pricelist->module->sku) }}), 
                    @endforeach
                </td>
            </tr>
        @endforeach
    </table>
    @endif
</html>
