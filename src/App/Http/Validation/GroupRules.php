<?php namespace Redooor\Redminportal\App\Http\Validation;

/*
 * Internal validation rules for groups. Not an extension API.
 */
class GroupRules extends InputRules
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
            'name' => 'required|unique:groups,name' . (isset($id) ? ',' . $id : ''),
            'permission-inherit' => 'nullable|regex:/^[a-z,0-9._\-?]+$/i',
            'permission-allow' => 'nullable|regex:/^[a-z,0-9._\-?]+$/i',
            'permission-deny' => 'nullable|regex:/^[a-z,0-9._\-?]+$/i'
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
            'permission-inherit.regex' => 'The permission inherit format is invalid. Try using the Permission Builder.',
            'permission-allow.regex' => 'The permission allow format is invalid. Try using the Permission Builder.',
            'permission-deny.regex' => 'The permission deny format is invalid. Try using the Permission Builder.'
        );
    }
}
