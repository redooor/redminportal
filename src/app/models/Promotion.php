<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

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
        return $this->morphMany('Redooor\Redminportal\Image', 'imageable');
    }
    
    public function deleteAllImages()
    {
        $folder = 'assets/img/promotions/';
        
        foreach ($this->images as $image) {
            // Delete physical file
            $filepath = $folder . $image->path;
            
            if (File::exists($filepath)) {
                File::delete($filepath);
            }
            
            // Delete image model
            $image->delete();
        }
    }
    
    public static function getAllActiveOrdered()
    {
        return Promotion::where('active', '=', '1')->orderBy('start_date', 'desc')->orderBy('name')->get();
    }
}
