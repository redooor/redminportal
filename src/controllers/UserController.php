<?php namespace Redooor\Redminportal;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Cartalyst\Sentry\Facades\Laravel\Sentry;

class UserController extends BaseController {

    protected $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

	public function getIndex()
	{
		$users = Sentry::getUserProvider()->findAll();

		foreach($users as $user)
		{
			$user->permissions = $user->getMergedPermissions();
		}

		return View::make('redminportal::users/view')->with('users', $users);
	}

	public function getCreate()
	{
		$groups = Sentry::getGroupProvider()->findAll();
		$roles = array();

		foreach ($groups as $group) {
			$roles[$group->id] = $group->name;
		}

		return View::make('redminportal::users/create')->with('roles', $roles);
	}

	public function postStore()
	{
	    $id = Input::get('id');

	    /*
         * Validate
         */
        $rules = array(
            'first_name'    => 'required',
            'last_name'     => 'required',
            'email'         => 'required',
            'password'      => 'required|confirmed|min:6'
        );

        $validation = Validator::make(Input::all(), $rules);

        if( !$validation->passes() )
        {
            if(isset($id))
            {
                return Redirect::to('admin/users/edit/' . $id)->withErrors($validation)->withInput();
            }
            else
            {
                return Redirect::to('admin/users/create')->withErrors($validation)->withInput();
            }
        }

		$first_name	= Input::get('first_name');
		$last_name	= Input::get('last_name');
		$email 		= Input::get('email');
		$password 	= Input::get('password');
		$role 		= Input::get('role');
		$activated 	= (Input::get('activated') == 'yes' ? 1 : 0);

		try
		{
		    // Create the user
		    $user = Sentry::getUserProvider()->create(array(
		        'email'    		=> $email,
		        'password' 		=> $password,
		        'first_name'	=> $first_name,
		        'last_name'		=> $last_name,
		        'activated'		=> $activated,
		    ));

		    // Find the group using the group id
		    $adminGroup = Sentry::getGroupProvider()->findById($role);

		    // Assign the group to the user
		    $user->addGroup($adminGroup);
		}
		catch (Cartalyst\Sentry\Users\LoginRequiredException $e)
		{
		    return 'Login field is required.';
		}
		catch (Cartalyst\Sentry\Users\PasswordRequiredException $e)
		{
		    return 'Password field is required.';
		}
		catch (Cartalyst\Sentry\Users\UserExistsException $e)
		{
		    return 'User with this login already exists.';
		}
		catch (Cartalyst\Sentry\Groups\GroupNotFoundException $e)
		{
		    return 'Group was not found.';
		}

		return Redirect::to('admin/users');
	}

	public function getDelete($id)
	{
		try
		{
		    // Find the user using the user id
		    $user = Sentry::getUserProvider()->findById($id);

		    // Delete the user
		    $user->delete();
		}
		catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
		    return 'User was not found.';
		}

		return Redirect::to('admin/users');
	}

    public function getActivate($id)
    {
        try
        {
            // Find the user using the user id
            $user = Sentry::getUserProvider()->findById($id);

            // Activate the user
            $user->activated = true;
            $user->save();
        }
        catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
        {
            return 'User was not found.';
        }

        return Redirect::to('admin/users');
    }

    public function getDeactivate($id)
    {
        try
        {
            // Find the user using the user id
            $user = Sentry::getUserProvider()->findById($id);

            // Activate the user
            $user->activated = false;
            $user->save();
        }
        catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
        {
            return 'User was not found.';
        }

        return Redirect::to('admin/users');
    }

}
