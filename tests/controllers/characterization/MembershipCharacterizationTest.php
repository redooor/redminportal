<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Media;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\ModuleMediaMembership;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class MembershipCharacterizationTest extends BaseControllerTest
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
            'edit' => $this->membershipInput(array('id' => 1))
        );
    }

    /**
     * A valid membership payload, with optional overrides and omitted keys
     */
    private function membershipInput(array $overrides = array(), array $without = array())
    {
        $input = array_merge(array(
            'name' => 'Gold',
            'rank' => '1'
        ), $overrides);

        foreach ($without as $key) {
            unset($input[$key]);
        }

        return $input;
    }

    /**
     * Test (Pass): create then update keeps one row and saves the main fields
     */
    public function testCharacterizationCreateThenUpdate()
    {
        $this->postStore($this->membershipInput(array(
            'name' => 'Original name',
            'rank' => '3'
        )));

        $this->assertRedirectedTo('/admin/memberships');
        $this->assertSame(1, Membership::count());

        $membership = Membership::first();
        $this->assertSame('Original name', $membership->name);
        $this->assertSame(3, (int) $membership->rank);

        $this->postStore($this->membershipInput(array(
            'id' => $membership->id,
            'name' => 'Updated name',
            'rank' => '0'
        )));

        $this->assertRedirectedTo('/admin/memberships');
        $this->assertSame(1, Membership::count());

        $membership = Membership::find($membership->id);
        $this->assertSame('Updated name', $membership->name);
        $this->assertSame(0, (int) $membership->rank);
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/memberships/create',
            $this->membershipInput(array(), array('name')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with a nonexistent id redirects to edit/5 before the not-found check
     */
    public function testCharacterizationValidationRedirectWhenIdIsFive()
    {
        $this->assertValidationFailure(
            '/admin/memberships/edit/5',
            $this->membershipInput(array('id' => '5'), array('name')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an empty id is converted to null by the HTTP middleware, so it redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsEmptyString()
    {
        $this->assertValidationFailure(
            '/admin/memberships/create',
            $this->membershipInput(array('id' => ''), array('name')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an id of zero is present and redirects to edit/0
     */
    public function testCharacterizationValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/memberships/edit/0',
            $this->membershipInput(array('id' => '0'), array('name')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $membership = $this->createModel(Membership::class, array(
            'name' => 'Existing',
            'rank' => 1
        ));

        $this->assertValidationFailure(
            '/admin/memberships/edit/' . $membership->id,
            $this->membershipInput(array('id' => $membership->id), array('name')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): valid input with a nonexistent id gives the editError not-found message
     */
    public function testCharacterizationNonexistentIdWithValidFields()
    {
        $this->postStore($this->membershipInput(array('id' => '5')));

        $this->assertRedirectedTo('/admin/memberships');
        $this->assertErrorMessage(
            'editError',
            'The membership cannot be found because it does not exist or may have been deleted.'
        );
        $this->assertSame(0, Membership::count());
    }

    /**
     * Test (Fail): the name field is required
     */
    public function testCharacterizationMissingName()
    {
        $this->assertValidationFailure(
            '/admin/memberships/create',
            $this->membershipInput(array(), array('name')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): the rank field is required when missing
     */
    public function testCharacterizationMissingRank()
    {
        $this->assertValidationFailure(
            '/admin/memberships/create',
            $this->membershipInput(array(), array('rank')),
            array('rank' => 'The rank field is required.')
        );
    }

    /**
     * Test (Fail): the rank field is required when empty
     */
    public function testCharacterizationEmptyRank()
    {
        $this->assertValidationFailure(
            '/admin/memberships/create',
            $this->membershipInput(array('rank' => '')),
            array('rank' => 'The rank field is required.')
        );
    }

    /**
     * Test (Fail): a negative rank fails min:0 with the default numeric message
     */
    public function testCharacterizationNegativeRankFailsMin()
    {
        $this->assertValidationFailure(
            '/admin/memberships/create',
            $this->membershipInput(array('name' => 'Negative rank', 'rank' => '-5')),
            array('rank' => 'The rank field must be at least 0.')
        );

        $this->assertSame(0, Membership::count());
    }

    /**
     * Test (Fail): a non-numeric rank fails the integer rule with the default message
     */
    public function testCharacterizationNonNumericRankFailsInteger()
    {
        $this->assertValidationFailure(
            '/admin/memberships/create',
            $this->membershipInput(array('name' => 'Text rank', 'rank' => 'abc')),
            array('rank' => 'The rank field must be an integer.')
        );

        $this->assertSame(0, Membership::count());
    }

    /**
     * Test (Pass): a rank of zero passes min:0 and is stored as zero
     */
    public function testCharacterizationZeroRankPassesMin()
    {
        $this->postStore($this->membershipInput(array('name' => 'Zero rank', 'rank' => '0')));

        $this->assertRedirectedTo('/admin/memberships');
        $this->assertSame(0, (int) Membership::first()->rank);
    }

    /**
     * Test (Fail): creating another row with an existing name fails unique with the exact message
     */
    public function testCharacterizationDuplicateNameOnCreate()
    {
        $this->createModel(Membership::class, array('name' => 'Taken', 'rank' => 1));

        $this->assertValidationFailure(
            '/admin/memberships/create',
            $this->membershipInput(array('name' => 'Taken')),
            array('name' => 'The name has already been taken.')
        );

        $this->assertSame(1, Membership::count());
    }

    /**
     * Test (Pass): an unchanged name on update passes unique and another row's name fails
     */
    public function testCharacterizationUniqueNameOnUpdate()
    {
        $first = $this->createModel(Membership::class, array('name' => 'First', 'rank' => 1));
        $this->createModel(Membership::class, array('name' => 'Second', 'rank' => 2));

        $this->postStore($this->membershipInput(array(
            'id' => $first->id,
            'name' => 'First',
            'rank' => '5'
        )));

        $this->assertRedirectedTo('/admin/memberships');
        $this->assertSame(5, (int) Membership::find($first->id)->rank);

        $this->postStore($this->membershipInput(array(
            'id' => $first->id,
            'name' => 'Second',
            'rank' => '6'
        )));

        $this->assertRedirectedTo('/admin/memberships/edit/' . $first->id);
        $this->assertErrorMessage('name', 'The name has already been taken.');
        $this->assertSame(5, (int) Membership::find($first->id)->rank);
        $this->assertSame(2, Membership::count());
    }

    /**
     * Test (Fail): old input keeps scalar values after a validation failure
     */
    public function testCharacterizationOldInput()
    {
        $this->assertValidationFailure(
            '/admin/memberships/create',
            $this->membershipInput(array('rank' => '-2'), array('name')),
            array('name' => 'The name field is required.')
        );

        $this->assertOldInput(array('rank' => '-2'));
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson($this->membershipInput(array('name' => '', '_error_bag' => 'custom')));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/memberships/create');
        $this->assertSessionHasErrors('name');
        $this->assertErrorMessage('name', 'The name field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }

    /**
     * Test (Fail): deleting a nonexistent membership redirects back with the exact deleteError
     */
    public function testCharacterizationDeleteMissing()
    {
        $this->from('/admin/memberships')->call('POST', '/admin/memberships/delete/999');

        $this->assertRedirectedTo('/admin/memberships');
        $this->assertErrorMessage(
            'deleteError',
            'The membership cannot be found. It could have already been deleted.'
        );
    }

    /**
     * Test (Fail): a membership used by ModuleMediaMembership cannot be deleted
     */
    public function testCharacterizationDeleteInUse()
    {
        $membership = $this->createModel(Membership::class, array('name' => 'In use', 'rank' => 1));
        $module = $this->createModel(Module::class, array(
            'name' => 'Delete module',
            'short_description' => 'Delete module short',
            'active' => true
        ));
        $media = $this->createModel(Media::class, array(
            'name' => 'Delete media',
            'path' => 'delete.pdf',
            'short_description' => 'Delete media short',
            'active' => true
        ));

        $link = new ModuleMediaMembership;
        $link->module_id = $module->id;
        $link->media_id = $media->id;
        $link->membership_id = $membership->id;
        $link->save();

        $this->from('/admin/memberships')->call('POST', '/admin/memberships/delete/' . $membership->id);

        $this->assertRedirectedTo('/admin/memberships');
        $this->assertErrorMessage(
            'deleteError',
            'The membership cannot be deleted because it is in used.'
        );
        $this->assertNotNull(Membership::find($membership->id));
        $this->assertNotNull(ModuleMediaMembership::find($link->id));
        $this->assertSame(1, Membership::count());
    }

    /**
     * Test (Pass): an unused membership is deleted and redirects back without errors
     */
    public function testCharacterizationDeleteSuccess()
    {
        $membership = $this->createModel(Membership::class, array('name' => 'Unused', 'rank' => 1));

        $this->from('/admin/memberships')->call('POST', '/admin/memberships/delete/' . $membership->id);

        $this->assertRedirectedTo('/admin/memberships');
        $this->assertNull(Membership::find($membership->id));
        $this->assertSame(0, Membership::count());
    }
}
