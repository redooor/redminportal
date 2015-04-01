<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model {
    
    public function tagable()
    {
        return $this->morphTo();
    }
    
}