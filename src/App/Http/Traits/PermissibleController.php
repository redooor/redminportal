<?php namespace Redooor\Redminportal\App\Http\Traits;

/*
 * Add permissible capability to controller
 */

trait PermissibleController
{
    /**
     * Returns an array of permissions based on given inherit, allow and deny list
     * @param array Inherit Permission
     * @param array Allow Permission
     * @param array Deny Permission
     * @return array Permission list
     **/
    protected function populatePermission($permission_inherit, $permission_allow, $permission_deny)
    {
        $permissions = [];
        
        // Add Deny permission
        $permissions = $this->retreivePermission($permissions, $permission_deny, -1);
        
        // Add Allow permission
        $permissions = $this->retreivePermission($permissions, $permission_allow, 1);
        
        // Add Allow permission
        $permissions = $this->retreivePermission($permissions, $permission_inherit, 0);
        
        return $permissions;
    }
    
    /**
     * Appends to an array of permissions based on given permission list and level
     * @param array Permission array to be appended
     * @param array Permission list to check through
     * @param int Level of permission
     * @return array Permission
     **/
    protected function retreivePermission($permissions, $check_permissions, $level)
    {
        $sorted_permissions = explode(',', $check_permissions);
        ksort($sorted_permissions);
        
        foreach ($sorted_permissions as $item) {
            $item = trim($item);
            if (! array_key_exists($item, $permissions) && ! empty($item)) {
                $permissions[$item] = $level;
            }
        }
        
        return $permissions;
    }
}
