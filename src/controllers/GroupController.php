<?php namespace Redooor\Redminportal;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Cartalyst\Sentry\Facades\Laravel\Sentry;

class GroupController extends BaseController {

	public function getIndex()
	{
		$groups = Sentry::getGroupProvider()->createModel()->paginate(20);
		return View::make('redminportal::groups/view')->with('groups', $groups);
	}

	public function getCreate()
	{
		return View::make('redminportal::groups/create');
	}

	public function postStore()
	{
		$name 	= Input::get('name');
		$admin 	= (Input::get('admin') == 'yes' ? 1 : 0);
		$user 	= (Input::get('user') == 'yes' ? 1 : 0);

		try
		{
		    // Create the group
		    $group = Sentry::getGroupProvider()->create(array(
		        'name'        => $name,
		        'permissions' => array(
		            'admin' => $admin,
		            'users' => $user,
		        ),
		    ));
		}
		catch (Cartalyst\Sentry\Groups\NameRequiredException $e)
		{
		    return 'Name field is required';
		}
		catch (Cartalyst\Sentry\Groups\GroupExistsException $e)
		{
		    return 'Group already exists';
		}

		return Redirect::to('admin/groups');
	}

}
