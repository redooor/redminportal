<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Support\MessageBag;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\PermissibleController;
use Redooor\Redminportal\App\Models\Group;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    use SorterController, PermissibleController;
    
    public function __construct(Group $model)
    {
        $this->model = $model;
        $this->sortBy = 'name';
        $this->orderBy = 'asc';
        $this->perpage = config('redminportal::pagination.size');
        $this->pageView = 'redminportal::groups.view';
        $this->pageRoute = 'admin/groups';
        
        // For sorting
        $this->query = $this->model;
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
        
        if (isset($sid)) {
            $redirect_url = 'admin/groups/edit/' . $sid;
            $group = Group::find($sid);
        } else {
            $redirect_url = 'admin/groups/create';
            $group = new Group;
        }
        
        if ($validation->fails()) {
            return redirect($redirect_url)->withErrors($validation)->withInput();
        }
        
        $permissions = $this->populatePermission(
            Input::get('permission-inherit'),
            Input::get('permission-allow'),
            Input::get('permission-deny')
        );
        
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
            $group->name = Input::get('name');
            $group->permissions = json_encode($permissions);
            $group->save();
        } catch (\Exception $exp) {
            // There was a problem saving it
            $errors = new MessageBag;
            $errors->add(
                'editError',
                trans('redminportal::messages.group_error_edit_cannot_save')
            );
            return redirect($redirect_url)->withErrors($errors)->withInput();
        }
        
        return redirect('admin/groups');
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
