<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Tag;
use Redooor\Redminportal\App\Models\User;

class TagApiTest extends RedminBrowserTestCase
{
    protected $tagnames;

    /**
     * Setup initial data for use in tests
     */
    public function setup(): void
    {
        parent::setup();

        $this->seed('RedminSeeder');

        $this->tagnames = ['tag1', 'tag2', 'tag3'];

        // Add multiple tags
        foreach ($this->tagnames as $tagname) {
            $tag = new Tag;
            $tag->name = $tagname;
            $tag->save();
        }
    }

    /**
     * Create a signed-in user without any administration permissions.
     */
    private function loginWithoutPermissions($activated = 1)
    {
        $user = new User;
        $user->email = 'tagger@test.com';
        $user->password = Hash::make('test');
        $user->first_name = 'Tag';
        $user->last_name = 'User';
        $user->activated = $activated;
        $user->save();

        Auth::guard('redminguard')->loginUsingId($user->id);
    }

    /**
     * Test (Pass): access /api should redirect
     */
    public function testRootRedirect()
    {
        $this->call('GET', '/api');
        $this->assertResponseStatus(302);
    }

    /**
     * Test (Fail): access /api/tag without Authentication
     */
    public function testIndexWithoutAuth()
    {
        $this->call('GET', '/api/tag');

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('login');
    }

    /**
     * Test (Fail): a deactivated user cannot read the tags
     */
    public function testIndexWithDeactivatedUser()
    {
        $this->loginWithoutPermissions(0);

        $this->call('GET', '/api/tag');

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('login');
        $this->assertTrue(Auth::guard('redminguard')->guest());
    }

    /**
     * Test (Pass): access /api/tag with Authentication
     */
    public function testIndexWithAuth()
    {
        $this->loginWithoutPermissions();

        $response = $this->call('GET', '/api/tag');
        $this->assertResponseOk();

        $input = $response->getContent();

        $output = '{"1":"tag1","2":"tag2","3":"tag3"}';
        $this->assertTrue($input == $output);
    }

    /**
     * Test (Fail): access /api/tag/name without Authentication
     */
    public function testGetNameWithoutAuth()
    {
        $this->call('GET', '/api/tag/name');

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('login');
    }

    /**
     * Test (Pass): access /api/tag/name with Authentication
     */
    public function testGetNameWithAuth()
    {
        $this->loginWithoutPermissions();

        $response = $this->call('GET', '/api/tag/name');
        $this->assertResponseOk();

        $input = $response->getContent();

        $output = '["tag1","tag2","tag3"]';
        $this->assertTrue($input == $output);
    }
}
