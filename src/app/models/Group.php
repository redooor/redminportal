<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;

class Group extends Model {

    public function users()
    {
        // Based on Cartalyst/Sentry SQL schema
        return $this->belongsToMany('Redooor\Redminportal\User', 'users_groups');
    }
}
