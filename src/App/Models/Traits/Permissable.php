<?php namespace Redooor\Redminportal\App\Models\Traits;

use Illuminate\Http\Request;

trait Permissable
{
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
        if ($request->is('*/create')) {
            $type = 'create';
        } elseif ($request->is('*/edit/*')) {
            $type = 'update';
        } elseif ($request->is('*/delete/*')) {
            $type = 'delete';
        } elseif ($request->is('*/store') && $request->isMethod('post')) {
            if ($request->has('id')) {
                $type = 'update';
            } else {
                $type = 'create';
            }
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
