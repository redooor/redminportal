<?php namespace Redooor\Redminportal;

class UserController extends BaseController
{
    public function getIndex()
    {
        $sortBy = 'email';
        $orderBy = 'asc';
        
        $users = User::orderBy($sortBy, $orderBy)->paginate(20);

        return \View::make('redminportal::users/view')
            ->with('sortBy', $sortBy)
            ->with('orderBy', $orderBy)
            ->with('users', $users);
    }

    public function getCreate()
    {
        $groups = \Sentry::getGroupProvider()->findAll();
        $roles = array();

        foreach ($groups as $group) {
            $roles[$group->id] = $group->name;
        }

        return \View::make('redminportal::users/create')->with('roles', $roles);
    }
    
    public function getEdit($sid)
    {
        try {
            // Find the user using the user id
            $user = \Sentry::findUserById($sid);
        } catch (\Exception $exp) {
            return \View::make('redminportal::pages/404');
        }
        
        $groups = \Sentry::getGroupProvider()->findAll();
        $roles = array();

        foreach ($groups as $group) {
            $roles[$group->id] = $group->name;
        }
        
        $group = $user->getGroups()->first();
        
        return \View::make('redminportal::users/edit')
            ->with('roles', $roles)
            ->with('user', $user)
            ->with('group', $group);
    }

    public function postStore()
    {
        $sid = \Input::get('id');
        
        $rules = array(
            'first_name'    => 'required',
            'last_name'     => 'required',
            'email'         => 'required'
        );
        
        if (isset($sid)) {
            $rules['password'] = 'confirmed|min:6';
        } else {
            $rules['password'] = 'required|confirmed|min:6';
        }

        $validation = \Validator::make(\Input::all(), $rules);

        if (!$validation->passes()) {
            if (isset($sid)) {
                return \Redirect::to('admin/users/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return \Redirect::to('admin/users/create')->withErrors($validation)->withInput();
            }
        }

        $first_name    = \Input::get('first_name');
        $last_name    = \Input::get('last_name');
        $email         = \Input::get('email');
        $password     = \Input::get('password');
        $role         = \Input::get('role');
        $activated     = (\Input::get('activated') == '' ? false : true);
        
        if (isset($sid)) {
            // Edit existing
            try {
                $user = \Sentry::findUserById($sid);

                $user->email = $email;
                if ($password != '') {
                    $user->password = $password;
                }
                $user->first_name = $first_name;
                $user->last_name = $last_name;
                $user->activated = $activated;
                
                // Find user's group
                $old_group = $user->getGroups()->first();
                $new_group = \Sentry::findGroupById($role);

                // Assign the group to the user
                if ($old_group->id != $new_group->id) {
                    $user->removeGroup($old_group);
                    $user->addGroup($new_group);
                }

                // Update the user
                if (! $user->save()) {
                    $errors = new \Illuminate\Support\MessageBag;
                    $errors->add(
                        'editError',
                        "The user cannot be updated due to some problem. Please try again."
                    );
                    return \Redirect::to('admin/users/edit/' . $sid)->withErrors($errors)->withInput();
                }
            } catch (\Exception $exp) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The user cannot be found because it does not exist or may have been deleted."
                );
                return \Redirect::to('/admin/users')->withErrors($errors);
            }
        } else {
            try {
                // Create the user
                $user = \Sentry::getUserProvider()->create(array(
                    'email'      => $email,
                    'password'   => $password,
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                    'activated'  => $activated,
                ));

                // Find the group using the group id
                $adminGroup = \Sentry::getGroupProvider()->findById($role);

                // Assign the group to the user
                $user->addGroup($adminGroup);

            } catch (\Exception $exp) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The user cannot be created due to some problem. Please try again."
                );
                return \Redirect::to('admin/users/create')->withErrors($errors)->withInput();
            }
        }

        return \Redirect::to('admin/users');
    }

    public function getDelete($sid)
    {
        try {
            // Find the user using the user id
            $user = \Sentry::getUserProvider()->findById($sid);

            // Delete the user
            $user->delete();
            
        } catch (\Exception $exp) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The user cannot be found because it does not exist or may have been deleted."
            );
            return \Redirect::to('/admin/users')->withErrors($errors);
        }

        return \Redirect::to('admin/users');
    }

    public function getActivate($sid)
    {
        try {
            // Find the user using the user id
            $user = \Sentry::getUserProvider()->findById($sid);

            // Activate the user
            $user->activated = true;
            $user->save();
            
        } catch (\Exception $exp) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The user cannot be found because it does not exist or may have been deleted."
            );
            return \Redirect::to('/admin/users')->withErrors($errors);
        }
        return \Redirect::to('admin/users');
    }

    public function getDeactivate($sid)
    {
        try {
            // Find the user using the user id
            $user = \Sentry::getUserProvider()->findById($sid);

            // Activate the user
            $user->activated = false;
            $user->save();
            
        } catch (\Exception $exp) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The user cannot be found because it does not exist or may have been deleted."
            );
            return \Redirect::to('/admin/users')->withErrors($errors);
        }

        return \Redirect::to('admin/users');
    }
    
    public function getSort($sortBy = 'email', $orderBy = 'asc')
    {
        $inputs = array(
            'sortBy' => $sortBy,
            'orderBy' => $orderBy
        );
        
        $rules = array(
            'sortBy'  => 'required|regex:/^[a-zA-Z0-9 _-]*$/',
            'orderBy' => 'required|regex:/^[a-zA-Z0-9 _-]*$/'
        );
        
        $validation = \Validator::make($inputs, $rules);

        if( ! $validation->passes() )
        {
            return \Redirect::to('admin/users')->withErrors($validation);
        }
        
        if ($orderBy != 'asc' && $orderBy != 'desc') {
            $orderBy = 'asc';
        }

        $users = User::orderBy($sortBy, $orderBy)->paginate(20);

        return \View::make('redminportal::users/view')
            ->with('sortBy', $sortBy)
            ->with('orderBy', $orderBy)
            ->with('users', $users);
    }
}
