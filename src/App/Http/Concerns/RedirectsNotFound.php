<?php namespace Redooor\Redminportal\App\Http\Concerns;

/*
 * Internal helper for missing record redirects. Not an extension API.
 */
trait RedirectsNotFound
{
    /**
     * Redirect with a not-found message in an error bag
     *
     * @param string $path
     * @param string $message
     * @param string $errorKey
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectNotFoundWithMessage(string $path, string $message, string $errorKey = 'editError'): \Illuminate\Http\RedirectResponse
    {
        $errors = new \Illuminate\Support\MessageBag;
        $errors->add($errorKey, $message);
        return redirect($path)->withErrors($errors);
    }
}
