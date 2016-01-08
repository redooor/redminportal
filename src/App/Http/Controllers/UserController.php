<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Lang;
use Auth;
use Redooor\Redminportal\App\Models\User;
use Redooor\Redminportal\App\Models\Group;

class UserController extends Controller
{
    private $perpage;
    
    public function __construct()
    {
        $this->perpage = config('redminportal::pagination.size');
    }
    
    public function getIndex()
    {
        $sortBy = 'email';
        $orderBy = 'asc';
        
        $users = User::orderBy($sortBy, $orderBy)->paginate($this->perpage);
        
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
                Lang::get('redminportal::messages.user_error_user_not_found')
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
            'email'         => 'required|unique:users,email,' . $sid
        );
        
        if (isset($sid)) {
            $rules['password'] = 'confirmed|min:6';
        } else {
            $rules['password'] = 'required|confirmed|min:6';
        }

        $validation = \Validator::make(\Input::all(), $rules);
        
        $path = (isset($sid) ? 'admin/users/edit/' . $sid : 'admin/users/create');
        
        if ($validation->fails()) {
            return redirect($path)->withErrors($validation)->withInput();
        }

        $first_name    = \Input::get('first_name');
        $last_name    = \Input::get('last_name');
        $email         = \Input::get('email');
        $password     = \Input::get('password');
        $role         = \Input::get('role');
        $activated     = (\Input::get('activated') == '' ? false : true);
        
