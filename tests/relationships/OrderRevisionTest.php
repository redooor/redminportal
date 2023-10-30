<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Redooor\Redminportal\App\Models\Order;

class OrderRevisionTest extends BaseRelationshipTest
{
    private $user;
    private $order;
    private $check_id;
    private $check_class;
    
    /**
     * Initialize Setup with seed
     */
    public function setUp(): void
    {
        parent::setUp();

        /* Fixed Event not fired issue with Laravel */
        Order::flushEventListeners();
        Order::boot();
        
        $this->seed('RedminSeeder');
        
        Auth::guard('redminguard')->loginUsingId(1);
        
        $this->user = Auth::guard('redminguard')->user();
        
        $this->order = $this->createNewModel(new Order, array(
            'user_id' => $this->user->id,
            'paid' => 10.99,
            'transaction_id' => 'UK12345YZ',
            'payment_status' => 'Pending'
        ));
        
        $this->check_id = $this->order->id;
        $this->check_class = get_class($this->order);
    }
    
    /**
     * Test (pass): Check that a new revision is created
     * when we create a new order.
     **/
    public function testCreateNewOrder()
    {
        $testcase = array(
            'user_id' => $this->user->id,
            'attribute' => 'created_at',
            'old_value' => null,
            'new_value' => $this->order->created_at
        );

        $this->assertTrue($this->order->revisions()->count() == 1);
        
        $check_orders = DB::table('revisions')
            ->where('revisionable_id', $this->check_id)
            ->where('revisionable_type', $this->check_class)
            ->count();
        
        $this->assertTrue($check_orders == 1);
        
        foreach ($this->order->revisions as $item) {
            $this->assertTrueModelAllTestcases($item, $testcase);
        }
        
        // Delete order will delete all relationship
        $this->order->delete();
        
        $check_orders = DB::table('revisions')
            ->where('revisionable_id', $this->check_id)
            ->where('revisionable_type', $this->check_class)
            ->count();
        
        $this->assertTrue($check_orders == 0);
    }
    
    /**
     * Test (pass): Check that a new revision is created
     * when we update an existing order.
     **/
    public function testUpdateExistingOrder()
    {
        $testcase = array(
            'user_id' => $this->user->id,
            'attribute' => 'payment_status',
            'old_value' => 'Pending',
            'new_value' => 'Completed'
        );
        
        $this->order->payment_status = 'Completed';
        $this->order->save();
        
        $this->assertTrue($this->order->revisions()->count() == 2);
        
        $check_orders = DB::table('revisions')
            ->where('revisionable_id', $this->check_id)
            ->where('revisionable_type', $this->check_class)
            ->count();
        
        $this->assertTrue($check_orders == 2);
        
        $this->assertTrueModelAllTestcases($this->order->revisions->last(), $testcase);
        
        // Delete order will delete all relationship
        $this->order->delete();
        
        $check_orders = DB::table('revisions')
            ->where('revisionable_id', $this->check_id)
            ->where('revisionable_type', $this->check_class)
            ->count();
        
        $this->assertTrue($check_orders == 0);
    }
}
