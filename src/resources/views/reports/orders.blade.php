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
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($order->user->first_name) }} {{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($order->user->last_name) }}</td>
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($order->user->email) }}</td>
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
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($order->payment_status) }}</td>
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($order->transaction_id) }}</td>
                <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($order->created_at) }}</td>
                <td>
                    @foreach ($order->coupons as $coupon)
                        {{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($coupon->code) }}:{{ $coupon->amount }}@if($coupon->is_percent){{ "%" }}@else{{ "(fixed)" }}@endif{{ ", " }}
                    @endforeach
                </td>
                <td>
                    @foreach ($order->products()->get() as $product)
                        {{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($product->name) }} ({{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($product->sku) }}) Qty: {{ $order->products()->where('name', $product->name)->count() }}, 
                    @endforeach
                    @foreach ($order->bundles()->get() as $bundle)
                        {{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($bundle->name) }} ({{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($bundle->sku) }}) Qty: {{ $order->bundles()->where('name', $bundle->name)->count() }}, 
                    @endforeach
                    @foreach ($order->pricelists as $pricelist)
                        {{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($pricelist->module->name) }}/{{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($pricelist->membership->name) }} ({{ \Redooor\Redminportal\App\Helpers\RHelper::exportCell($pricelist->module->sku) }}), 
                    @endforeach
                </td>
            </tr>
        @endforeach
    </table>
    @endif
</html>
