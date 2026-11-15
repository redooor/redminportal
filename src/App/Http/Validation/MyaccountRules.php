<?php namespace Redooor\Redminportal\App\Http\Validation;

/*
 * Internal validation rules for myaccount. Not an extension API.
 */
class MyaccountRules extends InputRules
{
    /**
     * Validation rules, optionally adjusted for an existing record id
     *
     * @param mixed $id
     * @return array
     */
    public function rules($id = null): array
    {
        return array(
            'first_name'    => 'required',
            'last_name'     => 'required',
            'old_password'  => 'required',
            'password'      => 'confirmed|min:' . config('redminportal::password.min_length', 10)
        );
    }

    /**
     * Validation messages
     *
     * @return array
     */
    public function messages(): array
    {
        return array(
            'old_password.required' => trans('redminportal::messages.user_error_old_password_required')
        );
    }
}
