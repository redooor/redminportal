<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;
use Redooor\Redminportal\App\Helpers\RHelper;

/*  Columns:
***********************
    id                  (increment)
    name                (string, 255)
    sku                 (string, 255)
    short_description   (string, 255)
    long_description    (text, nullable)
    price               (decimal, 8, 2)
    featured            (boolean, false)
    active              (boolean, true)
    options             (text, nullable)
    category_id         (unsigned, nullable)
    weight_unit         (string, 3, nullable)
    volume_unit         (string, 3, nullable)
    length              (decimal, 8, 3, nullable)
    width               (decimal, 8, 3, nullable)
    height              (decimal, 8, 3, nullable)
    weight              (decimal, 8, 3, nullable)
    created_at          (dateTime)
    updated_at          (dateTime)
***********************/
class Product extends Model
{
    protected $table = 'products';
    
    public function category()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\Category');
    }
    
    public function images()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Image', 'imageable');
    }
    
    public function tags()
    {
        return $this->morphToMany('Redooor\Redminportal\App\Models\Tag', 'taggable');
    }
    
    public function coupons()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Coupon', 'coupon_product');
    }
    
    public function bundles()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Bundle', 'bundle_product');
    }
    
    public function orders()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Order', 'order_product');
    }
    
    public function translations()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Translation', 'translatable');
    }
    
    public function variantParents()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Product', 'product_variant', 'variant_id', 'product_id');
    }
    
    public function variants()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Product', 'product_variant', 'product_id', 'variant_id');
    }
    
    public function delete()
    {
        // Remove all relationships
        $this->tags()->detach();
        $this->coupons()->detach();
        $this->bundles()->detach();
        $this->orders()->detach();
        $this->variantParents()->detach();
        
        // Detach and delete all variants
        foreach ($this->variants as $variant) {
            $variant->detach();
            $variant->delete();
        }
        
        // Delete all images
        foreach ($this->images as $image) {
            $image->delete();
        }
        
        // Delete all translations
        $this->translations()->delete();
        
        // Delete asset images folder
        $upload_dir = \Config::get('redminportal::image.upload_dir');
        $deleteFolder = new Image;
        $url_path = RHelper::joinPaths($upload_dir, $this->table, $this->id);
        $deleteFolder->deleteFiles($url_path);
        
        return parent::delete();
    }
}
