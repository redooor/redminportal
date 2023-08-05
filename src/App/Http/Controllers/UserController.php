<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Auth;
use Hash;
use Illuminate\Support\Facades\Input;
use Lang;
use Validator;
use Illuminate\Support\MessageBag;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\SearcherController;
use Redooor\Redminportal\App\Http\Traits\PermissibleController;
use Redooor\Redminportal\App\Models\User;
use Redooor\Redminportal\App\Models\Group;

class UserController extends Controller
{
    use SorterController, PermissibleController, SearcherController;
    
    public function __construct(User $model)
    {
        $this->model = $model;
        $this->sortBy = 'email';
        $this->orderBy = 'asc';
        $this->perpage = config('redminportal::pagination.size');
        $this->pageView = 'redminportal::users.view';
        $this->pageRoute = 'admin/users';
        
        // For sorting
        $this->query = $this->model
            ->LeftJoin('users_groups', 'users_groups.user_id', '=', 'users.id')
            ->LeftJoin('groups', 'groups.id', '=', 'users_groups.group_id')
            ->select('users.*', 'groups.name')
            ->groupBy('email');
        
        // For searching
        $this->searchable_fields = [
            'all' => 'Search all',
            'email' => 'Email',
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'name' => 'Group'
        ];
        
        // Default data for sharing
        $this->data = [
            'sortBy' => $this->sortBy,
            'orderBy' => $this->orderBy,
            'searchable_fields' => $this->searchable_fields
        ];
    }
    
    public function getIndex()
    {
        $models = User::orderBy($this->sortBy, $this->orderBy)->paginate($this->perpage);
        
        $data = array_merge($this->data, [
            'models' => $models
        ]);

        return view('redminportal::users/view', $data);
    }

    public function getCreate()
    {
        $roles = Group::orderBy('name')->pluck('name', 'id');
        
        return view('redminportal::users/create')->with('roles', $roles);
    }
    
    public function getEdit($sid)
    {
        $user = User::find($sid);
        
        if ($user == null) {
            $errors = new MessageBag;
            $errors->add(
                'editError',
                Lang::get('redminportal::messages.user_error_user_not_found')
            );
            return redirect('/admin/users')->withErrors($errors);
        }
        
        $roles = Group::orderBy('name')->pluck('name', 'id');
        
        $groups = [];
        foreach ($user->groups as $group) {
            $groups[$group->id] = $group->id;
        }
        
        $permission_inherit = [];
        $permission_allow = [];
        $permission_deny = [];
        
        foreach ($user->permissions() as $key => $value) {
            if ($value < 0) {
                $permission_deny[$key] = $key;
            } elseif ($value > 0) {
                $permission_allow[$key] = $key;
            } else {
                $permission_inherit[$key] = $key;
            }
        }
        
        $data = array(
            'roles' => $roles,
            'user' => $user,
            'groups' => $groups,
            'permission_inherit' => implode(',', $permission_inherit),
            'permission_allow' => implode(',', $permission_allow),
            'permission_deny' => implode(',', $permission_deny)
        );
        
        return view('redminportal::users/edit', $data);
    }

    public function postStore()
    {
        $sid = Input::get('id');
        $errors = new MessageBag;
        
        $rules = array(
            'first_name'    => 'required',
            'last_name'     => 'required',
            'role'          => 'required',
            'email'         => 'required|unique:users,email,' . $sid,
            'permission-inherit' => 'regex:/^[a-z,0-9._\-?]+$/i',
            'permission-allow'   => 'regex:/^[a-z,0-9._\-?]+$/i',
            'permission-deny'    => 'regex:/^[a-z,0-9._\-?]+$/i'
        );
        
        // Get activated input first, for checking if user is deactivating own account
        $activated = (Input::get('activated') == '' ? false : true);
        
        if (isset($sid)) {
            $rules['password'] = 'confirmed|min:6';
            $path ='admin/users/edit/' . $sid;
            $user = User::find($sid);
            
            // Check if this is logged in user, prevent deactivate
            if (Auth::user()->id == $sid && $activated == false) {
                $errors->add(
                    'deactivateError',
                    Lang::get('redminportal::messages.user_error_deactivate_own_account')
                );
                return redirect($path)->withErrors($errors)->withInput();
            }
        } else {
            $rules['password'] = 'required|confirmed|min:6';
            $path = 'admin/users/create';
            $user = new User;
        }
        
        $messages = array(
            'permission-inherit.regex' => 'The permission inherit format is invalid. Try using the Permission Builder.',
            'permission-allow.regex' => 'The permission allow format is invalid. Try using the Permission Builder.',
            'permission-deny.regex' => 'The permission deny format is invalid. Try using the Permission Builder.'
        );

        $validation = Validator::make(Input::all(), $rules, $messages);
        
        if ($validation->fails()) {
            return redirect($path)->withErrors($validation)->withInput();
        }
        
        // If user can't be created or found
        if ($user == null) {
            $errors->add(
                'createError',
                Lang::get('redminportal::messages.user_error_create_unknown')
            );
            return redirect('/admin/users')->withErrors($errors);
        }
        
        $permissions = $this->populatePermission(
            Input::get('permission-inherit'),
            Input::get('permission-allow'),
            Input::get('permission-deny')
        );
        
        // Save or Update
        $user->email = Input::get('email');
        
        $password = Input::get('password');
        if ($password != '') {
            $user->password = Hash::make($password);
        }
        
        $user->first_name = Input::get('first_name');
        $user->last_name = Input::get('last_name');
        $user->activated = $activated;
        $user->permissions = json_encode($permissions);
        
        if (! $user->save()) {
            $errors->add(
                'saveError',
                Lang::get('redminportal::messages.user_error_update_unknown')
            );
            return redirect($path)->withErrors($errors)->withInput();
        }
        
        // Assign group(s) to user
        // Return error message if group has error
        if (! $user->addGroup(Input::get('role'))) {
            $errors->add(
                'groupError',
                Lang::get('redminportal::messages.user_error_group_not_found')
            );
            return redirect($path)->withErrors($errors)->withInput();
        }

        return redirect('admin/users');
    }
    
    public function getDelete($sid)
    {
        $this_user = Auth::user();
        if ($this_user->id == $sid) {
            $errors = new MessageBag;
            $errors->add(
                'deleteError',
                Lang::get('redminportal::messages.user_error_delete_own_account')
            );
            return redirect()->back()->withErrors($errors);
        }
        
        $user = User::find($sid);
        
        if ($user == null) {
            $errors = new MessageBag;
            $errors->add(
                'deleteError',
                Lang::get('redminportal::messages.user_error_user_not_found')
            );
            return redirect()->back()->withErrors($errors);
        }
        
        // Delete the user
        $user->delete();
        
        return redirect()->back();
    }

    public function getActivate($sid)
    {
        $user = User::find($sid);
        
        if ($user == null) {
            $errors = new MessageBag;
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
            $errors = new MessageBag;
            $errors->add(
                'editError',
                Lang::get('redminportal::messages.user_error_deactivate_own_account')
            );
            return redirect('/admin/users')->withErrors($errors);
        }
        
        $user = User::find($sid);
        
        if ($user == null) {
            $errors = new MessageBag;
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
}
