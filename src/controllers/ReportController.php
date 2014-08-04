<?php namespace Redooor\Redminportal;

class ReportController extends BaseController {

    public function getIndex()
    {
        return \View::make('redminportal::pages/404');
    }

    public function postMailinglist()
    {
        $input_start_date = \Input::get('start_date');
        if ($input_start_date == "") {
            $input_start_date = "01/01/1900";
        }
        $start_date = \DateTime::createFromFormat('d/m/Y', $input_start_date);

        $input_end_date = \Input::get('end_date');

        if ($input_end_date == "") {
            $end_date = new \DateTime("NOW");
        } else {
            $end_date = \DateTime::createFromFormat('d/m/Y', $input_end_date);
        }

        $data = Mailinglist::where('updated_at', '>=', $start_date)
            ->where('updated_at', '<=', $end_date)
            ->orderBy('email')
            ->get();

        if (count($data) == 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('downloadError', "There's no data within the dates specified.");
            return \Redirect::to('admin/mailinglists')->withErrors($errors);
        }

        \Excel::create('Redooor_Mailinglist_Report', function($excel) use($data) {

            $excel->sheet('Mailinglist Report', function($sheet) use($data) {

                $sheet->loadView('redminportal::reports/mailinglist')->with('data', $data);

            });

        })->download('csv');
    }

    public function postPurchases()
    {
        $input_start_date = \Input::get('start_date');
        if ($input_start_date == "") {
            $input_start_date = "01/01/1900";
        }
        $start_date = \DateTime::createFromFormat('d/m/Y', $input_start_date);

        $input_end_date = \Input::get('end_date');

        if ($input_end_date == "") {
            $end_date = new \DateTime("NOW");
        } else {
            $end_date = \DateTime::createFromFormat('d/m/Y', $input_end_date);
        }

        $data = UserPricelist::where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->orderBy('created_at', 'desc')
            ->get();

        if (count($data) == 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('downloadError', "There's no data within the dates specified.");
            return \Redirect::to('admin/purchases')->withErrors($errors);
        }

        \Excel::create('Redooor_Purchases_Report', function($excel) use($data) {

            $excel->sheet('Purchases Report', function($sheet) use($data) {

                $sheet->loadView('redminportal::reports/purchases')->with('data', $data);

            });

        })->download('csv');
    }

}
