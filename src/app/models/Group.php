<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model {

    public function users()
    {
        // Based on Cartalyst/Sentry SQL schema
        return $this->belongsToMany('Redooor\Redminportal\App\Models\User', 'users_groups');
    }
}
