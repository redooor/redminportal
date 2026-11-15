<?php namespace Redooor\Redminportal\App\Http\Validation;

/*
 * Internal validation rules base class. Not an extension API.
 *
 * Rules classes are stateless and read config and translations when called,
 * not in constructors or static properties.
 */
abstract class InputRules
{
    /**
     * Validation rules, optionally adjusted for an existing record id
     *
     * @param mixed $id
     * @return array
     */
    abstract public function rules($id = null): array;

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
