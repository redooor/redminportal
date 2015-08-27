<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Group;

class GroupController extends Controller
{
    public function getIndex()
    {
        $sortBy = 'name';
        $orderBy = 'asc';
        
        $groups = Group::orderBy($sortBy, $orderBy)->paginate(20);

        return view('redminportal::groups/view')
            ->with('sortBy', $sortBy)
            ->with('orderBy', $orderBy)
            ->with('groups', $groups);
    }

    public function getCreate()
    {
        return view('redminportal::groups/create');
    }
    
    public function getEdit($sid)
    {
        $group = Group::find($sid);
        
        if ($group == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The group cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/groups')->withErrors($errors);
        }
        
        if (isset($group->permissions()->{'admin.view'})) {
            $checkbox_view = $group->permissions()->{'admin.view'};
        } else {
            $checkbox_view = false;
        }
        
        if (isset($group->permissions()->{'admin.create'})) {
            $checkbox_create = $group->permissions()->{'admin.create'};
        } else {
            $checkbox_create = false;
        }
        
        if (isset($group->permissions()->{'admin.delete'})) {
            $checkbox_delete = $group->permissions()->{'admin.delete'};
        } else {
            $checkbox_delete = false;
        }
        
        if (isset($group->permissions()->{'admin.update'})) {
            $checkbox_update = $group->permissions()->{'admin.update'};
        } else {
            $checkbox_update = false;
        }
        
        return view('redminportal::groups/edit')
            ->with('group', $group)
            ->with('checkbox_view', $checkbox_view)
            ->with('checkbox_create', $checkbox_create)
            ->with('checkbox_delete', $checkbox_delete)
            ->with('checkbox_update', $checkbox_update);
    }
    
    public function postStore()
    {
        $sid = \Input::get('id');
        
        $rules = array(
            'name' => 'required'
        );

        $validation = \Validator::make(\Input::all(), $rules);

        if ($validation->fails()) {
            if (isset($sid)) {
                return redirect('admin/groups/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return redirect('admin/groups/create')->withErrors($validation)->withInput();
            }
        }
        
        // Validation passes
        $name 	= \Input::get('name');
        $view 	= (\Input::get('view') == '' ? false : true);
        $create 	= (\Input::get('create') == '' ? false : true);
        $delete 	= (\Input::get('delete') == '' ? false : true);
        $update 	= (\Input::get('update') == '' ? false : true);
        
        $permissions = json_encode(
            array(
                'admin.view' => $view,
                'admin.create' => $create,
                'admin.delete' => $delete,
                'admin.update' => $update
            )
        );
        
        if (isset($sid)) {
            $group = Group::find($sid);

            if ($group == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The group cannot be found because it does not exist or may have been deleted."
                );
                return redirect('/admin/groups')->withErrors($errors);
            }

            // Update the group details
            $group->name = $name;
            $group->permissions = $permissions;

            // Update the group
            if ($group->save()) {
                return redirect('admin/groups');
            } else {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The group cannot be updated due to some problem. Please try again."
                );
                return redirect('admin/groups/edit/' . $sid)->withErrors($errors)->withInput();
            }

        } else {
            // Create the group
            $group = new Group;
            $group->name = $name;
            $group->permissions = $permissions;
            
            try {
                $group->save();
            } catch (\Exception $exp) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The group cannot be created due to some problem. Please try again."
                );
                return redirect('admin/groups/create')->withErrors($errors)->withInput();
            }
        }
            

        return redirect('admin/groups');
    }
    
    public function getDelete($sid)
    {
        $group = Group::find($sid);
        
        if ($group == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The group cannot be deleted at this time. It may have already been deleted.");
            return redirect('/admin/groups')->withErrors($errors);
        }
        
        if (count($group->users) > 0) {
            // Prevent deletion of this group
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'deleteError',
                "The group cannot be deleted because it is in use. Try moving the users to another group first."
            );
            return redirect('/admin/groups')->withErrors($errors);
        } else {
            $group->delete();
        }

        return redirect('admin/groups');
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
            return redirect('admin/groups')->withErrors($validation);
        }
        
        if ($orderBy != 'asc' && $orderBy != 'desc') {
            $orderBy = 'asc';
        }

        $groups = Group::orderBy($sortBy, $orderBy)->paginate(20);

        return view('redminportal::groups/view')
            ->with('sortBy', $sortBy)
            ->with('orderBy', $orderBy)
            ->with('groups', $groups);
    }
}
