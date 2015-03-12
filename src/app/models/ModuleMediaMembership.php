<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class ModuleMediaMembership extends Model {

    public function module()
    {
        return $this->belongsTo('Redooor\Redminportal\Module');
    }

    public function media()
    {
        return $this->belongsTo('Redooor\Redminportal\Media');
    }

    public function membership()
    {
        return $this->belongsTo('Redooor\Redminportal\Membership');
    }

}
