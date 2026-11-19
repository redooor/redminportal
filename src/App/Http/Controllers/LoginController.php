<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Redooor\Redminportal\App\Models\User;

class LoginController extends Controller
{
    const MAX_LOGIN_ATTEMPTS = 5;
    const MAX_IP_LOGIN_ATTEMPTS = 20;
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
        $ip         = Request::ip();
        $throttleKey = $this->accountThrottleKey($email, $ip);
        $ipThrottleKey = $this->ipThrottleKey($ip);
        $limiter = app(\Illuminate\Cache\RateLimiter::class);

        $accountThrottled = $limiter->tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS);
        $ipThrottled = $limiter->tooManyAttempts($ipThrottleKey, self::MAX_IP_LOGIN_ATTEMPTS);

        if ($accountThrottled || $ipThrottled) {
            $seconds = 0;

            if ($accountThrottled) {
                $seconds = $limiter->availableIn($throttleKey);
            }

            if ($ipThrottled) {
                $seconds = max($seconds, $limiter->availableIn($ipThrottleKey));
            }

            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('invalid', trans(
                'redminportal::messages.login_error_too_many_attempts',
                array('seconds' => $seconds)
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
        $limiter->hit($ipThrottleKey, self::LOGIN_DECAY_SECONDS);

        $errors = new \Illuminate\Support\MessageBag;
        $errors->add('invalid', "Oops, your email or password is incorrect.");

        return redirect('login')->withErrors($errors)->withInput();
    }

    /**
     * Key failed attempts by the matching account id, or by the email when no account exists.
     *
     * @param string $email
     * @param string $ip
     * @return string
     */
    private function accountThrottleKey($email, $ip)
    {
        $user = User::where('email', $email)->first();

        if ($user != null) {
            return 'user:' . $user->id . '|' . $ip;
        }

        return strtolower(trim($email)) . '|' . $ip;
    }

    /**
     * Key failed attempts for the whole IP address.
     *
     * @param string $ip
     * @return string
     */
    private function ipThrottleKey($ip)
    {
        return 'ip:' . $ip;
    }
}
