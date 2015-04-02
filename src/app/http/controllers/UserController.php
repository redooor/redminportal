<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\User;
use Redooor\Redminportal\App\Models\Group;

class UserController extends Controller
{
    public function getIndex()
    {
        $sortBy = 'email';
        $orderBy = 'asc';
        
        $users = User::orderBy($sortBy, $orderBy)->paginate(20);
        
        $data = array(
            'sortBy' => $sortBy,
            'orderBy' => $orderBy,
            'users' => $users
        );

        return view('redminportal::users/view', $data);
    }

    public function getCreate()
    {
        $roles = Group::orderBy('name')->lists('name', 'id');
        
        return view('redminportal::users/create')->with('roles', $roles);
    }
    
    public function getEdit($sid)
    {
        $user = User::find($sid);
        
        if ($user == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The user cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/users')->withErrors($errors);
        }
        
        $roles = Group::orderBy('name')->lists('name', 'id');
        
        $group = $user->groups()->first();
        
        if ($group == null) {
            $group = Group::orderBy('name')->first();
        }
        
        $data = array(
            'roles' => $roles,
            'user' => $user,
            'group' => $group
        );
        
        return view('redminportal::users/edit', $data);
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

        if ($validation->fails()) {
            if (isset($sid)) {
                return redirect('admin/users/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return redirect('admin/users/create')->withErrors($validation)->withInput();
            }
        }

        $first_name    = \Input::get('first_name');
        $last_name    = \Input::get('last_name');
        $email         = \Input::get('email');
        $password     = \Input::get('password');
        $role         = \Input::get('role');
        $activated     = (\Input::get('activated') == '' ? false : true);
        
        if (isset($sid)) {
            $user = User::find($sid);
            
            if ($user == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The user cannot be found because it does not exist or may have been deleted."
                );
                return redirect('/admin/users')->withErrors($errors);
            }
            
            // Edit existing
            $user->email = $email;
            if ($password != '') {
                $user->password = $password;
            }
            $user->first_name = $first_name;
            $user->last_name = $last_name;
            $user->activated = $activated;
            
            // Update the user
            if (! $user->save()) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The user cannot be updated due to some problem. Please try again."
                );
                return redirect('admin/users/edit/' . $sid)->withErrors($errors)->withInput();
            }
            
            // Find user's group
            $old_group = $user->groups()->first();
            $new_group = Group::find($role);
            
            if ($new_group == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The user cannot be updated because the selected group cannot be found. Please try again."
                );
                return redirect('admin/users/edit/' . $sid)->withErrors($errors)->withInput();
            }
            
            // Assign the group to the user
            if ($old_group == null) {
                $user->groups()->save($new_group);
            } elseif ($old_group->id != $new_group->id) {
                $user->groups()->detach();
                $user->groups()->save($new_group);
            }
            
        } else {
            $user = new User;
            $user->email = $email;
            $user->password = $password;
            $user->first_name = $first_name;
            $user->last_name = $last_name;
            $user->activated = $activated;
            
            // Update the user
            if (! $user->save()) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The user cannot be updated due to some problem. Please try again."
                );
                return redirect('admin/users/edit/' . $sid)->withErrors($errors)->withInput();
            }
            
            $new_group = Group::find($role);
            
            if ($new_group == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The user cannot be updated because the selected group cannot be found. Please try again."
                );
                return redirect('admin/users/edit/' . $sid)->withErrors($errors)->withInput();
            }
            
            // Assign new group
            $user->groups()->save($new_group);
        }

        return redirect('admin/users');
    }

    public function getDelete($sid)
    {
        $user = User::find($sid);
        
        if ($user == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The user cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/users')->withErrors($errors);
        }
        
        // Delete the user
        $user->delete();
        
        return redirect()->back();
    }

    public function getActivate($sid)
    {
        $user = User::find($sid);
        
        if ($user == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The user cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/users')->withErrors($errors);
        }
        
        // Activate the user
        $user->activated = true;
        $user->save();
        
        return redirect()->back();
    }

    public function getDeactivate($sid)
    {
        $user = User::find($sid);
        
        if ($user == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The user cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/users')->withErrors($errors);
        }
        
        // Deactivate the user
        $user->activated = false;
        $user->save();
        
        return redirect()->back();
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

        if ($validation->fails()) {
            return redirect('admin/users')->withErrors($validation);
        }
        
        if ($orderBy != 'asc' && $orderBy != 'desc') {
            $orderBy = 'asc';
        }

        $users = User::orderBy($sortBy, $orderBy)->paginate(20);
        
        $data = array(
            'sortBy' => $sortBy,
            'orderBy' => $orderBy,
            'users' => $users
        );
        
        return view('redminportal::users/view', $data);
    }
}
