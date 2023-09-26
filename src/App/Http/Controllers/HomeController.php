<?php namespace Redooor\Redminportal\App\Http\Controllers;

class HomeController extends Controller
{
    /**
     * Show the application welcome screen to the user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function home()
    {
        return view('redminportal::pages.home');
    }
}
