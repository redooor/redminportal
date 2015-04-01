<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

/* Columns
 *
 * id               (increment)
 * name             (string, 255)
 * path             (string, 320)
 * sku              (string, 255, unique, nullable)
 * short_description (string, 255)
 * long_description (text, nullable)
 * featured         (boolean, default false)
 * active           (boolean, default true)
 * options          (text, nullable)
 * mimetype         (string, 255, default 'application/pdf')
 * category_id      (integer, unsigned)
 * created_at       (dateTime)
 * updated_at       (dateTime)
 *
 */

class Media extends Model
{
    protected $table = 'medias';
    
    public function category()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\Category');
    }
    
    public function group()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\Group');
    }
    
    public function images()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Image', 'imageable');
    }
    
    public function tags()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Tag', 'tagable');
    }
    
    public function deleteAllImages()
    {
        $folder = 'assets/img/medias/';
        
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
    
    public function deleteAllTags()
    {
        $this->tags()->delete();
    }
    
    public function deleteMediaFolder($dir)
    {
        $this->deleteFiles($dir);
    }

    /*
     * php delete function that deals with directories recursively
     */
    private function deleteFiles($target)
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
