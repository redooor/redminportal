<?php namespace Redooor\Redminportal\App\Http\Validation;

/*
 * Internal validation rules for mailinglists. Not an extension API.
 */
class MailinglistRules extends InputRules
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
            'email'          => 'required|unique:mailinglists,email' . (isset($id) ? ',' . $id : ''),
            'first_name'     => 'required',
            'last_name'      => 'required'
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
