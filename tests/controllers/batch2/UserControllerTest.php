<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\User;

class UserControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest, TraitSearcherControllerTest;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/users';
        $this->viewhas = array(
            'singular' => 'user',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => array(
                'first_name' => 'Peter',
                'last_name'  => 'Lim',
                'email'      => 'peter.lim@test.com',
                'password'   => 'Passw0rd-1234',
                'password_confirmation' => 'Passw0rd-1234',
                'role'       => 1,
                'activated'  => true,
                'permission-inherit' => 'admin.view,admin.users.view',
                'permission-allow'  => 'admin.create,admin.update',
                'permission-deny' => 'admin.delete,admin.users.delete'
            ),
            'edit' => array(
                'id'   => 2,
                'first_name' => 'Peter',
                'last_name'  => 'Lim',
                'email'      => 'peter.lim@test.com',
                'password'   => 'Passw0rd-1234',
                'password_confirmation' => 'Passw0rd-1234',
                'role'       => 1,
                'activated'  => true,
                'permission-inherit' => 'admin.view,admin.groups.view',
                'permission-allow'  => 'admin.create,admin.update',
                'permission-deny' => 'admin.delete,admin.groups.delete'
            )
        );
        
        // For testing sort
        $this->sortBy = 'email';
        
        // For testing search
        $this->searchable_field = 'name';
        $this->search_text = 'user';
    }
    
    /**
     * Test (Fail): access getEdit, return 404.
     * Override BaseControllerTest method because Seeder already created 1 user
     */
    public function testEditFail404()
    {
        $this->call('GET', $this->page . '/edit/2');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access getDelete with id = 2
     * Override BaseControllerTest method, cannot delete self, must delete another user
     */
    public function testDeletePass()
    {
        $this->testStoreCreatePass();
        
        $this->call('POST', $this->page . '/delete/2');

        $this->assertRedirectedTo('/'); // Redirected to previous page, which is root
    }
    
    /**
     * Test (Fail): access getDelete with id = 2
     * Override BaseControllerTest method because Seeder already created 1 user
     */
    public function testDeleteFailNoRecord()
    {
        $this->call('POST', $this->page . '/delete/2');

        $this->assertRedirectedTo('/');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access getDelete with id = 1
     * Cannot delete own account while logged in
     */
    public function testDeleteFailDeleteSelf()
    {
        $this->call('POST', $this->page . '/delete/1');

        $this->assertRedirectedTo('/');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access getActivate with id = 2
     */
    public function testActivateFail()
    {
        $this->call('POST', $this->page . '/activate/2');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access getActivate with id = 1
     */
    public function testActivatePassActivateSelf()
    {
        $this->call('POST', $this->page . '/activate/1');
        
        $this->assertRedirectedTo('/'); // Redirected to previous page, which is root
    }
    
    /**
     * Test (Pass): access getActivate with id = 2
     */
    public function testActivatePassActivateOther()
    {
        // Creates 2nd record
        $this->testStoreCreatePass();
        
        // Then try activate it
        $this->call('POST', $this->page . '/activate/2');
        
        $this->assertRedirectedTo('/'); // Redirected to previous page, which is root
    }
    
    /**
     * Test (Fail): access getDeactivate with id = 2
     */
    public function testDeactivateFailNoRecord()
    {
        $this->call('POST', $this->page . '/deactivate/2');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access getDeactivate with id = 1
     * Cannot deactivate own account while logged in
     */
    public function testDeactivateFailDeactivateSelf()
    {
        $this->call('POST', $this->page . '/deactivate/1');
        
        $this->assertResponseStatus(302); // Redirected
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access getDeactivate with id = 2
     */
    public function testDeactivatePass()
    {
        // Creates 2nd record
        $this->testStoreCreatePass();
        
        // Then try deactivate it
        $this->call('POST', $this->page . '/deactivate/2');
        
        $this->assertResponseStatus(302); // Redirected
        $this->assertRedirectedTo('/'); // Redirected to previous page, which is root
    }

    /**
     * Test (Fail): access postStore create with a 9-character password
     */
    public function testStoreCreateFailPasswordTooShort()
    {
        Config::set('redminportal::password.min_length', 10);

        $input = $this->input['create'];
        $input['password'] = 'Passw0rd1';
        $input['password_confirmation'] = 'Passw0rd1';

        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page . '/create');
        $this->assertSessionHasErrors('password');
    }

    /**
     * Test (Pass): access postStore create with a 10-character password
     */
    public function testStoreCreatePassTenCharacterPassword()
    {
        $input = $this->input['create'];
        $input['password'] = 'Passw0rd12';
        $input['password_confirmation'] = 'Passw0rd12';

        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page);
        $this->assertNotNull(User::where('email', 'peter.lim@test.com')->first());
    }

    /**
     * Test (Fail): access postStore edit with a 9-character password
     */
    public function testStoreEditFailPasswordTooShort()
    {
        Config::set('redminportal::password.min_length', 10);

        $this->testStoreCreatePass();

        $input = $this->input['edit'];
        $input['password'] = 'Passw0rd1';
        $input['password_confirmation'] = 'Passw0rd1';

        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page . '/edit/2');
        $this->assertSessionHasErrors('password');
    }

    /**
     * Test (Pass): access postStore edit with a 10-character password
     */
    public function testStoreEditPassTenCharacterPassword()
    {
        $this->testStoreCreatePass();

        $input = $this->input['edit'];
        $input['password'] = 'Passw0rd12';
        $input['password_confirmation'] = 'Passw0rd12';

        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page);
        $this->assertTrue(Hash::check('Passw0rd12', User::find(2)->password));
    }

    /**
     * Test (Pass): minimum password length follows the config value
     */
    public function testPasswordMinLengthFollowsConfig()
    {
        Config::set('redminportal::password.min_length', 12);

        $input = $this->input['create'];
        $input['password'] = 'Passw0rd123';
        $input['password_confirmation'] = 'Passw0rd123';

        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page . '/create');
        $this->assertSessionHasErrors('password');

        $input['password'] = 'Passw0rd1234';
        $input['password_confirmation'] = 'Passw0rd1234';

        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page);
    }
}
