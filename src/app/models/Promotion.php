<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;
use Redooor\Redminportal\App\Helpers\RHelper;

/* Columns
 *
 * id               (increment)
 * name             (string, 255)
 * short_description (string, 255)
 * long_description (text, nullable)
 * start_date       (date)
 * end_date         (date)
 * active           (boolean, default true)
 * options          (text, nullable)
 * created_at       (dateTime)
 * updated_at       (dateTime)
 *
 */

class Promotion extends Model
{
    protected $table = 'promotions';
    
    public function images()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Image', 'imageable');
    }
    
    public static function getAllActiveOrdered()
    {
        return Promotion::where('active', '=', '1')->orderBy('start_date', 'desc')->orderBy('name')->get();
    }
    
    public function delete()
    {
        // Delete all images
        foreach ($this->images as $image) {
            $image->delete();
        }
        
        // Delete asset images folder
        $upload_dir = \Config::get('redminportal::image.upload_dir');
        $deleteFolder = new Image;
        $url_path = RHelper::joinPaths($upload_dir, $this->table, $this->id);
        $deleteFolder->deleteFiles($url_path);
        
        return parent::delete();
    }
}
