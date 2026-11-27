<?php namespace Redooor\Redminportal\App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class MailinglistExport implements FromView, WithTitle
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('redminportal::reports/mailinglist', ['data' => $this->data]);
    }

    public function title(): string
    {
        return 'Mailinglist Report';
    }
}
