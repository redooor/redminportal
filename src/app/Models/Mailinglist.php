<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/* Columns
 *
 * id           (increment)
 * email        (string, 255, unique)
 * first_name   (string, 255)
 * last_name    (string, 255)
 * active       (boolean, default true)
 * created_at   (dateTime)
 * updated_at   (dateTime)
 *
 */

class Mailinglist extends Model
{
    protected $table = 'mailinglists';
    
    public function delete()
    {
        return parent::delete();
    }
}
