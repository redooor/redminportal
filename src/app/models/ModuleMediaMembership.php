<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/* Columns
 *
 * id           (increment)
 * module_id    (integer, unsigned)
 * media_id     (integer, unsigned)
 * membership_id (integer, unsigned)
 * created_at   (dateTime)
 * updated_at   (dateTime)
 *
 * unique('module_id', 'media_id', 'membership_id')
 *
 */

class ModuleMediaMembership extends Model
{
    protected $table = 'module_media_memberships';
    
    public function module()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\Module');
    }

    public function media()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\Media');
    }

    public function membership()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\Membership');
    }
}
