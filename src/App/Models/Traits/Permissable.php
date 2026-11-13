<?php namespace Redooor\Redminportal\App\Models\Traits;

use Illuminate\Http\Request;
use Redooor\Redminportal\App\Models\User;

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
        // Use the decoded path, the same one the router matches
        return $this->hasPermission($request->decodedPath(), $this->checkType($request));
    }

    /**
     * Check if user has permission for a path and action
     * @param string Path e.g. admin/users
     * @param string Action (view, create, update, delete)
     * @return bool True if user has permission
     *
     * User level permission supersedes Group level permission.
     * Any group deny will result in forceful deny.
     * At least 1 group allow (if no deny) will result in allow.
     **/
    public function hasPermission(string $path, string $action): bool
    {
        // Check user level permissions first
        $user_permission = $this->checkPermission($path, $action, json_decode($this->permissions));
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
            $group_permission = $this->checkPermission($path, $action, json_decode($group->permissions));

            if ($group_permission < 0) {
                return false; // Forceful deny
            }

            $permission_level = $permission_level || $group_permission;
        }

        return $permission_level;
    }

    /**
     * Check if every allowed permission entry can be granted by this user
     * @param array Permissions (key => value)
     * @return bool True if all allowed entries can be granted
     **/
    public function canGrant(array $permissions): bool
    {
        $actions = ['view', 'create', 'update', 'delete'];

        foreach ($permissions as $key => $value) {
            if ($value <= 0) {
                continue;
            }

            $key = (string) $key;
            $last_dot = strrpos($key, '.');

            if ($last_dot === false) {
                $path = $key;
                $action = null;
            } else {
                $path = substr($key, 0, $last_dot);
                $action = substr($key, $last_dot + 1);
            }

            $permission_path = $path;
            $path = str_replace('.', '/', $path);

            if ($action !== null && in_array($action, $actions, true)) {
                if (! $this->hasPermission($path, $action)
                    || $this->hasDescendantDeny($permission_path, $action)) {
                    return false;
                }
            } else {
                foreach ($actions as $required_action) {
                    if (! $this->hasPermission($path, $required_action)
                        || $this->hasDescendantDeny($permission_path, $required_action)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Check if a deny exists below a path for an action
     * @param string Path e.g. admin.pages
     * @param string Action (view, create, update, delete)
     * @return bool True if a narrower deny exists
     **/
    private function hasDescendantDeny(string $path, string $action): bool
    {
        $actions = ['view', 'create', 'update', 'delete'];
        $prefix = $path . '.';

        $permission_sets = [(array) json_decode($this->permissions)];

        foreach ($this->groups as $group) {
            $permission_sets[] = (array) json_decode($group->permissions);
        }

        foreach ($permission_sets as $permissions) {
            foreach ($permissions as $key => $value) {
                if ($value >= 0) {
                    continue;
                }

                $key = (string) $key;

                if (strpos($key, $prefix) !== 0) {
                    continue;
                }

                $last_dot = strrpos($key, '.');
                $last_segment = $last_dot === false ? $key : substr($key, $last_dot + 1);

                if ($last_segment === $action || ! in_array($last_segment, $actions, true)) {
                    // Only a deny that is still in force counts, a user-level allow can override a group deny
                    $deny_path = $last_segment === $action || in_array($last_segment, $actions, true)
                        ? substr($key, 0, strrpos($key, '.'))
                        : $key;

                    if (! $this->hasPermission(str_replace('.', '/', $deny_path), $action)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Returns denies that are no longer denied, as grantable entries
     * @param array Old permissions
     * @param array New permissions
     * @return array Entries to grant
     **/
    public function liftedDenies(array $old, array $new): array
    {
        $lifted = [];

        foreach ($old as $key => $value) {
            if ($value >= 0) {
                continue;
            }

            if (! array_key_exists($key, $new) || $new[$key] >= 0) {
                $lifted[$key] = 1;
            }
        }

        return $lifted;
    }

    /**
     * Check if this user can grant everything the target effectively holds
     * @param User Target user
     * @return bool True if the target is covered
     **/
    public function coversUser(User $target): bool
    {
        if ($this->id == $target->id) {
            return true;
        }

        $permissions = [];

        foreach ((array) $target->permissions() as $key => $value) {
            if ($value > 0) {
                $permissions[$key] = $value;
            }
        }

        foreach ($target->groups as $group) {
            foreach ((array) $group->permissions() as $key => $value) {
                if ($value > 0) {
                    $permissions[$key] = $value;
                }
            }
        }

        return $this->canGrant($permissions);
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
        if ($request->is('*/activate/*')
            || $request->is('*/deactivate/*')
            || $request->is('*/imgremove/*')
            || $request->is('*/imgprimary/*')
            || $request->is('*/imgmove/*')
            || $request->is('*/variant-imgremove/*')
            || $request->is('*/uploadform/*')
            || $request->is('*/upload/*')
            || $request->is('*/duration/*')) {
            $type = 'update';
        } elseif ($request->is('*/delete-variant-json/*')) {
            $type = 'delete';
        } elseif ($request->is('*/create-variant/*')) {
            $type = 'create';
        } elseif ($request->is('*/edit-variant/*')) {
            $type = 'update';
        } elseif ($request->is('*/create')) {
            $type = 'create';
        } elseif ($request->is('*/edit/*') || $request->is('*/update/*')) {
            $type = 'update';
        } elseif ($request->is('*/delete/*')) {
            $type = 'delete';
        } elseif ($request->is('admin/orders/store', 'admin/purchases/store') && $request->isMethod('post')) {
            // These handlers always add a new order, whatever id is sent
            $type = 'create';
        } elseif ($request->is('*/store') && $request->isMethod('post')) {
            // Use the same id for the permission check and the controller
            $id = $request->input('id');
            if (is_array($id) || (is_scalar($id) && trim((string) $id) !== '')) {
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
     * @param object Permissions
     * @return bool True if user has access rights
     **/
    private function checkRouteAccess($route, $permissions)
    {
        $permission_level = 0;
        
        if (property_exists($permissions, $route)) {
            $permission_level = $permissions->$route;
            
            // Check if type is bool, convert to int
            if (gettype($permission_level) == 'boolean') {
                $permission_level = ($permission_level ? 1 : 0);
            }
        }
        
        return $permission_level;
    }
}
