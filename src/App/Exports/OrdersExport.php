<?php namespace Redooor\Redminportal\App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class OrdersExport implements FromView, WithTitle
{
    public function __construct(private $data) {}

    public function view(): View
    {
        return view('redminportal::reports/orders', ['data' => $this->data]);
    }

    public function title(): string
    {
        return 'Orders Report';
    }
}
