<?php namespace Redooor\Redminportal\App\Http\Controllers;

class PageController extends Controller {
    
	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function home()
	{
		return view('redminportal::pages.home');
	}

}
