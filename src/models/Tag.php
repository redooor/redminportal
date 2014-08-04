<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model {
    
    public function tagable()
    {
        return $this->morphTo();
    }
    
}