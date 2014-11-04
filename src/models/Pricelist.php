<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

/*  Columns:
***********************
*   id (integer)
*   price (float)
*   module_id (integer)
*   membership_id (integer)
*   module_id, membership_id (unique)
*   created_at (date)
*   updated_at (date)
***********************/
class Pricelist extends Model {

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
