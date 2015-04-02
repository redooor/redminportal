<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/* Columns
 *
 * id           (increment)
 * path         (string, 320)
 * tagable_id   (integer)
 * tagable_type (string, 255)
 * created_at   (dateTime)
 * updated_at   (dateTime)
 *
 */

class Tag extends Model
{
    protected $table = 'tags';
    
    public function tagable()
    {
        return $this->morphTo();
    }
}
