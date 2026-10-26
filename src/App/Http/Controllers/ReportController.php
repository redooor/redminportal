<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Mailinglist;
use Redooor\Redminportal\App\Models\UserPricelist;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Exports\MailinglistExport;
use Redooor\Redminportal\App\Exports\PurchasesExport;
use Redooor\Redminportal\App\Exports\OrdersExport;
use Illuminate\Support\Facades\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function getIndex()
    {
        return view('redminportal::pages/404');
    }

    public function postMailinglist()
    {
        $input_start_date = Request::get('start_date');
        if ($input_start_date == "") {
            $input_start_date = "01/01/1900";
        }
        $start_date = \DateTime::createFromFormat('d/m/Y', $input_start_date);

        $input_end_date = Request::get('end_date');

        if ($input_end_date == "") {
            $end_date = new \DateTime("NOW");
        } else {
            $end_date = \DateTime::createFromFormat('d/m/Y', $input_end_date);
        }

        $data = Mailinglist::where('updated_at', '>=', $start_date)
            ->where('updated_at', '<=', $end_date)
            ->orderBy('email')
            ->get();

        if (is_countable($data) && count($data) == 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('downloadError', "There's no data within the dates specified.");
            return redirect('admin/mailinglists')->withErrors($errors);
        }

        return Excel::download(new MailinglistExport($data), 'Redmin_Mailinglist_Report.csv');
    }

    public function postPurchases()
    {
        $input_start_date = Request::get('start_date');
        if ($input_start_date == "") {
            $input_start_date = "01/01/1900";
        }
        $start_date = \DateTime::createFromFormat('d/m/Y', $input_start_date);

        $input_end_date = Request::get('end_date');

        if ($input_end_date == "") {
            $end_date = new \DateTime("NOW");
        } else {
            $end_date = \DateTime::createFromFormat('d/m/Y', $input_end_date);
        }

        $data = UserPricelist::where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->orderBy('created_at', 'desc')
            ->get();

        if (is_countable($data) && count($data) == 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('downloadError', "There's no data within the dates specified.");
            return redirect('admin/purchases')->withErrors($errors);
        }

        return Excel::download(new PurchasesExport($data), 'Redmin_Purchases_Report.csv');
    }

    public function postOrders()
    {
        $input_start_date = Request::get('start_date');
        if ($input_start_date == "") {
            $input_start_date = "01/01/1900";
        }
        $start_date = \DateTime::createFromFormat('d/m/Y', $input_start_date);

        $input_end_date = Request::get('end_date');

        if ($input_end_date == "") {
            $end_date = new \DateTime("NOW");
        } else {
            $end_date = \DateTime::createFromFormat('d/m/Y', $input_end_date);
        }

        $data = Order::where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->orderBy('created_at', 'desc')
            ->get();

        if (is_countable($data) && count($data) == 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('downloadError', "There's no data within the dates specified.");
            return redirect('admin/orders')->withErrors($errors);
        }

        return Excel::download(new OrdersExport($data), 'Redmin_Orders_Report.xlsx');
    }
}
