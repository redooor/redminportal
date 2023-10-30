<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Helpers\RHelper;

/*  Columns:
***********************
    id                  (increment)
    name                (string, 255)
    sku                 (string, 255)
    short_description   (string, 255)
    long_description    (text, nullable)
    price               (decimal(8,2), 0)
    featured            (boolean, false)
    active              (boolean, true)
    options             (text, nullable)
    category_id         (unsigned, nullable)
    created_at  (dateTime)
    updated_at  (dateTime)
***********************/
class Bundle extends Model
{
    protected $table = 'bundles';
    
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
    
    public function pricelists()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Pricelist', 'bundle_pricelist');
    }
    
    public function products()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Product', 'bundle_product');
    }
    
    public function coupons()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Coupon', 'bundle_coupon');
    }
    
    public function translations()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Translation', 'translatable');
    }
    
    public function orders()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Order', 'bundle_order');
    }
    
    public function totalvalue()
    {
        $totalvalue = 0;
        
        $totalvalue = $this->pricelists->sum('price') + $this->products->sum('price');
        
        return $totalvalue;
    }
    
    public function delete()
    {
        // Remove all relationships
        $this->tags()->detach();
        $this->pricelists()->detach();
        $this->products()->detach();
        $this->orders()->detach();
        $this->coupons()->detach();
        
        // Delete all images
        foreach ($this->images as $image) {
            $image->delete();
        }
        
        // Delete all translations
        $this->translations()->delete();
        
        // Delete asset images folder
        $upload_dir = Config::get('redminportal::image.upload_dir');
        $deleteFolder = new Image;
        $url_path = RHelper::joinPaths($upload_dir, $this->table, $this->id);
        $deleteFolder->deleteFiles($url_path);
        
        return parent::delete();
    }
}
