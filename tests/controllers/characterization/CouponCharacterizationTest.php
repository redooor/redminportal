<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Bundle;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Coupon;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\Product;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class CouponCharacterizationTest extends BaseControllerTest
{
    use CharacterizationSupport;

    protected $category;
    protected $product;
    protected $bundle;
    protected $membership;
    protected $module;
    protected $pricelist;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/coupons';
        $this->viewhas = array(
            'singular' => 'coupon',
            'plural' => 'models'
        );

        $this->createCouponFixtures();

        $this->input = array(
            'create' => $this->couponInput(),
            'edit' => $this->couponInput(array('id' => 1))
        );
    }

    /**
     * Build the rows a coupon can be restricted to
     */
    private function createCouponFixtures()
    {
        $this->category = $this->createModel(Category::class, array(
            'name' => 'Coupon category',
            'short_description' => 'Coupon category short',
            'active' => true
        ));

        $this->product = $this->createModel(Product::class, array(
            'name' => 'Coupon product',
            'sku' => 'COUPON-PRODUCT-SKU',
            'short_description' => 'Coupon product short',
            'category_id' => $this->category->id,
            'active' => true
        ));

        $this->bundle = $this->createModel(Bundle::class, array(
            'name' => 'Coupon bundle',
            'sku' => 'COUPON-BUNDLE-SKU',
            'short_description' => 'Coupon bundle short',
            'category_id' => $this->category->id,
            'active' => true
        ));

        $this->membership = $this->createModel(Membership::class, array(
            'name' => 'Coupon membership',
            'rank' => 1
        ));

        $this->module = $this->createModel(Module::class, array(
            'name' => 'Coupon module',
            'sku' => 'COUPON-MODULE-SKU',
            'short_description' => 'Coupon module short',
            'category_id' => $this->category->id,
            'active' => true
        ));

        $this->pricelist = $this->createModel(Pricelist::class, array(
            'price' => 10,
            'module_id' => $this->module->id,
            'membership_id' => $this->membership->id
        ));
    }

    /**
     * A valid coupon payload, with optional overrides and omitted keys
     */
    private function couponInput(array $overrides = array(), array $without = array())
    {
        $input = array_merge(array(
            'code' => 'SAVE10',
            'description' => 'Coupon description',
            'amount' => '10.50',
            'start_date' => '02/05/2016 05:39 PM',
            'end_date' => '03/05/2016 05:39 PM',
            'max_spent' => '200.99',
            'min_spent' => '100.00',
            'usage_limit_per_coupon' => '10',
            'usage_limit_per_user' => '1',
            'is_percent' => '1',
            'multiple_coupons' => '1',
            'exclude_sale_item' => '1',
            'automatically_apply' => '1',
            'category_id' => array($this->category->id)
        ), $overrides);

        foreach ($without as $key) {
            unset($input[$key]);
        }

        return $input;
    }

    /**
     * Test (Pass): create then update keeps one row, saves fields and replaces every pivot
     */
    public function testCharacterizationCreateThenUpdateRelationsAndFields()
    {
        $firstCategory = $this->category;
        $secondCategory = $this->createModel(Category::class, array(
            'name' => 'Second coupon category',
            'short_description' => 'Second category short',
            'active' => true
        ));
        $secondProduct = $this->createModel(Product::class, array(
            'name' => 'Second coupon product',
            'sku' => 'SECOND-COUPON-PRODUCT-SKU',
            'short_description' => 'Second product short',
            'active' => true
        ));

        $this->postStore($this->couponInput(array(
            'code' => 'ORIGINAL',
            'description' => 'Original description',
            'amount' => '19.99',
            'category_id' => array($firstCategory->id),
            'product_id' => array($this->product->id),
            'pricelist_id' => array($this->pricelist->id),
            'bundle_id' => array($this->bundle->id)
        )));

        $this->assertRedirectedTo('/admin/coupons');
        $this->assertSame(1, Coupon::count());

        $coupon = Coupon::first();
        $this->assertSame('ORIGINAL', $coupon->code);
        $this->assertSame('Original description', $coupon->description);
        $this->assertSame(19.99, (float) $coupon->amount);
        $this->assertSame('2016-05-02 17:39:00', $coupon->start_date);
        $this->assertSame('2016-05-03 17:39:00', $coupon->end_date);
        $this->assertSame(1, (int) $coupon->is_percent);
        $this->assertSame(1, (int) $coupon->multiple_coupons);
        $this->assertSame(1, (int) $coupon->exclude_sale_item);
        $this->assertSame(1, (int) $coupon->automatically_apply);
        $this->assertSame(array($firstCategory->id), $coupon->categories()->pluck('categories.id')->all());
        $this->assertSame(array($this->product->id), $coupon->products()->pluck('products.id')->all());
        $this->assertSame(array($this->pricelist->id), $coupon->pricelists()->pluck('pricelists.id')->all());
        $this->assertSame(array($this->bundle->id), $coupon->bundles()->pluck('bundles.id')->all());

        $this->postStore($this->couponInput(array(
            'id' => $coupon->id,
            'code' => 'UPDATED',
            'description' => 'Updated description',
            'amount' => '29.99',
            'is_percent' => '',
            'multiple_coupons' => '',
            'exclude_sale_item' => '',
            'automatically_apply' => '',
            'category_id' => array($secondCategory->id),
            'product_id' => array($secondProduct->id)
        )));

        $this->assertRedirectedTo('/admin/coupons');
        $this->assertSame(1, Coupon::count());

        $coupon = Coupon::find($coupon->id);
        $this->assertSame('UPDATED', $coupon->code);
        $this->assertSame('Updated description', $coupon->description);
        $this->assertSame(29.99, (float) $coupon->amount);
        $this->assertSame(0, (int) $coupon->is_percent);
        $this->assertSame(0, (int) $coupon->multiple_coupons);
        $this->assertSame(0, (int) $coupon->exclude_sale_item);
        $this->assertSame(0, (int) $coupon->automatically_apply);
        $this->assertSame(array($secondCategory->id), $coupon->categories()->pluck('categories.id')->all());
        $this->assertSame(array($secondProduct->id), $coupon->products()->pluck('products.id')->all());
        $this->assertSame(array(), $coupon->pricelists()->pluck('pricelists.id')->all());
        $this->assertSame(array(), $coupon->bundles()->pluck('bundles.id')->all());
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/coupons/create',
            $this->couponInput(array(), array('code')),
            array('code' => 'The code field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with a nonexistent id redirects to edit/5 before the not-found check
     */
    public function testCharacterizationValidationRedirectWhenIdIsFive()
    {
        $this->assertValidationFailure(
            '/admin/coupons/edit/5',
            $this->couponInput(array('id' => '5'), array('code')),
            array('code' => 'The code field is required.')
        );
    }

    /**
     * Test (Fail): an empty id is converted to null by the HTTP middleware, so it redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsEmptyString()
    {
        $this->assertValidationFailure(
            '/admin/coupons/create',
            $this->couponInput(array('id' => ''), array('code')),
            array('code' => 'The code field is required.')
        );
    }

    /**
     * Test (Fail): an id of zero is present and redirects to edit/0
     */
    public function testCharacterizationValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/coupons/edit/0',
            $this->couponInput(array('id' => '0'), array('code')),
            array('code' => 'The code field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $coupon = $this->createModel(Coupon::class, array(
            'code' => 'EXISTING',
            'amount' => 5,
            'start_date' => '2016-05-02 17:39:00',
            'end_date' => '2016-05-03 17:39:00'
        ));

        $this->assertValidationFailure(
            '/admin/coupons/edit/' . $coupon->id,
            $this->couponInput(array('id' => $coupon->id), array('code')),
            array('code' => 'The code field is required.')
        );
    }

    /**
     * Test (Fail): valid input with a nonexistent id gives couponError
     */
    public function testCharacterizationNonexistentIdWithValidFields()
    {
        $this->postStore($this->couponInput(array('id' => '5')));

        $this->assertRedirectedTo('/admin/coupons');
        $this->assertErrorMessage('couponError', 'The coupon does not exist or may have been deleted.');
        $this->assertSame(0, Coupon::count());
    }

    /**
     * Test (Fail): code is required
     */
    public function testCharacterizationMissingCode()
    {
        $this->assertValidationFailure(
            '/admin/coupons/create',
            $this->couponInput(array(), array('code')),
            array('code' => 'The code field is required.')
        );
    }

    /**
     * Test (Fail): start_date is required
     */
    public function testCharacterizationMissingStartDate()
    {
        $this->assertValidationFailure(
            '/admin/coupons/create',
            $this->couponInput(array(), array('start_date')),
            array('start_date' => 'The start date field is required.')
        );
    }

    /**
     * Test (Fail): end_date is required
     */
    public function testCharacterizationMissingEndDate()
    {
        $this->assertValidationFailure(
            '/admin/coupons/create',
            $this->couponInput(array(), array('end_date')),
            array('end_date' => 'The end date field is required.')
        );
    }

    /**
     * Test (Fail): amount is required
     */
    public function testCharacterizationAmountMissingFailsValidation()
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
     * Test (Fail): a non-numeric amount fails the numeric rule
     */
    public function testCharacterizationNonNumericAmount()
    {
        $this->assertValidationFailure(
            '/admin/coupons/create',
            $this->couponInput(array('amount' => 'abc')),
            array('amount' => 'The amount field must be a number.')
        );
    }

    /**
     * Test (Fail): non-numeric min_spent and max_spent fail with their exact messages
     */
    public function testCharacterizationNonNumericSpentFields()
    {
        $this->assertValidationFailure(
            '/admin/coupons/create',
            $this->couponInput(array('min_spent' => 'abc', 'max_spent' => 'def')),
            array(
                'min_spent' => 'The min spent field must be a number.',
                'max_spent' => 'The max spent field must be a number.'
            )
        );
    }

    /**
     * Test (Fail): invalid numeric beats an invalid date range because validation runs first
     */
    public function testCharacterizationInvalidNumericBeatsDateRange()
    {
        $this->assertValidationFailure(
            '/admin/coupons/create',
            $this->couponInput(array(
                'amount' => 'abc',
                'start_date' => '03/05/2016 05:39 PM',
                'end_date' => '02/05/2016 05:39 PM'
            )),
            array('amount' => 'The amount field must be a number.')
        );

        $this->assertFalse($this->defaultErrors()->has('dateRangeError'));
    }

    /**
     * Test (Fail): invalid validation for a nonexistent id redirects to edit/5, not the couponError page
     */
    public function testCharacterizationValidationForNonexistentIdBeatsNotFound()
    {
        $this->assertValidationFailure(
            '/admin/coupons/edit/5',
            $this->couponInput(array('id' => '5', 'amount' => 'abc'), array('code')),
            array(
                'code' => 'The code field is required.',
                'amount' => 'The amount field must be a number.'
            )
        );

        $this->assertFalse($this->defaultErrors()->has('couponError'));
    }

    /**
     * Test (Fail): old input keeps scalar values after a validation failure
     */
    public function testCharacterizationOldInput()
    {
        $this->assertValidationFailure(
            '/admin/coupons/create',
            $this->couponInput(array(
                'description' => 'Old description',
                'amount' => '11.11',
                'is_percent' => '1'
            ), array('code')),
            array('code' => 'The code field is required.')
        );

        $this->assertOldInput(array(
            'description' => 'Old description',
            'amount' => '11.11',
            'is_percent' => '1',
            'start_date' => '02/05/2016 05:39 PM',
            'end_date' => '03/05/2016 05:39 PM'
        ));
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson($this->couponInput(array('_error_bag' => 'custom'), array('code')));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/coupons/create');
        $this->assertSessionHasErrors('code');
        $this->assertErrorMessage('code', 'The code field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }

    /**
     * Test (Fail): the couponError not-found check wins over date, spent and apply-to errors
     */
    public function testCharacterizationCouponErrorBeatsDateSpentAndApplyTo()
    {
        $this->postStore($this->couponInput(array(
            'id' => '5',
            'start_date' => '03/05/2016 05:39 PM',
            'end_date' => '02/05/2016 05:39 PM',
            'max_spent' => '10.00',
            'min_spent' => '20.00'
        ), array('category_id')));

        $this->assertRedirectedTo('/admin/coupons');
        $this->assertErrorMessage('couponError', 'The coupon does not exist or may have been deleted.');
        $this->assertFalse($this->defaultErrors()->has('dateRangeError'));
        $this->assertFalse($this->defaultErrors()->has('spentRangeError'));
        $this->assertFalse($this->defaultErrors()->has('applyToError'));
        $this->assertSame(0, Coupon::count());
    }

    /**
     * Test (Fail): dateRangeError beats spentRangeError and applyToError
     */
    public function testCharacterizationDateRangeErrorBeatsSpentAndApplyTo()
    {
        $this->postStore($this->couponInput(array(
            'start_date' => '03/05/2016 05:39 PM',
            'end_date' => '02/05/2016 05:39 PM',
            'max_spent' => '10.00',
            'min_spent' => '20.00'
        ), array('category_id')));

        $this->assertRedirectedTo('/admin/coupons/create');
        $this->assertErrorMessage('dateRangeError', 'Please enter an End date later than Start date.');
        $this->assertFalse($this->defaultErrors()->has('spentRangeError'));
        $this->assertFalse($this->defaultErrors()->has('applyToError'));
        $this->assertSame(0, Coupon::count());
    }

    /**
     * Test (Fail): the end date must be strictly later than the start date
     */
    public function testCharacterizationEqualStartAndEndDateFailsDateRange()
    {
        $this->postStore($this->couponInput(array(
            'start_date' => '02/05/2016 05:39 PM',
            'end_date' => '02/05/2016 05:39 PM'
        )));

        $this->assertRedirectedTo('/admin/coupons/create');
        $this->assertErrorMessage('dateRangeError', 'Please enter an End date later than Start date.');
        $this->assertSame(0, Coupon::count());
    }

    /**
     * Test (Fail): dateRangeError with an existing id redirects to the edit url and keeps the row
     */
    public function testCharacterizationDateRangeErrorRedirectsToEditWhenIdExists()
    {
        $coupon = $this->createModel(Coupon::class, array(
            'code' => 'EXISTING',
            'amount' => 5,
            'start_date' => '2016-05-02 17:39:00',
            'end_date' => '2016-05-03 17:39:00'
        ));

        $this->postStore($this->couponInput(array(
            'id' => $coupon->id,
            'start_date' => '03/05/2016 05:39 PM',
            'end_date' => '02/05/2016 05:39 PM'
        )));

        $this->assertRedirectedTo('/admin/coupons/edit/' . $coupon->id);
        $this->assertErrorMessage('dateRangeError', 'Please enter an End date later than Start date.');
        $this->assertSame(1, Coupon::count());
        $this->assertSame('EXISTING', Coupon::find($coupon->id)->code);
    }

    /**
     * Test (Fail): spentRangeError beats applyToError
     */
    public function testCharacterizationSpentRangeErrorBeatsApplyTo()
    {
        $this->postStore($this->couponInput(array(
            'max_spent' => '10.00',
            'min_spent' => '20.00'
        ), array('category_id')));

        $this->assertRedirectedTo('/admin/coupons/create');
        $this->assertErrorMessage('spentRangeError', 'Max spent cannot be less than Min spent.');
        $this->assertFalse($this->defaultErrors()->has('applyToError'));
        $this->assertSame(0, Coupon::count());
    }

    /**
     * Test (Fail): no restricted-to models gives applyToError
     */
    public function testCharacterizationApplyToErrorWhenNoRestrictedModels()
    {
        $this->postStore($this->couponInput(array(), array('category_id')));

        $this->assertRedirectedTo('/admin/coupons/create');
        $this->assertErrorMessage(
            'applyToError',
            'You have not selected any Category, Product or Membership/Module under Restricted To.'
        );
        $this->assertSame(0, Coupon::count());
    }

    /**
     * Test (Fail): selected ids that no longer exist give the same applyToError
     */
    public function testCharacterizationApplyToErrorWhenAllSelectedIdsMissing()
    {
        $this->postStore($this->couponInput(array(
            'category_id' => array(999991),
            'product_id' => array(999992),
            'pricelist_id' => array(999993),
            'bundle_id' => array(999994)
        )));

        $this->assertRedirectedTo('/admin/coupons/create');
        $this->assertErrorMessage(
            'applyToError',
            'You have not selected any Category, Product or Membership/Module under Restricted To.'
        );
        $this->assertSame(0, Coupon::count());
    }

    /**
     * Test (Pass): zero max_spent, min_spent and usage limits are stored as null
     */
    public function testCharacterizationZeroSpentAndLimitsStoreNull()
    {
        $this->postStore($this->couponInput(array(
            'max_spent' => '0',
            'min_spent' => '0',
            'usage_limit_per_coupon' => '0',
            'usage_limit_per_user' => '0'
        )));

        $this->assertRedirectedTo('/admin/coupons');
        $this->assertSame(1, Coupon::count());

        $coupon = Coupon::first();
        $this->assertNull($coupon->max_spent);
        $this->assertNull($coupon->min_spent);
        $this->assertNull($coupon->usage_limit_per_coupon);
        $this->assertNull($coupon->usage_limit_per_user);
    }

    /**
     * Test (Pass): checkbox columns are true for any non-empty string and false only for empty
     */
    public function testCharacterizationCheckboxTruthiness()
    {
        $this->postStore($this->couponInput(array(
            'is_percent' => '0',
            'multiple_coupons' => '',
            'exclude_sale_item' => '0',
            'automatically_apply' => ''
        )));

        $this->assertRedirectedTo('/admin/coupons');
        $coupon = Coupon::first();
        $this->assertSame(1, (int) $coupon->is_percent);
        $this->assertSame(0, (int) $coupon->multiple_coupons);
        $this->assertSame(1, (int) $coupon->exclude_sale_item);
        $this->assertSame(0, (int) $coupon->automatically_apply);
    }
}
