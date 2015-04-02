<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Redooor\Redminportal\App\Helpers\RImage;

/* Columns
 *
 * id           (increment)
 * path         (string, 320)
 * imageable_id (integer)
 * imageable_type (string, 255)
 * created_at   (dateTime)
 * updated_at   (dateTime)
 *
 */

class Image extends Model
{
    protected $table = 'images';
    
    public function imageable()
    {
        return $this->morphTo();
    }
    
    public function remove()
    {
        if ($this == null) {
            return; // No such data found
        }
        
        // Remove original file
        $filename = $this->path;
        if (File::exists($filename)) {
            File::delete($filename);
        }
         
        $imagine = new RImage;
        
        // Remove all dimenions
        $defaultDimensions = \Config::get('redminportal::image.dimensions');
        
        foreach (array_keys($defaultDimensions) as $key) {
            $filename_variant = $imagine->getUrl($filename, $key);
            if (File::exists($filename_variant)) {
                File::delete($filename_variant);
            }
        }
        
        // Delete image model
        $this->delete();
    }
}
