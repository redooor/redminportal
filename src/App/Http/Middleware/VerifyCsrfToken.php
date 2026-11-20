<?php namespace Redooor\Redminportal\App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifyCsrfToken;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends BaseVerifyCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * A sign-in form left open until its session expired is sent back to a fresh sign-in page
     * with a message. Other requests keep the standard token mismatch response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            if ($request->is('login/login') && ! $request->expectsJson()) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add('invalid', trans('redminportal::messages.login_error_session_expired'));

                return redirect('login')->withErrors($errors);
            }

            throw $e;
        }
    }
}
