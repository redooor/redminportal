<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/* Columns
 *
 * id           (increment)
 * user_id      (integer, unsigned)
 * pricelist    (integer, unsigned)
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
    public function user()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\User');
    }

    public function pricelist()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\Pricelist');
    }
}
