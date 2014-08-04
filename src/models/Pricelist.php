<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Pricelist extends Model {

    /* Members:
    Price
    Module
    Membership
    */

    public function module()
    {
        return $this->belongsTo('Redooor\Redminportal\Module');
    }

    public function membership()
    {
        return $this->belongsTo('Redooor\Redminportal\Membership');
    }

    public function discounts()
    {
        return $this->morphMany('Redooor\Redminportal\Discount', 'discountable');
    }

}
