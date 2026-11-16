<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Membership;

/**
 * Covers the integer and minimum rules on membership rank.
 */
class MembershipRankValidationFixTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/memberships';
        $this->viewhas = array(
            'singular' => 'membership',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => $this->membershipInput(),
            'edit' => $this->membershipInput(array(
                'id' => 1,
                'name' => 'Edited',
                'rank' => '2'
            ))
        );
    }

    /**
     * Test (Fail): a non-numeric rank is rejected with the default integer message
     */
    public function testNonNumericRankIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/memberships/create',
            $this->membershipInput(array('rank' => 'abc')),
            array('rank' => 'The rank field must be an integer.')
        );

        $this->assertOldInput(array('rank' => 'abc'));
        $this->assertSame(0, Membership::count());
    }

    /**
     * Test (Fail): a negative rank is rejected with the default numeric minimum message
     */
    public function testNegativeRankIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/memberships/create',
            $this->membershipInput(array('name' => 'Negative rank', 'rank' => '-5')),
            array('rank' => 'The rank field must be at least 0.')
        );

        $this->assertOldInput(array('rank' => '-5'));
        $this->assertSame(0, Membership::count());
    }

    /**
     * Test (Pass): zero and three are accepted and stored
     */
    public function testZeroAndThreeRanksAreAccepted()
    {
        $this->postStore($this->membershipInput(array('name' => 'Zero rank', 'rank' => '0')));

        $this->assertRedirectedTo('/admin/memberships');
        $this->assertSame(1, Membership::count());
        $this->assertSame(0, (int) Membership::first()->rank);

        $this->postStore($this->membershipInput(array('name' => 'Three rank', 'rank' => '3')));

        $this->assertRedirectedTo('/admin/memberships');
        $this->assertSame(2, Membership::count());
        $this->assertSame(3, (int) Membership::where('name', 'Three rank')->first()->rank);
    }

    /**
     * Test (Fail): an empty rank is still rejected by the required rule
     */
    public function testEmptyRankIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/memberships/create',
            $this->membershipInput(array('rank' => '')),
            array('rank' => 'The rank field is required.')
        );

        $this->assertSame(0, Membership::count());
    }

    /**
     * Test (Pass): an unchanged valid existing rank still passes on edit
     */
    public function testUnchangedRankIsAcceptedOnEdit()
    {
        $membership = $this->createModel(Membership::class, array(
            'name' => 'Existing rank',
            'rank' => 3
        ));

        $this->postStore($this->membershipInput(array(
            'id' => $membership->id,
            'name' => 'Existing rank',
            'rank' => '3'
        )));

        $this->assertRedirectedTo('/admin/memberships');
        $this->assertSame(1, Membership::count());
        $this->assertSame(3, (int) Membership::find($membership->id)->rank);
    }

    /**
     * A valid membership payload, with optional overrides
     */
    private function membershipInput(array $overrides = array())
    {
        return array_merge(array(
            'name' => 'Gold',
            'rank' => '1'
        ), $overrides);
    }
}
