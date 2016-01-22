<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Http\Request;
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
    
    /**
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
    
    /**
     * Check if user has access rights for the route
     * @param Request
     * @return bool True if user has access rights
     *
     * User level permission supersedes Group level permission.
     * Any group deny will result in forceful deny.
     * At least 1 group allow (if no deny) will result in allow.
     * Sub route permission supersedes Main route permission.
     *
     * -1: Deny     (Or any negative value, Force deny)
     * 1 : Allow
     * 0 : Inherit  (If none is defined, default is deny)
     **/
    public function hasAccess(Request $request)
    {
        $route = $request->path();
        
        // Check the type of request
        $type = $this->checkType($request);
        
        // Check user level permissions first
        $user_permission = $this->checkPermission($route, $type, json_decode($this->permissions));
        if ($user_permission < 0) {
            // Force deny
            return false;
        } elseif ($user_permission == 1) {
            // Force allow
            return true;
        }
        
        $permission_level = false;
        
        // Next check all group permissions
        foreach ($this->groups as $group) {
            $group_permission = $this->checkPermission($route, $type, json_decode($group->permissions));
            
            if ($group_permission < 0) {
                return false; // Forceful deny
            }
            
            $permission_level = $permission_level || $group_permission;
        }
        
        return $permission_level;
    }
    
    /**
     * Check the request and return the request type
     * e.g. view, create, update, delete, store
     *
     * @param Request
     **/
    private function checkType(Request $request)
    {
        $type = 'view';
        if ($request->is('admin/*/create')) {
            $type = 'create';
        } elseif ($request->is('admin/*/edit/*')) {
            $type = 'update';
        } elseif ($request->is('admin/*/delete/*')) {
            $type = 'delete';
        }
        
        return $type;
    }
    
    /**
     * Check if permission is allowed for the route and type
     * @param string route
     * @param string Type of permission (view, create, delete, update)
     * @param object Permissions
     * @return bool True if user has access rights
     *
     * Sub route permission supersedes Main route permission.
     *
     * -1: Deny     (Or any negative value, Force deny)
     * 1 : Allow
     * 0 : Inherit  (If none is defined, default is deny)
     **/
    private function checkPermission($route, $type, $permissions)
    {
        $permission_level = 0;
        
        if ($permissions) {
            /**
             * Go through all levels of route
             * Starting from the bottom
             * Checks for route.type (e.g. admin.view)
             **/
            $last_route = null;
            $route_path = explode('/', $route);
            while ($route_path) {
                $test_route = implode('.', $route_path) . '.' . $type;
                $permission_level = $this->checkRouteAccess($test_route, $permissions);
                // Exit if not 0
                if ($permission_level != 0) {
                    return $permission_level;
                }
                $last_route = array_pop($route_path); // Remove sub route
            }

            /**
             * Backward compatibility support
             * Checks for route without type (e.g. admin)
             **/
            if ($last_route) {
                $permission_level = $this->checkRouteAccess($last_route, $permissions);
                // Exit if not 0
                if ($permission_level != 0) {
                    return $permission_level;
                }
            }
        }
        
        return $permission_level;
    }
    
    /**
     * Check this route for permission
     *
     * @param string path
     * @param string Type of permission (view, create, delete, update)
     * @return bool True if user has access rights
     **/
    private function checkRouteAccess($route, $permissions)
    {
        $permission_level = 0;
        
        if (array_key_exists($route, $permissions)) {
            $permission_level = $permissions->$route;
            
            // Check if type is bool, convert to int
            if (gettype($permission_level) == 'boolean') {
                $permission_level = ($permission_level ? 1 : 0);
            }
        }
        
        return $permission_level;
    }
}
