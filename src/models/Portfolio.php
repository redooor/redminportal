<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Portfolio extends Model {

    public function category()
    {
        return $this->belongsTo('Redooor\Redminportal\Category');
    }
    
    public function images()
    {
        return $this->morphMany('Redooor\Redminportal\Image', 'imageable');
    }
    
    public function translations()
    {
        return $this->morphMany('Redooor\Redminportal\Translation', 'translatable');
    }
    
    public function deleteAllImages()
    {
        foreach ($this->images as $image)
        {
            // Delete physical file, including all different sizes
            $parts = pathinfo($image->path);
            
            $this->delete_files($parts['dirname']);
            
            // Delete image model
            $image->delete();
        }
    }
    
    public function deleteImageFolder($dir)
    {
        $this->delete_files($dir);
    }

    /* 
     * php delete function that deals with directories recursively
     */
    private function delete_files($target) 
    {
        if(is_dir($target)){
            $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
            
            foreach( $files as $file )
            {
                $this->delete_files( $file );      
            }
            
            if (is_dir($target)) {
                rmdir( $target );
            }
            
        } elseif(is_file($target)) {
            unlink( $target );  
        }
    }
    
    public function delete()
    {
        // Delete all translations
        $this->translations()->delete();

        return parent::delete();
    }

}