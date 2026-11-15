<?php namespace Redooor\Redminportal\App\Http\Validation;

/*
 * Internal validation rules for promotions. Not an extension API.
 */
class PromotionRules extends InputRules
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
            'image'             => 'mimes:jpg,jpeg,png,gif|max:500',
            'images'            => 'array',
            'images.*'          => 'mimes:jpg,jpeg,png,gif|max:500',
            'name'              => 'required',
            'short_description' => 'required',
            'long_description'  => 'required',
            'start_date'        => 'required',
            'end_date'          => 'required',
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
