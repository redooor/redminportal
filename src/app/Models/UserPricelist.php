<?php namespace Redooor\Redminportal\App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

/* Deprecated */

/* Legacy support
 *
 * user_pricelists table has been moved to order_pricelist table
 * This model attempts to support upgrading from v0.3.0 and below.
 * If you're new to this framework, use Order->Product instead.
 *
 */

/* Columns
 *
 * id           (increment)
 * user_id      (integer, unsigned)
 * pricelist_id (integer, unsigned)
 * paid         (decimal, 8, 2, default 0)
 * transaction_id (string, default 'Unknown', nullable)
 * payment_status (string, default 'Completed', nullable)
 * options      (text, nullable)
 * created_at   (dateTime)
 * updated_at   (dateTime)
 *
 * unique('user_id', 'pricelist_id')
 *
 */

class UserPricelist extends Model
{
    protected $table = 'orders';
    private $pricelistId;
    
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];
    
    public function user()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\User');
    }

    public function pricelists()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Pricelist', 'order_pricelist', 'order_id', 'pricelist_id');
    }
    
    /*
     * Legacy support, return first pricelist
     *
     * @return pricelist_id
     */
    public function getPricelistIdAttribute()
    {
        // Set pricelist_id
        $pricelist = $this->pricelists->first();
        
        if ($pricelist) {
            $this->pricelistId = $pricelist->id;
        } else {
            $this->pricelistId = null;
        }
        
        return $this->pricelistId;
    }
    
    /*
     * Legacy support, set pricelist id
     *
     */
    public function setPricelistIdAttribute($value)
    {
        $this->pricelistId = $value;
    }
    
    /*
     * Legacy support, return first pricelist
     *
     * @return Pricelist
     */
    public function getPricelistAttribute()
    {
        $result = Pricelist::join('order_pricelist', 'pricelists.id', '=', 'order_pricelist.pricelist_id')
            ->where('order_id', $this->id)
            ->first();
        
        return $result;
    }
    
    /*
     * Legacy support, saves Pricelist to Order
     *
     */
    public function save(array $options = array())
    {
        $result = false;
        
        if ($this->pricelistId) {
            $result = parent::save($options);
            $order = Order::find($this->id);
            $order->pricelists()->attach($this->pricelistId);
        } else {
            throw new Exception('pricelist_id not set.');
        }
        
        return $result;
    }
    
    public function delete()
    {
        // Remove product association
        $order = Order::find($this->id);
        $order->pricelists()->detach();
        
        return $order->delete();
    }
}
