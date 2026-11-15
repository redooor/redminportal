<?php namespace Redooor\Redminportal\App\Http\Validation;

/*
 * Internal validation rules for posts. Not an extension API.
 */
class PostRules extends InputRules
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
            'image'         => 'mimes:jpg,jpeg,png,gif|max:500',
            'images'        => 'array',
            'images.*'      => 'mimes:jpg,jpeg,png,gif|max:500',
            'title'         => 'required|regex:/^[a-z,0-9 ._\(\)-?]+$/i',
            'slug'          => 'required|regex:/^[a-z,0-9 ._\(\)-?]+$/i',
            'content'       => 'required',
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
