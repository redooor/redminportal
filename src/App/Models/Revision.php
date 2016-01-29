<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/* Columns
 *
 * id                   (increment)
 * user_id              (integer, unsigned)
 * attribute            (string)
 * revisionable_id      (integer)
 * revisionable_type    (string, 255)
 * old_value            (text)
 * new_value            (text)
 * created_at           (dateTime)
 * updated_at           (dateTime)
 *
 */

class Revision extends Model
{
    protected $table = 'revisions';
    
    /**
     * Make morphable
     **/
    public function revisionable()
    {
        return $this->morphTo();
    }
    
    /**
     * Link revision to user
     **/
    public function user()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\User');
    }
    
    /**
     * Present attribute in a User friendly string
     **/
    public function showAttribute()
    {
        $present_attribute = $this->attribute;
        
        // Replace underscore and dots with space
        $to_be_replaced = array("_", ".");
        $present_attribute = str_replace($to_be_replaced, ' ', $present_attribute);
        
        return ucwords($present_attribute);
    }
}
