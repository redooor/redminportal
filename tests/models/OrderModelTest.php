<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Order;

class OrderModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Order;
        $testcase = array(
            'user_id' => 1,
            'paid' => 10.99,
            'transaction_id' => 'UK12345YZ',
            'payment_status' => 'Completed'
        );
        
        $this->prepare($model, $testcase);
    }
}
