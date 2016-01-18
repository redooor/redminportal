<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/* Columns
 *
 * id               (increment)
 * email            (string, unique)
 * password         (string, 60)
 * remember_token   (string)
 * persmissions     (text, nullable)
 * activated        (boolean, default 0)
 * activation_code  (string, nullable, index)
 * actiavted_at     (timestamp, nullable)
 * last_login       (timestamp, nullable)
 * persist_code     (string, nullable)
 * reset_password_code  (string, nullable, index)
 * first_name       (string, nullable)
 * last_name        (string, nullable)
 * created_at       (dateTime)
 * updated_at       (dateTime)
 *
 */

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
    
    public function groups()
    {
        // Based on Cartalyst/Sentry SQL schema
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Group', 'users_groups');
    }
    
    public function orders()
    {
        return $this->hasMany('Redooor\Redminportal\App\Models\Order');
    }
    
    public function delete()
    {
        $this->groups()->detach();
        
        return parent::delete();
    }
    
    /*
    /* Add Group(s) to User
    /* @param Group can be single Id or array of Group Id
    /* @return bool True if successful
     */
    public function addGroup($group_id)
    {
        $successful = true;
        
        if ($group_id == null) {
            return false;
        }
        
        // Remove all existing group(s) from user
        $this->groups()->detach();
        
        // Assign group(s) to user
        if (is_array($group_id)) {
            // If multiple roles
            if (count($group_id) > 0) {
                foreach ($group_id as $item) {
                    $new_group = Group::find($item);
                    if ($new_group == null) {
                        $successful = false;
                    } else {
                        $this->groups()->save($new_group);
                    }
                }
            }
        } else {
            $new_group = Group::find($group_id);
            if ($new_group == null) {
                $successful = false;
            } else {
                $this->groups()->save($new_group);
            }
        }
        
        return $successful;
    }
}
