<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model {
    
    public function imageable()
    {
        return $this->morphTo();
    }
    
    public function remove()
    {
        if($this == null) return; // No such data found
        
        // Remove original file
        $filename = $this->path;
        if( \File::exists($filename) ) \File::delete($filename);
         
        $imagine = new Helper\Image();
        
        // Remove all dimenions
        $defaultDimensions = \Config::get('redminportal::image.dimensions');
        foreach($defaultDimensions as $key => $value)
        {
            $filename_variant = $imagine->getUrl($filename, $key);
            if( \File::exists($filename_variant) ) \File::delete($filename_variant);
        } 
        
        // Delete image model
        $this->delete();
    }
    
}