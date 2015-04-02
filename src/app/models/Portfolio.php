<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/* Columns
 *
 * id               (increment)
 * name             (string, 255)
 * short_description (string, 255)
 * long_description (text, nullable)
 * active           (boolean, default true)
 * options          (text, nullable)
 * category_id      (integer, unsigned)
 * created_at       (dateTime)
 * updated_at       (dateTime)
 *
 */

class Portfolio extends Model
{
    protected $table = 'portfolios';
    
    public function category()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\Category');
    }
    
    public function images()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Image', 'imageable');
    }
    
    public function deleteAllImages()
    {
        foreach ($this->images as $image) {
            // Delete physical file, including all different sizes
            $parts = pathinfo($image->path);
            
            $this->deleteFiles($parts['dirname']);
            
            // Delete image model
            $image->delete();
        }
    }
    
    public function deleteImageFolder($dir)
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
    
    public function delete()
    {
        $this->deleteAllImages();
        return parent::delete();
    }
}
