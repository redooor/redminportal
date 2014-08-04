<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class UserPricelist extends Model {

    public function user()
    {
        return $this->belongsTo('Redooor\Redminportal\User');
    }

    public function pricelist()
    {
        return $this->belongsTo('Redooor\Redminportal\Pricelist');
    }

}
