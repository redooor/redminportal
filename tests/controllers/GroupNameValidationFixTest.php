<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Group;

/**
 * Covers the unique rule on group names.
 */
class GroupNameValidationFixTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/groups';
        $this->viewhas = array(
            'singular' => 'group',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => $this->groupInput(),
            'edit' => $this->groupInput(array(
                'id' => 3,
                'name' => 'Edited Group'
            ))
        );
    }

    /**
     * The seeded Admin group has id 1, so use a truly missing id.
     */
    public function testEditFail404()
    {
        $this->call('GET', $this->page . '/edit/999');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }

    /**
     * Test (Pass): a unique name is accepted and saved
     */
    public function testUniqueNameIsAcceptedOnCreate()
    {
        $this->postStore($this->groupInput(array('name' => 'Fresh Group')));

        $this->assertRedirectedTo('/admin/groups');
        $this->assertSame(3, Group::count());
        $this->assertNotNull(Group::where('name', 'Fresh Group')->first());
    }

    /**
     * Test (Fail): a duplicate name is rejected on create with the default unique message
     */
    public function testDuplicateNameIsRejectedOnCreate()
    {
        $this->assertValidationFailure(
            '/admin/groups/create',
            $this->groupInput(array('name' => 'Admin')),
            array('name' => 'The name has already been taken.')
        );

        $this->assertOldInput(array('name' => 'Admin'));
        $this->assertSame(2, Group::count());
    }

    /**
     * Test (Fail): another group's name is rejected on update with the default unique message
     */
    public function testDuplicateOtherGroupNameIsRejectedOnUpdate()
    {
        $first = $this->createGroup('First Group');
        $this->createGroup('Second Group');

        $this->assertValidationFailure(
            '/admin/groups/edit/' . $first->id,
            $this->groupInput(array(
                'id' => $first->id,
                'name' => 'Second Group'
            )),
            array('name' => 'The name has already been taken.')
        );

        $this->assertOldInput(array('name' => 'Second Group'));
        $this->assertSame('First Group', $first->fresh()->name);
        $this->assertSame(4, Group::count());
    }

    /**
     * Test (Pass): an unchanged existing name still passes the unique rule on edit
     */
    public function testUnchangedNameIsAcceptedOnUpdate()
    {
        $group = $this->createGroup('Unchanged Group');

        $this->postStore($this->groupInput(array(
            'id' => $group->id,
            'name' => 'Unchanged Group'
        )));

        $this->assertRedirectedTo('/admin/groups');
        $this->assertSame('Unchanged Group', $group->fresh()->name);
        $this->assertSame(3, Group::count());
    }

    /**
     * A valid group payload, with optional overrides
     */
    private function groupInput(array $overrides = array())
    {
        return array_merge(array(
            'name' => 'Created Group',
            'permission-inherit' => '',
            'permission-allow' => '',
            'permission-deny' => ''
        ), $overrides);
    }

    /**
     * Create a saved group fixture
     */
    private function createGroup($name)
    {
        return $this->createModel(Group::class, array(
            'name' => $name,
            'permissions' => json_encode(array())
        ));
    }
}
