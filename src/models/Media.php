<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Media extends Model {

    public function category()
    {
        return $this->belongsTo('Redooor\Redminportal\Category');
    }
    
    public function group()
    {
        return $this->belongsTo('Redooor\Redminportal\Group');
    }
    
    public function images()
    {
        return $this->morphMany('Redooor\Redminportal\Image', 'imageable');
    }
    
    public function tags()
    {
        return $this->morphMany('Redooor\Redminportal\Tag', 'tagable');
    }
    
    public function translations()
    {
        return $this->morphMany('Redooor\Redminportal\Translation', 'translatable');
    }
    
    public function deleteAllImages()
    {
        $folder = 'assets/img/medias/';
        
        foreach ($this->images as $image)
        {
            // Delete physical file
            $filepath = $folder . $image->path;
            
            if( File::exists($filepath) ) {
                File::delete($filepath);
            }
            
            // Delete image model
            $image->delete();
        }
    }
    
    public function deleteAllTags()
    {
        foreach ($this->tags as $tag) 
        {
            $tag->delete();
        }
    }
    
    public function deleteMediaFolder($dir)
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