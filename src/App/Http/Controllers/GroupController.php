<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Input;
use Validator;
use Illuminate\Support\MessageBag;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Models\Group;

class GroupController extends Controller
{
    protected $model;
    protected $perpage;
    protected $sortBy;
    protected $orderBy;
    
    use SorterController;
    
    public function __construct(Group $model)
    {
        $this->model = $model;
        $this->sortBy = 'name';
        $this->orderBy = 'asc';
        $this->perpage = config('redminportal::pagination.size');
        // For sorting
        $this->query = $this->model;
        $this->sort_success_view = 'redminportal::groups.view';
        $this->sort_fail_redirect = 'admin/groups';
    }
    
    public function getIndex()
    {
        $models = Group::orderBy($this->sortBy, $this->orderBy)->paginate($this->perpage);
        
        $data = [
            'models' => $models,
            'sortBy' => $this->sortBy,
            'orderBy' => $this->orderBy
        ];

        return view('redminportal::groups/view', $data);
    }

    public function getCreate()
    {
        return view('redminportal::groups/create');
    }
    
    public function getEdit($sid)
    {
        $group = Group::find($sid);
        
        if ($group == null) {
            $errors = new MessageBag;
            $errors->add(
                'editError',
                trans('redminportal::messages.group_error_edit_no_group_found')
            );
            return redirect('/admin/groups')->withErrors($errors);
        }
        
        $permission_inherit = [];
        $permission_allow = [];
        $permission_deny = [];
        
        foreach ($group->permissions() as $key => $value) {
            if ($value < 0) {
                $permission_deny[$key] = $key;
            } elseif ($value > 0) {
                $permission_allow[$key] = $key;
            } else {
                $permission_inherit[$key] = $key;
            }
        }
        
        $data = [
            'group' => $group,
            'permission_inherit' => implode(',', $permission_inherit),
            'permission_allow' => implode(',', $permission_allow),
            'permission_deny' => implode(',', $permission_deny)
        ];
        
        return view('redminportal::groups/edit', $data);
    }
    
    public function postStore()
    {
        $sid = Input::get('id');
        
        $rules = array(
            'name' => 'required',
            'permission-inherit' => 'regex:/^[a-z,0-9._\-?]+$/i',
            'permission-allow' => 'regex:/^[a-z,0-9._\-?]+$/i',
            'permission-deny' => 'regex:/^[a-z,0-9._\-?]+$/i'
        );
        
        $messages = array(
            'permission-inherit.regex' => 'The permission inherit format is invalid. Try using the Permission Builder.',
            'permission-allow.regex' => 'The permission allow format is invalid. Try using the Permission Builder.',
            'permission-deny.regex' => 'The permission deny format is invalid. Try using the Permission Builder.'
        );

        $validation = Validator::make(Input::all(), $rules, $messages);
        
        $redirect_url = 'admin/groups/' . (isset($sid) ? 'edit/' . $sid : 'create');
        
        if ($validation->fails()) {
            return redirect($redirect_url)->withErrors($validation)->withInput();
        }
        
        $name = Input::get('name');
        $permission_inherit = Input::get('permission-inherit');
        $permission_allow = Input::get('permission-allow');
        $permission_deny = Input::get('permission-deny');
        
        $permissions = $this->populatePermission($permission_inherit, $permission_allow, $permission_deny);
        
        $group = (isset($sid) ? Group::find($sid) : new Group);
        
        if ($group == null) {
            $errors = new MessageBag;
            $errors->add(
                'editError',
                trans('redminportal::messages.group_error_edit_no_group_found')
            );
            return redirect('admin/groups')->withErrors($errors);
        }
        
        try {
            // Save the group details
            $group->name = $name;
            $group->permissions = json_encode($permissions);
            $group->save();
        } catch (\Exception $exp) {
            $errors = new MessageBag;
            $errors->add(
                'editError',
                trans('redminportal::messages.group_error_edit_cannot_save')
            );
            return redirect($redirect_url)->withErrors($errors)->withInput();
        }
        
        return redirect('admin/groups');
    }
    
    /**
     * Returns an array of permissions based on given inherit, allow and deny list
     * @param array Inherit Permission
     * @param array Allow Permission
     * @param array Deny Permission
     * @return array Permission list
     **/
    private function populatePermission($permission_inherit, $permission_allow, $permission_deny)
    {
        $permissions = [];
        
        // Add Deny permission
        $permissions = $this->retreivePermission($permissions, $permission_deny, -1);
        
        // Add Allow permission
        $permissions = $this->retreivePermission($permissions, $permission_allow, 1);
        
        // Add Allow permission
        $permissions = $this->retreivePermission($permissions, $permission_inherit, 0);
        
        return $permissions;
    }
    
    /**
     * Appends to an array of permissions based on given permission list and level
     * @param array Permission array to be appended
     * @param array Permission list to check through
     * @param int Level of permission
     * @return array Permission
     **/
    private function retreivePermission($permissions, $permission_list, $level)
    {
        $sorted_permissions = explode(',', $permission_list);
        ksort($sorted_permissions);
        
        foreach ($sorted_permissions as $item) {
            $item = trim($item);
            if (! array_key_exists($item, $permissions) && ! empty($item)) {
                $permissions[$item] = $level;
            }
        }
        
        return $permissions;
    }
    
    public function getDelete($sid)
    {
        $group = Group::find($sid);
        
        if ($group == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The group cannot be deleted at this time. It may have already been deleted.");
            return redirect()->back()->withErrors($errors);
        }
        
        if (count($group->users) > 0) {
            // Prevent deletion of this group
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'deleteError',
                "The group cannot be deleted because it is in use. Try moving the users to another group first."
            );
            return redirect()->back()->withErrors($errors);
        } else {
            $group->delete();
        }

        return redirect()->back();
    }
}
