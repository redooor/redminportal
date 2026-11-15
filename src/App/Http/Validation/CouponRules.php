<?php namespace Redooor\Redminportal\App\Http\Validation;

/*
 * Internal validation rules for coupons. Not an extension API.
 */
class CouponRules extends InputRules
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
            'code'          => 'required',
            // The earlier 'amount' => 'required' rule was overridden by this rule and must not be restored
            'amount'        => 'numeric',
            'start_date'    => 'required',
            'end_date'      => 'required',
            'min_spent'     => 'numeric',
            'max_spent'     => 'numeric'
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
