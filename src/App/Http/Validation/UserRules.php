<?php namespace Redooor\Redminportal\App\Http\Validation;

/*
 * Internal validation rules for users. Not an extension API.
 */
class UserRules extends InputRules
{
    /**
     * Validation rules, optionally adjusted for an existing record id
     *
     * @param mixed $id
     * @return array
     */
    public function rules($id = null): array
    {
        $rules = array(
            'first_name'    => 'required',
            'last_name'     => 'required',
            'role'          => 'required',
            'email'         => 'required|unique:users,email,' . $id,
            'permission-inherit' => 'nullable|regex:/^[a-z,0-9._\-?]+$/i',
            'permission-allow'   => 'nullable|regex:/^[a-z,0-9._\-?]+$/i',
            'permission-deny'    => 'nullable|regex:/^[a-z,0-9._\-?]+$/i'
        );

        if (isset($id)) {
            $rules['password'] = 'confirmed|min:' . config('redminportal::password.min_length', 10);
        } else {
            $rules['password'] = 'required|confirmed|min:' . config('redminportal::password.min_length', 10);
        }

        return $rules;
    }

    /**
     * Validation messages
     *
     * @return array
     */
    public function messages(): array
    {
        return array(
            'permission-inherit.regex' => 'The permission inherit format is invalid. Try using the Permission Builder.',
            'permission-allow.regex' => 'The permission allow format is invalid. Try using the Permission Builder.',
            'permission-deny.regex' => 'The permission deny format is invalid. Try using the Permission Builder.'
        );
    }
}
