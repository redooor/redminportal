<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/* Columns
 *
 * id           (increment)
 * name         (string, unique)
 * permissions  (text, nullable)
 * created_at   (dateTime)
 * updated_at   (dateTime)
 *
 */

class Group extends Model
{
    protected $table = 'groups';
    
    public function users()
    {
        // Based on Cartalyst/Sentry SQL schema
        return $this->belongsToMany('Redooor\Redminportal\App\Models\User', 'users_groups');
    }
    
    public function permissions()
    {
        return json_decode($this->permissions);
    }
    
    public function delete()
    {
        $this->users()->detach();
        return parent::delete();
    }
}
