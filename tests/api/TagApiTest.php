<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Redooor\Redminportal\App\Models\Tag;

class TagApiTest extends RedminBrowserTestCase
{
    protected $tagnames;
    
    /**
     * Setup initial data for use in tests
     */
    public function setup(): void
    {
        parent::setup();

        $this->tagnames = ['tag1', 'tag2', 'tag3'];
        
        // Add multiple tags
        foreach ($this->tagnames as $tagname) {
            $tag = new Tag;
            $tag->name = $tagname;
            $tag->save();
        }
    }
    
    /**
     * Test (Pass): access /api should redirect
     */
    public function testRootRedirect()
    {
        $response = $this->call('GET', '/api');
        $this->assertResponseStatus(302);
    }

    /**
     * Test (Pass): access /api/tag without Authentication
     */
    public function testIndexWithoutAuth()
    {
        $response = $this->call('GET', '/api/tag');
        $this->assertResponseOk();
        
        $input = $response->getContent();
        
        $output = '{"1":"tag1","2":"tag2","3":"tag3"}';
        $this->assertTrue($input == $output);
    }

    /**
     * Test (Pass): access /api/tag with Authentication
     */
    public function testIndexWithAuth()
    {
        Auth::loginUsingId(1); // Fake admin authentication

        $response = $this->call('GET', '/api/tag');
        $this->assertResponseOk();
        
        $input = $response->getContent();
        
        $output = '{"1":"tag1","2":"tag2","3":"tag3"}';
        $this->assertTrue($input == $output);
    }
    
    /**
     * Test (Pass): access /api/tag/name without Authentication
     */
    public function testGetNameWithoutAuth()
    {
        $response = $this->call('GET', '/api/tag/name');
        $this->assertResponseOk();
        
        $input = $response->getContent();
        
        $output = '["tag1","tag2","tag3"]';
        $this->assertTrue($input == $output);
    }

    /**
     * Test (Pass): access /api/tag/name with Authentication
     */
    public function testGetNameWithAuth()
    {
        Auth::loginUsingId(1); // Fake admin authentication
        
        $response = $this->call('GET', '/api/tag/name');
        $this->assertResponseOk();
        
        $input = $response->getContent();
        
        $output = '["tag1","tag2","tag3"]';
        $this->assertTrue($input == $output);
    }
}
