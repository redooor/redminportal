<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/* Columns
 *
 * id           (increment)
 * user_id      (integer, unsigned)
 * paid         (decimal, 8, 2, default 0)
 * transaction_id (string, default 'Unknown', nullable)
 * payment_status (string, default 'Completed', nullable)
 * options      (text, nullable)
 * created_at   (dateTime)
 * updated_at   (dateTime)
 *
 */

class Order extends Model
{
    protected $table = 'orders';
    
    public function user()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\User');
    }
    
    public function products()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Product', 'order_product');
    }
    
    public function bundles()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Bundle', 'bundle_order');
    }
    
    public function delete()
    {
        // Remove product association
        $this->products()->detach();
        $this->bundles()->detach();
        
        return parent::delete();
    }
}
