<?php namespace Redooor\Redminportal\App\Http\Validation;

/*
 * Internal validation rules for portfolios. Not an extension API.
 */
class PortfolioRules extends InputRules
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
            'category_id'       => 'required|numeric|min:1',
        );
    }

    /**
     * Validation messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'category_id.min' => 'The category field is required.'
        ];
    }
}
