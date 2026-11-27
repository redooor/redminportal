<?php namespace Redooor\Redminportal\App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery as BasePreventRequestForgery;
use Illuminate\Http\Exceptions\OriginMismatchException;
use Illuminate\Session\TokenMismatchException;

/*
 * Send an expired sign-in form back to the sign-in page instead of showing a 419 page
 * at a POST-only URL. Other requests keep the standard token mismatch response.
 */
class PreventRequestForgery extends BasePreventRequestForgery
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException | OriginMismatchException $e) {
            if ($request->is('login/login') && ! $request->expectsJson() && ! $request->isJson() && ! $request->ajax()) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add('invalid', trans('redminportal::messages.login_error_session_expired'));

                return redirect('login')->withErrors($errors);
            }

            throw $e;
        }
    }
}