        // Check if this is logged in user, prevent deactivate
        if (isset($sid)) {
            $this_user = Auth::user();
            if ($this_user->id == $sid && $activated == false) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'deactivateError',
                    Lang::get('redminportal::messages.user_error_deactivate_own_account')
                );
                return redirect($path)->withErrors($errors)->withInput();
            }
        }
        
        $user = (isset($sid) ? User::find($sid) : new User);
        
        if ($user == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'createError',
                Lang::get('redminportal::messages.user_error_create_unknown')
            );
            return redirect('/admin/users')->withErrors($errors);
        }
        
        // Save or Update
        $user->email = $email;
        if ($password != '') {
            $user->password = \Hash::make($password);
        }
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->activated = $activated;
        
        if (! $user->save()) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'saveError',
                Lang::get('redminportal::messages.user_error_update_unknown')
            );
            return redirect($path)->withErrors($errors)->withInput();
        }
        
        // Find user's group
        $old_group = $user->groups()->first();
        $new_group = Group::find($role);

        if ($new_group == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'groupError',
                Lang::get('redminportal::messages.user_error_group_not_found')
            );
            return redirect($path)->withErrors($errors)->withInput();
        }

        // Assign the group to the user
        if ($old_group == null) {
            $user->groups()->save($new_group);
        } elseif ($old_group->id != $new_group->id) {
            $user->groups()->detach();
            $user->groups()->save($new_group);
        }

        return redirect('admin/users');
    }

    public function getDelete($sid)
    {
        $this_user = Auth::user();
        if ($this_user->id == $sid) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'deleteError',
                Lang::get('redminportal::messages.user_error_delete_own_account')
            );
            return redirect('/admin/users')->withErrors($errors);
        }
        
        $user = User::find($sid);
        
        if ($user == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'deleteError',
                Lang::get('redminportal::messages.user_error_user_not_found')
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
                Lang::get('redminportal::messages.user_error_user_not_found')
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
        $this_user = Auth::user();
        if ($this_user->id == $sid) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                Lang::get('redminportal::messages.user_error_deactivate_own_account')
            );
            return redirect('/admin/users')->withErrors($errors);
        }
        
        $user = User::find($sid);
        
        if ($user == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                Lang::get('redminportal::messages.user_error_user_not_found')
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
        
        if ($sortBy == 'group') {
            $users = User::LeftJoin('users_groups', 'users_groups.user_id', '=', 'users.id')
                ->LeftJoin('groups', 'groups.id', '=', 'users_groups.group_id')
                ->select('users.*', 'groups.name')
                ->orderBy('groups.name', $orderBy)
                ->paginate($this->perpage);
        } else {
            $users = User::orderBy($sortBy, $orderBy)->paginate($this->perpage);
        }
        
        $data = array(
            'sortBy' => $sortBy,
            'orderBy' => $orderBy,
            'users' => $users
        );
        
        return view('redminportal::users/view', $data);
    }
    
    public function postSearch()
    {
        $pattern = '/^[a-zA-Z0-9 -:\@\.]+$/';
        
        $rules = array(
            'search' => 'required|regex:' . $pattern
        );

        $validation = \Validator::make(\Input::all(), $rules);

        if( $validation->fails() )
        {
            return redirect('admin/users')->withErrors($validation)->withInput();
        }
        
        $search = trim(\Input::get('search'));
        
        // Check special characters
        $errors = $this->checkSpecialChar($search);
        if ($errors) {
            return redirect('admin/users')->withErrors($errors)->withInput();
        }
        
        // Check if the search is by group
        if (preg_match("/^group:/i", $search)) {
            $search = preg_replace("/^group:/i", "", $search);
            return redirect('admin/users/group/' . $search);
        }

        // Else perform normal search
        return redirect('admin/users/search/' . $search);
    }

    public function getSearch($search)
    {
        $pattern = '/^[a-zA-Z0-9 -@.]+$/';
        
        $rules = array(
            'search' => 'required|regex:' . $pattern
        );

        $inputs = array(
            'search' => $search
        );

        $validation = \Validator::make($inputs, $rules);

        if( $validation->fails() )
        {
            return redirect('admin/users')->withErrors($validation)->with('search', $search);
        }
        
        // Check special characters
        $errors = $this->checkSpecialChar($search);
        if ($errors) {
            return redirect('admin/users')->withErrors($errors);
        }
        
        $sortBy = 'email';
        $orderBy = 'asc';

        $users = User::where('first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('last_name', 'LIKE', '%' . $search . '%')
            ->orWhere('email', 'LIKE', '%' . $search . '%')
            ->orderBy($sortBy, $orderBy)
            ->paginate($this->perpage);
        
        $data = array(
            'sortBy' => $sortBy,
            'orderBy' => $orderBy,
            'users' => $users,
            'search' => $search
        );

        return view('redminportal::users/view', $data);
    }
    
    public function getGroup($search)
    {
        $pattern = '/^[a-zA-Z0-9 -]+$/';
        
        $rules = array(
            'search' => 'required|regex:' . $pattern
        );

        $inputs = array(
            'search' => $search
        );

        $validation = \Validator::make($inputs, $rules);

        if( $validation->fails() )
        {
            return redirect('admin/users')->withErrors($validation)->with('search', $search);
        }
        
        // Check special characters
        $errors = $this->checkSpecialChar($search);
        if ($errors) {
            return redirect('admin/users')->withErrors($errors);
        }
        
        $sortBy = 'email';
        $orderBy = 'asc';

        $users = User::join('users_groups', 'users_groups.user_id', '=', 'users.id')
            ->join('groups', 'groups.id', '=', 'users_groups.group_id')
            ->where('groups.name', 'LIKE', $search)
            ->select('users.*')
            ->orderBy($sortBy, $orderBy)
            ->paginate($this->perpage);
        
        $data = array(
            'sortBy' => $sortBy,
            'orderBy' => $orderBy,
            'users' => $users,
            'search' => 'group:' . $search
        );
        
        return view('redminportal::users/view', $data);
    }
    
    /*
     * Returns error message if found special characters
     * return null if no special characters found
     * @return MessageBag if special character found, null if not found
     */
    private function checkSpecialChar($search)
    {
        if (preg_match("/[%$&*]/i", $search)) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'stringError',
                Lang::get('redminportal::messages.error_remove_special_characters')
            );
            return $errors;
        }
        
        return null;
    }
}
