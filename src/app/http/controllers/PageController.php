<?php namespace Redooor\Redminportal\App\Http\Controllers;

class PageController extends Controller {
    
	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		return view('redminportal::pages.test');
	}

}
