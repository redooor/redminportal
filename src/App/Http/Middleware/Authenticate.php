<?php namespace Redooor\Redminportal\App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'redminguard')
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax()) {
                return view('redminportal::users.notauthorized');
            } else {
                return redirect()->guest('login');
            }
        }
        
        $user = Auth::guard($guard)->user();

        // Check if user is activated
        if ($user != null) {
            if (! $user->activated) {
                // User logged in but was deactivated after
                // Log out this user and bring to login page
                Auth::guard($guard)->logout();
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
