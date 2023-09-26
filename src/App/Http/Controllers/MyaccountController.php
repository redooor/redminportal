<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Redooor\Redminportal\App\Models\User;

class MyaccountController extends Controller
{
    
    public function __construct(User $model)
    {
        $this->model = $model;
        $this->pageView = 'redminportal::users.myaccount';
        $this->pageRoute = 'myaccount';
    }
    
    public function getIndex()
    {
        if (! Auth::guard('redminguard')->check()) {
            return redirect('login');
        }
        
        $user = Auth::guard('redminguard')->user();
        
        $data = array(
            'user' => $user
        );
        
        return view($this->pageView, $data);
    }
    
    public function postStore()
    {
        if (! Auth::guard('redminguard')->check()) {
            return redirect('login');
        }
        
        $authUser = Auth::guard('redminguard')->user();
        $errors = new MessageBag;
        $sid = $authUser->id;
        $user = User::find($sid);
        
        $rules = array(
            'first_name'    => 'required',
            'last_name'     => 'required',
            'old_password'  => 'required',
            'password'      => 'confirmed|min:6'
        );
        
        $messages = array(
            'old_password.required' => trans('redminportal::messages.user_error_old_password_required')
        );

        $validation = Validator::make(Input::all(), $rules, $messages);
        
        if ($validation->fails()) {
            return redirect($this->pageRoute)->withErrors($validation)->withInput();
        }
        
        $old_password = Input::get('old_password');
        
        // Check that the existing password mataches
        if (Hash::check($old_password, $user->password)) {
            // Update password if not empty
            $password = Input::get('password');
            if ($password != '') {
                $user->password = Hash::make($password);
            }
            // Update First and Last name
            $user->first_name = Input::get('first_name');
            $user->last_name = Input::get('last_name');
            
            if (! $user->save()) {
                $errors->add(
                    'saveError',
                    Lang::get('redminportal::messages.user_error_update_unknown')
                );
                return redirect($this->pageRoute)->withErrors($errors)->withInput();
            }
            
            if ($password != '') {
                Auth::guard('redminguard')->logout();
                return redirect($this->pageRoute);
            } else {
                $message = trans('redminportal::messages.user_myaccount_save_success');
                return redirect($this->pageRoute)->with('success_message', $message);
            }
        }
        
        $errors->add(
            'saveError',
            Lang::get('redminportal::messages.user_authentication_error')
        );
        return redirect($this->pageRoute)->withErrors($errors)->withInput();
    }
}
