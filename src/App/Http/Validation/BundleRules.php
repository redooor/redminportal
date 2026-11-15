<?php namespace Redooor\Redminportal\App\Http\Validation;

/*
 * Internal validation rules for bundles. Not an extension API.
 */
class BundleRules extends InputRules
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
            'name'              => 'required|unique:products,name' . (isset($id) ? ',' . $id : ''),
            'short_description' => 'required',
            'price'             => 'numeric',
            'sku'               => 'required|alpha_dash|unique:bundles,sku' . (isset($id) ? ',' . $id : ''),
            'category_id'       => 'required',
            'tags'              => 'regex:/^[a-z,0-9 -]+$/i',
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
