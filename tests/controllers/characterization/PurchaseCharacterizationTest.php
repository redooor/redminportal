<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\User;
use Redooor\Redminportal\App\Models\UserPricelist;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class PurchaseCharacterizationTest extends BaseControllerTest
{
    use CharacterizationSupport;

    private $pricelist;
    private $secondPricelist;
    private $buyer;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/purchases';
        $this->viewhas = array(
            'singular' => 'purchase',
            'plural' => 'purchases'
        );

        $module = new Module;
        $module->name = 'Characterization Module';
        $module->sku = 'CHAR-SKU-1';
        $module->short_description = 'Short';
        $module->long_description = 'Long';
        $module->featured = false;
        $module->active = true;
        $module->category_id = 1;
        $module->save();

        $membership = new Membership;
        $membership->name = 'Gold';
        $membership->rank = 5;
        $membership->save();

        $secondMembership = new Membership;
        $secondMembership->name = 'Silver';
        $secondMembership->rank = 1;
        $secondMembership->save();

        $this->pricelist = new Pricelist;
        $this->pricelist->price = 10;
        $this->pricelist->module_id = $module->id;
        $this->pricelist->membership_id = $membership->id;
        $this->pricelist->save();

        $this->secondPricelist = new Pricelist;
        $this->secondPricelist->price = 20;
        $this->secondPricelist->module_id = $module->id;
        $this->secondPricelist->membership_id = $secondMembership->id;
        $this->secondPricelist->save();

        $this->buyer = $this->createUser('buyer@example.com');

        $this->input = array(
            'create' => $this->validInput(),
            'edit' => $this->validInput(array('id' => '1'))
        );
    }

    private function createUser($email, array $permissions = array())
    {
        $user = new User;
        $user->email = $email;
        $user->password = Hash::make('secret');
        $user->first_name = 'Purchase';
        $user->last_name = 'Tester';
        $user->activated = 1;
        $user->permissions = json_encode($permissions);
        $user->save();

        return $user;
    }

    private function login(User $user)
    {
        Auth::guard('redminguard')->loginUsingId($user->id);
    }

    private function validInput(array $overrides = array())
    {
        return array_merge(array(
            'pricelist_id' => $this->pricelist->id,
            'transaction_id' => 'TX-CHAR-001',
            'payment_status' => 'Completed',
            'paid' => '9.99',
            'email' => $this->buyer->email
        ), $overrides);
    }

    private function createPurchase($userId, $pricelistId)
    {
        $order = new Order;
        $order->user_id = $userId;
        $order->paid = 9.99;
        $order->transaction_id = 'EXISTING-TX';
        $order->payment_status = 'Completed';
        $order->save();

        $order->pricelists()->attach($pricelistId);

        return $order;
    }

    /**
     * Overwrite base function, edit is disabled for purchases
     */
    public function testEditPass()
    {
        $this->call('GET', '/admin/purchases/edit/1');

        $this->assertRedirectedTo('/admin/purchases');
        $this->assertSessionHasErrors('editError');
    }

    /**
     * Overwrite base function, a posted id still creates a new purchase instead of updating
     */
    public function testStoreEdit()
    {
        $this->postStore($this->validInput(array('id' => '1')));

        $this->assertRedirectedTo('/admin/purchases');
        $this->assertSame(1, Order::count());
    }

    /**
     * Test (Fail): no orders permission wins over invalid fields and redirects back with input
     */
    public function testCharacterizationPermissionDeniedBeforeValidation()
    {
        $actor = $this->createUser('no-orders@example.com', array(
            'admin.purchases.view' => 1,
            'admin.purchases.create' => 1
        ));
        $this->login($actor);

        $this->postStore(
            $this->validInput(array('transaction_id' => '')),
            array(),
            array('HTTP_REFERER' => 'http://localhost/admin/purchases/create')
        );

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/purchases/create');
        $this->assertSessionHasErrors('createError');
        $this->assertErrorMessage('createError', 'You also need permission to add orders.');
        $this->assertSame('', $this->defaultErrors()->first('transaction_id'));
        $this->assertOldInput(array('payment_status' => 'Completed', 'paid' => '9.99'));
        $this->assertSame(0, Order::count());
    }

    /**
     * Test (Fail): an actor with orders permission gets validation errors for invalid fields
     */
    public function testCharacterizationPermissionAllowedThenValidation()
    {
        $actor = $this->createUser('with-orders@example.com', array(
            'admin.purchases.create' => 1,
            'admin.orders.create' => 1
        ));
        $this->login($actor);

        $this->assertValidationFailure(
            '/admin/purchases/create',
            $this->validInput(array('transaction_id' => '')),
            array('transaction_id' => 'The transaction id field is required.')
        );

        $this->assertSame(0, Order::count());
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/purchases/create',
            $this->validInput(array('transaction_id' => '')),
            array('transaction_id' => 'The transaction id field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with id=5 redirects to edit/5 before any lookup
     */
    public function testCharacterizationValidationRedirectWhenIdIsFive()
    {
        $this->assertValidationFailure(
            '/admin/purchases/edit/5',
            $this->validInput(array('id' => '5', 'transaction_id' => '')),
            array('transaction_id' => 'The transaction id field is required.')
        );
    }

    /**
     * Test (Fail): an empty id is converted to null by the middleware and redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsEmptyString()
    {
        $this->assertValidationFailure(
            '/admin/purchases/create',
            $this->validInput(array('id' => '', 'transaction_id' => '')),
            array('transaction_id' => 'The transaction id field is required.')
        );
    }

    /**
     * Test (Fail): an id of zero is kept and redirects to edit/0
     */
    public function testCharacterizationValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/purchases/edit/0',
            $this->validInput(array('id' => '0', 'transaction_id' => '')),
            array('transaction_id' => 'The transaction id field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing order id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $order = $this->createPurchase($this->buyer->id, $this->pricelist->id);

        $this->assertValidationFailure(
            '/admin/purchases/edit/' . $order->id,
            $this->validInput(array('id' => $order->id, 'transaction_id' => '')),
            array('transaction_id' => 'The transaction id field is required.')
        );
    }

    /**
     * Test (Pass): a valid new purchase with id=5 is still created and redirects to the index
     */
    public function testCharacterizationNonexistentIdWithValidFieldsStillCreates()
    {
        $this->postStore($this->validInput(array('id' => '5')));

        $this->assertRedirectedTo('/admin/purchases');
        $this->assertNull(session('errors'));
        $this->assertSame(1, Order::count());
        $this->assertSame($this->buyer->id, (int) Order::first()->user_id);
    }

    /**
     * Test (Fail): pricelist_id is required
     */
    public function testCharacterizationMissingPricelistId()
    {
        $this->assertValidationFailure(
            '/admin/purchases/create',
            $this->validInput(array('pricelist_id' => '')),
            array('pricelist_id' => 'The pricelist id field is required.')
        );
    }

    /**
     * Test (Fail): a non-numeric pricelist_id fails the integer rule
     */
    public function testCharacterizationPricelistIdNonNumeric()
    {
        $this->assertValidationFailure(
            '/admin/purchases/create',
            $this->validInput(array('pricelist_id' => 'abc')),
            array('pricelist_id' => 'The pricelist id field must be an integer.')
        );
    }

    /**
     * Test (Fail): transaction_id is required
     */
    public function testCharacterizationMissingTransactionId()
    {
        $this->assertValidationFailure(
            '/admin/purchases/create',
            $this->validInput(array('transaction_id' => '')),
            array('transaction_id' => 'The transaction id field is required.')
        );
    }

    /**
     * Test (Fail): payment_status is required
     */
    public function testCharacterizationMissingPaymentStatus()
    {
        $this->assertValidationFailure(
            '/admin/purchases/create',
            $this->validInput(array('payment_status' => '')),
            array('payment_status' => 'The payment status field is required.')
        );
    }

    /**
     * Test (Fail): a non-numeric paid value fails the numeric rule
     */
    public function testCharacterizationPaidNonNumeric()
    {
        $this->assertValidationFailure(
            '/admin/purchases/create',
            $this->validInput(array('paid' => 'not-a-number')),
            array('paid' => 'The paid field must be a number.')
        );
    }

    /**
     * Test (Fail): email is required
     */
    public function testCharacterizationMissingEmail()
    {
        $this->assertValidationFailure(
            '/admin/purchases/create',
            $this->validInput(array('email' => '')),
            array('email' => 'The email field is required.')
        );
    }

    /**
     * Test (Fail): an invalid email fails the email rule
     */
    public function testCharacterizationInvalidEmail()
    {
        $this->assertValidationFailure(
            '/admin/purchases/create',
            $this->validInput(array('email' => 'not-an-email')),
            array('email' => 'The email field must be a valid email address.')
        );
    }

    /**
     * Test (Fail): old input keeps the scalar values after a validation failure
     */
    public function testCharacterizationOldInputAfterValidationFailure()
    {
        $this->assertValidationFailure(
            '/admin/purchases/create',
            $this->validInput(array('transaction_id' => '')),
            array('transaction_id' => 'The transaction id field is required.')
        );

        $this->assertOldInput(array(
            'pricelist_id' => (string) $this->pricelist->id,
            'payment_status' => 'Completed',
            'paid' => '9.99',
            'email' => $this->buyer->email
        ));
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson($this->validInput(array(
            'transaction_id' => '',
            '_error_bag' => 'custom'
        )));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/purchases/create');
        $this->assertSessionHasErrors('transaction_id');
        $this->assertErrorMessage('transaction_id', 'The transaction id field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }

    /**
     * Test (Fail): invalid fields beat the missing-pricelist check
     */
    public function testCharacterizationInvalidFieldsBeatMissingPricelist()
    {
        $this->postStore($this->validInput(array(
            'pricelist_id' => '999999',
            'transaction_id' => ''
        )));

        $this->assertRedirectedTo('/admin/purchases/create');
        $this->assertErrorMessage('transaction_id', 'The transaction id field is required.');
        $this->assertSame('', $this->defaultErrors()->first('pricelistError'));
        $this->assertSame(0, Order::count());
    }

    /**
     * Test (Fail): a missing pricelist gives pricelistError before the user lookup
     */
    public function testCharacterizationMissingPricelistBeatsMissingUser()
    {
        $this->postStore($this->validInput(array(
            'pricelist_id' => '999999',
            'email' => 'nobody@example.com'
        )));

        $this->assertRedirectedTo('/admin/purchases/create');
        $this->assertErrorMessage(
            'pricelistError',
            'The Module/Membership may have been deleted. Please try again.'
        );
        $this->assertSame('', $this->defaultErrors()->first('userError'));
        $this->assertSame(0, Order::count());
    }

    /**
     * Test (Fail): a missing user gives userError before the already-purchased check
     */
    public function testCharacterizationMissingUserBeatsAlreadyPurchased()
    {
        $this->createPurchase($this->buyer->id, $this->pricelist->id);

        $this->postStore($this->validInput(array('email' => 'nobody@example.com')));

        $this->assertRedirectedTo('/admin/purchases/create');
        $this->assertErrorMessage(
            'userError',
            'The user may have been deleted. Please try again.'
        );
        $this->assertSame('', $this->defaultErrors()->first('userpricelistError'));
        $this->assertSame(1, Order::count());
    }

    /**
     * Test (Fail): an existing purchase gives the exact already-purchased message
     */
    public function testCharacterizationAlreadyPurchased()
    {
        $this->createPurchase($this->buyer->id, $this->pricelist->id);

        $this->postStore($this->validInput());

        $this->assertRedirectedTo('/admin/purchases/create');
        $this->assertErrorMessage(
            'userpricelistError',
            'buyer@example.com has already purchased Characterization Module (Gold).'
        );
        $this->assertSame(1, Order::count());
    }

    /**
     * Test (Pass): a valid purchase saves the order and its pricelist relation
     */
    public function testCharacterizationCreatePurchase()
    {
        $this->postStore($this->validInput());

        $this->assertRedirectedTo('/admin/purchases');
        $this->assertSame(1, Order::count());
        $this->assertSame(1, UserPricelist::count());

        $purchase = UserPricelist::first();
        $this->assertSame($this->buyer->id, (int) $purchase->user_id);
        $this->assertSame('9.99', (string) $purchase->paid);
        $this->assertSame('TX-CHAR-001', $purchase->transaction_id);
        $this->assertSame('Completed', $purchase->payment_status);
        $this->assertSame($this->pricelist->id, $purchase->pricelists()->first()->id);
    }

    /**
     * Test (Fail): re-posting the same purchase keeps one row and gives the already-purchased error
     */
    public function testCharacterizationCreateThenRepostSamePurchase()
    {
        $this->postStore($this->validInput());

        $this->assertRedirectedTo('/admin/purchases');
        $this->assertSame(1, Order::count());

        $purchase = UserPricelist::first();

        $this->postStore($this->validInput(array(
            'id' => $purchase->id,
            'first_name' => 'Ignored'
        )));

        $this->assertRedirectedTo('/admin/purchases/edit/' . $purchase->id);
        $this->assertErrorMessage(
            'userpricelistError',
            'buyer@example.com has already purchased Characterization Module (Gold).'
        );
        $this->assertSame(1, Order::count());
        $this->assertSame('TX-CHAR-001', UserPricelist::first()->transaction_id);
    }

    /**
     * Test (Pass): an id does not select an existing purchase, a new pricelist creates another row
     */
    public function testCharacterizationIdDoesNotUpdateExistingPurchase()
    {
        $this->postStore($this->validInput());

        $this->assertRedirectedTo('/admin/purchases');
        $this->assertSame(1, Order::count());

        $purchase = UserPricelist::first();

        $this->postStore($this->validInput(array(
            'id' => $purchase->id,
            'pricelist_id' => $this->secondPricelist->id,
            'transaction_id' => 'TX-CHAR-002'
        )));

        $this->assertRedirectedTo('/admin/purchases');
        $this->assertSame(2, Order::count());
        $this->assertSame('TX-CHAR-001', UserPricelist::orderBy('id')->first()->transaction_id);
        $this->assertSame('TX-CHAR-002', UserPricelist::orderBy('id', 'desc')->first()->transaction_id);
    }

    /**
     * Test (Fail): getEdit is disabled and redirects with the exact editError message
     */
    public function testCharacterizationGetEditDisabled()
    {
        $this->call('GET', '/admin/purchases/edit/1');

        $this->assertRedirectedTo('/admin/purchases');
        $this->assertErrorMessage(
            'editError',
            'The edit function has been disabled for all purchases.'
        );
    }

    /**
     * Test (Fail): delete without orders delete permission keeps the order and redirects back
     */
    public function testCharacterizationDeleteNeedsOrdersDeletePermission()
    {
        $order = $this->createPurchase($this->buyer->id, $this->pricelist->id);

        $actor = $this->createUser('no-delete-orders@example.com', array(
            'admin.purchases.view' => 1,
            'admin.purchases.delete' => 1
        ));
        $this->login($actor);

        $this->call(
            'POST',
            '/admin/purchases/delete/' . $order->id,
            array(),
            array(),
            array(),
            array('HTTP_REFERER' => 'http://localhost/admin/purchases')
        );

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/purchases');
        $this->assertErrorMessage(
            'deleteError',
            'You also need permission to delete orders.'
        );
        $this->assertNotNull(Order::find($order->id));
    }

    /**
     * Test (Pass): delete with orders delete permission removes the order and redirects back
     */
    public function testCharacterizationDeleteWithOrdersDeletePermission()
    {
        $order = $this->createPurchase($this->buyer->id, $this->pricelist->id);

        $actor = $this->createUser('with-delete-orders@example.com', array(
            'admin.purchases.view' => 1,
            'admin.purchases.delete' => 1,
            'admin.orders.delete' => 1
        ));
        $this->login($actor);

        $this->call(
            'POST',
            '/admin/purchases/delete/' . $order->id,
            array(),
            array(),
            array(),
            array('HTTP_REFERER' => 'http://localhost/admin/purchases')
        );

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/purchases');
        $this->assertNull(Order::find($order->id));
    }
}
