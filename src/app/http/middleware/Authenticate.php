<?php namespace Redooor\Redminportal\App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Contracts\Auth\Guard;
use Redooor\Redminportal\App\Models\User;

class Authenticate {

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
		if ($this->auth->guest())
		{
			if ($request->ajax())
			{
				return view('redminportal::users.notauthorized');
			}
			else
			{
				return redirect()->guest('login');
			}
		}
        
        $email = Auth::user()->email;
        
        // Check if user is in Admin group
        $user = User::where('email', $email)->first();
        if ($user != null) {
            $group = $user->groups()->where('name', 'Admin')->first();
            if ($group != null) {
                // Save login time
                $user->last_login = date('Y-m-d H:i:s');
                $user->save();
                
                return $next($request);
            }
        }
        return redirect('login/unauthorized');
	}

}
