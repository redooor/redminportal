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
    
    public function delete()
    {
        // Remove original file
        $filename = $this->path;
        $this->deleteFiles($filename);
         
        $imagine = new RImage;
        
        // Remove all dimenions
        $defaultDimensions = \Config::get('redminportal::image.dimensions');
        
        foreach (array_keys($defaultDimensions) as $key) {
            $filename_variant = $imagine->getUrl($filename, $key);
            $this->deleteFiles($filename_variant);
        }
        
        return parent::delete();
    }
    
    /*
     * php delete function that deals with directories recursively
     */
    public function deleteFiles($target)
    {
        if (is_dir($target)) {
            $files = glob($target . '*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned

            foreach ($files as $file) {
                $this->deleteFiles($file);
            }

            if (is_dir($target)) {
                rmdir($target);
            }

        } elseif (is_file($target)) {
            unlink($target);
        }
    }
}
