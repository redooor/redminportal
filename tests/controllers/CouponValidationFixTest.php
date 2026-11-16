<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Coupon;

/**
 * Covers the tightened coupon amount and date validation.
 */
class CouponValidationFixTest extends BaseControllerTest
{
    use CharacterizationSupport;

    protected $category;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/coupons';
        $this->viewhas = array(
            'singular' => 'coupon',
            'plural' => 'models'
        );

        $this->category = $this->createModel(Category::class, array(
            'name' => 'Coupon validation category',
            'short_description' => 'Coupon validation short',
            'active' => true
        ));

        $this->input = array(
            'create' => $this->couponInput(),
            'edit' => $this->couponInput(array('id' => 1))
        );
    }

    /**
     * Test (Pass): a valid d/m/Y h:i A coupon is accepted
     */
    public function testValidCouponIsAccepted()
    {
        $this->postStore($this->couponInput());

        $this->assertRedirectedTo('/admin/coupons');
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(1, Coupon::count());

        $coupon = Coupon::first();
        $this->assertSame('2016-05-02 17:39:00', $coupon->start_date);
        $this->assertSame('2016-05-03 17:39:00', $coupon->end_date);
    }

    /**
     * Test (Fail): a missing amount is required before the applyTo checks
     */
    public function testMissingAmountIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/coupons/create',
            $this->couponInput(array(), array('amount')),
            array('amount' => 'The amount field is required.')
        );

        $this->assertFalse($this->defaultErrors()->has('applyToError'));
        $this->assertSame(0, Coupon::count());
    }

    /**
     * Test (Fail): a non-numeric amount is rejected
     */
    public function testNonNumericAmountIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/coupons/create',
            $this->couponInput(array('amount' => 'abc')),
            array('amount' => 'The amount field must be a number.')
        );

        $this->assertSame(0, Coupon::count());
    }

    /**
     * Test (Fail): an ISO start date does not match d/m/Y h:i A
     */
    public function testWrongFormatStartDateIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/coupons/create',
            $this->couponInput(array('start_date' => '2016-03-01 10:00')),
            array('start_date' => 'The start date field must match the format d/m/Y h:i A.')
        );

        $this->assertSame(0, Coupon::count());
    }

    /**
     * Test (Fail): an ISO end date does not match d/m/Y h:i A
     */
    public function testWrongFormatEndDateIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/coupons/create',
            $this->couponInput(array('end_date' => '2016-03-01 10:00')),
            array('end_date' => 'The end date field must match the format d/m/Y h:i A.')
        );

        $this->assertSame(0, Coupon::count());
    }

    /**
     * Test (Pass): the coupon edit form dates still validate when unchanged
     */
    public function testEditWithUnchangedValidDatesIsAccepted()
    {
        $this->postStore($this->couponInput());
        $coupon = Coupon::first();
        $this->assertNotNull($coupon);

        $this->postStore($this->couponInput(array(
            'id' => $coupon->id,
            'code' => 'EDITED',
            'start_date' => date('d/m/Y h:i A', strtotime($coupon->start_date)),
            'end_date' => date('d/m/Y h:i A', strtotime($coupon->end_date))
        )));

        $this->assertRedirectedTo('/admin/coupons');
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(1, Coupon::count());

        $coupon = Coupon::find($coupon->id);
        $this->assertSame('EDITED', $coupon->code);
        $this->assertSame('2016-05-02 17:39:00', $coupon->start_date);
        $this->assertSame('2016-05-03 17:39:00', $coupon->end_date);
    }

    /**
     * Build a valid coupon input, optionally overridden or with keys removed
     */
    private function couponInput(array $overrides = array(), array $remove = array())
    {
        $input = array_merge(array(
            'code' => 'SAVE10',
            'description' => 'Coupon description',
            'amount' => '10.50',
            'start_date' => '02/05/2016 05:39 PM',
            'end_date' => '03/05/2016 05:39 PM',
            'max_spent' => '200.99',
            'min_spent' => '100.00',
            'is_percent' => '1',
            'multiple_coupons' => '1',
            'exclude_sale_item' => '1',
            'automatically_apply' => '1',
            'category_id' => array($this->category->id)
        ), $overrides);

        foreach ($remove as $key) {
            unset($input[$key]);
        }

        return $input;
    }
}
