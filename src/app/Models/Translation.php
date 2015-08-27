<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/* Columns
 *
 * id                   (increment)
 * lang                 (string, 3)
 * content              (text)
 * translatable_id      (integer)
 * translatable_type    (string, 255)
 * created_at           (dateTime)
 * updated_at           (dateTime)
 *
 */

class Translation extends Model
{
    protected $table = 'translations';
    
    public function translatable()
    {
        return $this->morphTo();
    }
}
