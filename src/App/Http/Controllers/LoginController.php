<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

        $validation = Validator::make(Request::all(), $rules);

        if ($validation->fails()) {
            return redirect('login')->withErrors($validation)->withInput();
        }
        
        $email      = Request::input('email');
        $password   = Request::input('password');
        $throttleKey = Str::transliterate(Str::lower(trim($email))) . '|' . Request::ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('invalid', trans(
                'redminportal::messages.login_error_too_many_attempts',
                ['seconds' => RateLimiter::availableIn($throttleKey)]
            ));

            return redirect('login')->withErrors($errors)->withInput();
        }

        if (Auth::guard($this->guard)->attempt(['email' => $email, 'password' => $password, 'activated' => 1])) {
            RateLimiter::clear($throttleKey);

            $user = Auth::guard($this->guard)->user();
            Request::session()->put('redmin_fingerprint_user', $user->id);
            Request::session()->put('redmin_password_fingerprint', hash('sha256', $user->password));

            return redirect()->intended('admin/dashboard');
        }

        RateLimiter::hit($throttleKey, self::LOGIN_DECAY_SECONDS);

        $errors = new \Illuminate\Support\MessageBag;
        $errors->add('invalid', "Oops, your email or password is incorrect.");

        return redirect('login')->withErrors($errors)->withInput();
    }
}
