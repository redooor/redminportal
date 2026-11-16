<?php namespace Redooor\Redminportal\App\Http\Validation;

/*
 * Internal validation rules for products. Not an extension API.
 */
class ProductRules extends InputRules
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
            'sku'               => 'required|alpha_dash|unique:products,sku' . (isset($id) ? ',' . $id : ''),
            'category_id'       => 'required|numeric|min:1',
            'tags'              => 'nullable|regex:/^[a-z,0-9 -]+$/i',
            'weight'            => 'nullable|numeric',
            'length'            => 'nullable|numeric',
            'width'             => 'nullable|numeric',
            'height'            => 'nullable|numeric'
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
