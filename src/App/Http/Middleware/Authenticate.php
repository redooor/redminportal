<?php namespace Redooor\Redminportal\App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Contracts\Auth\Guard;
use Redooor\Redminportal\App\Models\User;

class Authenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->guest()) {
            if ($request->ajax()) {
                return view('redminportal::users.notauthorized');
            } else {
                return redirect()->guest('login');
            }
        }
        
        $user = Auth::user();
        
        // Check if user has permission
        if ($user != null) {
            if (! $user->activated) {
                // User logged in but was deactivated after
                // Log out this user and bring to login page
                Auth::logout();
                return redirect()->guest('login');
            }
            // Proceed to check user permission
            if ($user->hasAccess($request)) {
                // Save login time
                $user->last_login = date('Y-m-d H:i:s');
                $user->save();
                
                return $next($request);
            }
        }
        
        return redirect('login/unauthorized');
    }
}
