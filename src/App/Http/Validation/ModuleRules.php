<?php namespace Redooor\Redminportal\App\Http\Validation;

/*
 * Internal validation rules for modules. Not an extension API.
 */
class ModuleRules extends InputRules
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
            'name'              => 'required|unique:modules,name' . (isset($id) ? ',' . $id : ''),
            'short_description' => 'required',
            'sku'               => 'required|alpha_dash|unique:modules,sku' . (isset($id) ? ',' . $id : ''),
            'category_id'       => 'required|numeric|min:1',
            'tags'              => 'nullable|regex:/^[a-z,0-9 -]+$/i',
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
