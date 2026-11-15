<?php namespace Redooor\Redminportal\App\Http\Concerns;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

/*
 * Internal helper for validating request input. Not an extension API.
 */
trait ValidatesInput
{
    /**
     * Validate request input or return a redirect back to the failure URL
     *
     * @param array $rules
     * @param array $messages
     * @param string $failureUrl
     * @return \Illuminate\Http\RedirectResponse|null
     */
    private function validateInputOrRedirect(array $rules, array $messages, string $failureUrl): ?\Illuminate\Http\RedirectResponse
    {
        $validation = Validator::make(Request::all(), $rules, $messages);

        if ($validation->fails()) {
            return redirect($failureUrl)->withErrors($validation)->withInput();
        }

        return null;
    }
}
