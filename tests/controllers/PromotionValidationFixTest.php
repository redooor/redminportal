<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Promotion;

/**
 * Covers the tightened promotion date validation.
 */
class PromotionValidationFixTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/promotions';
        $this->viewhas = array(
            'singular' => 'promotion',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => $this->promotionInput(),
            'edit' => $this->promotionInput(array('id' => 1))
        );
    }

    /**
     * Test (Pass): valid d/m/Y dates are accepted
     */
    public function testValidDatesAreAccepted()
    {
        $this->postStore($this->promotionInput());

        $this->assertRedirectedTo('/admin/promotions');
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(1, Promotion::count());

        $promotion = Promotion::first();
        $this->assertStringStartsWith('2016-02-29', $promotion->start_date);
        $this->assertStringStartsWith('2016-03-01', $promotion->end_date);
    }

    /**
     * Test (Pass): the leap day 29/02/2016 is a valid calendar date
     */
    public function testLeapDayIsAccepted()
    {
        $this->postStore($this->promotionInput(array(
            'start_date' => '29/02/2016',
            'end_date' => '29/02/2016'
        )));

        $this->assertRedirectedTo('/admin/promotions');
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(1, Promotion::count());
        $this->assertStringStartsWith('2016-02-29', Promotion::first()->start_date);
    }

    /**
     * Test (Fail): 29/02/2015 is not a real calendar date
     */
    public function testInvalidLeapDayIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            $this->promotionInput(array('start_date' => '29/02/2015')),
            array('start_date' => 'The start date field must match the format d/m/Y.')
        );

        $this->assertSame(0, Promotion::count());
    }

    /**
     * Test (Fail): an ISO date does not match the d/m/Y format
     */
    public function testIsoDateIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            $this->promotionInput(array('start_date' => '2016-03-01')),
            array('start_date' => 'The start date field must match the format d/m/Y.')
        );

        $this->assertSame(0, Promotion::count());
    }

    /**
     * Test (Pass): 05/06/2016 is parsed day first as 5 June 2016
     */
    public function testDateIsParsedDayFirst()
    {
        $this->postStore($this->promotionInput(array(
            'start_date' => '05/06/2016',
            'end_date' => '06/06/2016'
        )));

        $this->assertRedirectedTo('/admin/promotions');
        $this->assertSame(1, Promotion::count());

        $promotion = Promotion::first();
        $this->assertStringStartsWith('2016-06-05', $promotion->start_date);
        $this->assertStringStartsWith('2016-06-06', $promotion->end_date);
    }

    /**
     * Test (Pass): an end date equal to the start date is allowed
     */
    public function testEqualStartAndEndAreAccepted()
    {
        $this->postStore($this->promotionInput(array(
            'start_date' => '01/03/2016',
            'end_date' => '01/03/2016'
        )));

        $this->assertRedirectedTo('/admin/promotions');
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(1, Promotion::count());
        $this->assertStringStartsWith('2016-03-01', Promotion::first()->end_date);
    }

    /**
     * Test (Fail): an end date before the start date is rejected
     */
    public function testEndBeforeStartIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            $this->promotionInput(array(
                'start_date' => '01/03/2016',
                'end_date' => '29/02/2016'
            )),
            array('end_date' => 'The end date field must be a date after or equal to start date.')
        );

        $this->assertSame(0, Promotion::count());
    }

    /**
     * Test (Fail): an unparseable start date is rejected and creates no row
     */
    public function testUnparseableStartDateIsRejectedAndCreatesNoRow()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            $this->promotionInput(array('start_date' => 'abc')),
            array('start_date' => 'The start date field must match the format d/m/Y.')
        );

        $this->assertSame(0, Promotion::count());
    }

    /**
     * Test (Pass): an edit with unchanged valid existing dates still passes
     */
    public function testEditWithUnchangedValidDatesIsAccepted()
    {
        $promotion = $this->createModel(Promotion::class, array(
            'name' => 'Existing promotion',
            'short_description' => 'Existing short',
            'long_description' => 'Existing long',
            'start_date' => '2016-02-29 00:00:00',
            'end_date' => '2016-03-01 00:00:00'
        ));

        $this->postStore($this->promotionInput(array(
            'id' => $promotion->id,
            'name' => 'Updated promotion',
            'start_date' => '29/02/2016',
            'end_date' => '01/03/2016'
        )));

        $this->assertRedirectedTo('/admin/promotions');
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(1, Promotion::count());

        $promotion = Promotion::find($promotion->id);
        $this->assertSame('Updated promotion', $promotion->name);
        $this->assertStringStartsWith('2016-02-29', $promotion->start_date);
        $this->assertStringStartsWith('2016-03-01', $promotion->end_date);
    }

    /**
     * Build a valid promotion input, optionally overridden or with keys removed
     */
    private function promotionInput(array $overrides = array(), array $remove = array())
    {
        $input = array_merge(array(
            'name' => 'Promotion name',
            'short_description' => 'Promotion short',
            'long_description' => 'Promotion long',
            'start_date' => '29/02/2016',
            'end_date' => '01/03/2016',
            'active' => '1'
        ), $overrides);

        foreach ($remove as $key) {
            unset($input[$key]);
        }

        return $input;
    }
}
