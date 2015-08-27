<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/* Columns
 *
 * id           (increment)
 * name         (string, 255)
 * rank         (integer, default 0)
 * created_at   (dateTime)
 * updated_at   (dateTime)
 *
 */

class Membership extends Model
{
    protected $table = 'memberships';
    
    public function modules()
    {
        return $this->belongsToMany(
            'Redooor\Redminportal\App\Models\Module',
            'module_media_memberships',
            'membership_id',
            'module_id'
        );
    }
    
    public function medias()
    {
        return $this->belongsToMany(
            'Redooor\Redminportal\App\Models\Media',
            'module_media_memberships',
            'membership_id',
            'media_id'
        );
    }
    
    public function delete()
    {
        $this->modules()->detach();
        $this->medias()->detach();
        return parent::delete();
    }
}
