<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOGIN_DECAY_SECONDS = 60;

    function __construct()
    {
        $this->guard = 'redminguard';
    }
    
    public function getIndex()
    {
        return view('redminportal::users/login');
    }

    public function getUnauthorized()
    {
        return view('redminportal::users/notauthorized');
    }

    public function getLogout()
    {
        // Logs the user out
        Auth::guard($this->guard)->logout();
        return redirect('/');
    }

    public function postLogin()
    {
        if (Auth::guard($this->guard)->check()) {
            return redirect('/');
        }
        
        $rules = array(
            'email'     => 'required|email',
            'password'  => 'required',
        );

        $validation = Validator::make(Input::all(), $rules);

        if ($validation->fails()) {
            return redirect('login')->withErrors($validation)->withInput();
        }
        
        $email      = Input::get('email');
        $password   = Input::get('password');
        $throttleKey = strtolower(trim($email)) . '|' . Request::ip();
        $limiter = app(\Illuminate\Cache\RateLimiter::class);

        if ($limiter->tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('invalid', trans(
                'redminportal::messages.login_error_too_many_attempts',
                array('seconds' => $limiter->availableIn($throttleKey))
            ));

            return redirect('login')->withErrors($errors)->withInput();
        }

        if (Auth::guard($this->guard)->attempt(array('email' => $email, 'password' => $password, 'activated' => 1))) {
            $limiter->clear($throttleKey);

            $user = Auth::guard($this->guard)->user();
            Request::session()->put('redmin_fingerprint_user', $user->id);
            Request::session()->put('redmin_password_fingerprint', hash('sha256', $user->password));

            return redirect()->intended('admin/dashboard');
        }

        $limiter->hit($throttleKey, self::LOGIN_DECAY_SECONDS);

        $errors = new \Illuminate\Support\MessageBag;
        $errors->add('invalid', "Oops, your email or password is incorrect.");

        return redirect('login')->withErrors($errors)->withInput();
    }
}
