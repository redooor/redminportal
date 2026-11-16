<?php namespace Redooor\Redminportal\App\Http\Validation;

/*
 * Internal validation rules for memberships. Not an extension API.
 */
class MembershipRules extends InputRules
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
            'name'   => 'required|unique:memberships,name' . (isset($id) ? ',' . $id : ''),
            'rank'   => 'required|integer|min:0',
        );
    }

    /**
     * Validation messages
     *
     * @return array
     */
    public function messages(): array
    {
        return array();
    }
}
