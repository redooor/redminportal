<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Tag;

class TagApiTest extends RedminTestCase
{
    protected $tagnames;
    
    public function __construct()
    {
        $this->tagnames = ['tag1', 'tag2', 'tag3'];
    }
    /**
     * Setup initial data for use in tests
     */
    public function setup(): void
    {
        parent::setup();
        
        // Add multiple tags
        foreach ($this->tagnames as $tagname) {
            $tag = new Tag;
            $tag->name = $tagname;
            $tag->save();
        }
    }
    
    /**
     * Test (Pass): access /api/tag
     */
    public function testIndex()
    {
        $response = $this->call('GET', '/api/tag');
        $this->assertResponseOk();
        
        $input = $response->getContent();
        
        $output = '{"1":"tag1","2":"tag2","3":"tag3"}';
        $this->assertTrue($input == $output);
    }
    
    /**
     * Test (Pass): access /api/tag/name
     */
    public function testGetName()
    {
        $response = $this->call('GET', '/api/tag/name');
        $this->assertResponseOk();
        
        $input = $response->getContent();
        
        $output = '["tag1","tag2","tag3"]';
        $this->assertTrue($input == $output);
    }
}
