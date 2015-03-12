<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

/*  Columns:
***********************
    id                  (increment)
    name                (string, 255)
    sku                 (string, 255)
    short_description   (string, 255)
    long_description    (text, nullable)
    price               (float, 0)
    featured            (boolean, false)
    active              (boolean, true)
    options             (text, nullable)
    category_id         (unsigned, nullable)
    created_at  (dateTime)
    updated_at  (dateTime)
***********************/
class Product extends Model {

    protected $table = 'products';
    
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
    
    public function coupons()
    {
        return $this->belongsToMany('Redooor\Redminportal\Coupon', 'coupon_product');
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
    
    public function delete()
    {
        // Remove all relationships
        $this->coupons()->detach();
        
        return parent::delete();
    }

}