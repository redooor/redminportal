<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Membership extends Model {

    public function modules()
    {
        return $this->hasMany('Redooor\Redminportal\Module');
    }

}
