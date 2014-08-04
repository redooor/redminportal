<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model {

    /* Members:
    Code
    Expiry_date
    */

    public function pricelist()
    {
        return $this->belongsTo('Redooor\Redminportal\Pricelist');
    }

    public function discountable()
    {
        return $this->morphTo();
    }

}
