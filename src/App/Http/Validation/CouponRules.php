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
            'amount'        => 'required|numeric',
            'start_date'    => 'required|date_format:d/m/Y h:i A',
            'end_date'      => 'required|date_format:d/m/Y h:i A',
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
