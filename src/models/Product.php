<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Product extends Model {

    public function category()
    {
        return $this->belongsTo('Redooor\Redminportal\Category');
    }
    
    public function images()
    {
        return $this->morphMany('Redooor\Redminportal\Image', 'imageable');
    }
    
    public function tags()
    {
        return $this->morphMany('Redooor\Redminportal\Tag', 'tagable');
    }
    
    public function deleteAllImages()
    {
        $folder = 'assets/img/products/';
        
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

}