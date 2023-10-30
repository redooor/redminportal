<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Media;

class MediaControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest, TraitImageControllerTest;
    
    private $path;
    private $files;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/medias';
        $this->viewhas = array(
            'singular' => 'media',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => array(
                'name'                  => 'This is title',
                'short_description'     => 'This is body',
                'cn_name'               => 'CN name',
                'cn_short_description'  => 'CN short body',
                'category_id'           => 1,
                'sku'                   => 'UNIQUESKU001',
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
            ),
            'edit' => array(
                'id'        => 1,
                'name'                  => 'This is title',
                'short_description'     => 'This is body',
                'cn_name'               => 'CN name',
                'cn_short_description'  => 'CN short body',
                'category_id'           => 1,
                'sku'                   => 'UNIQUESKU001',
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
            )
        );
        
        // For testing sort
        $this->sortBy = 'created_at';

        // For testing image
        $this->img_parent_model = new Media;
        $this->img_parent_create = [
            'name' => 'This is the title',
            'path' => 'path/to/somewhere',
            'sku' => 'UNIQUESKU001',
            'short_description' => 'This is the body',
            'category_id' => 1,
            'active' => true
        ];
        
        $this->path = __DIR__ . '/../../dummy/';
        
        $this->files = [
            [
                'name' => 'foo113a.pdf',
                'type' => 'application/pdf',
                'size' => 11941,
                'duration' => '{"duration":""}'
            ],
            [
                'name' => 'foo113audio.m4a',
                'type' => 'audio/x-m4a',
                'size' => 225613,
                'duration' => '{"duration":"0:07"}'
            ],
            [
                'name' => 'foo113audio.mp3',
                'type' => 'audio/mpeg',
                'size' => 174923,
                'duration' => '{"duration":"0:07"}'
            ]
        ];
    }
    
    /**
     * Test (Fail): access postStore with no name
     */
    public function testStoreCreateFailsNameBlank()
    {
        $input = array(
            'name'                  => '',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001'
        );

        $this->call('POST', '/admin/medias/store', $input);

        $this->assertRedirectedTo('/admin/medias/create');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): upload file pass for .pdf
     */
    public function testUploadFilePassPdf()
    {
        $this->testStoreCreatePass(); // Create for upload
        
        $this->runSubtestUpload($this->files[0]);
    }
    
    /**
     * Test (Pass): upload file pass for .m4a
     *
     * PHP7 returns "audio/x-m4a" as MimeType
     * PHP5 returns "audio/mp4" as MimeType
     * Don't test this for now. Inconsistency due to external plugin.
     */
   public function testUploadFilePassM4a()
   {
       $this->testStoreCreatePass(); // Create for upload
       
       $this->runSubtestUpload($this->files[1]);
   }
    
    /**
     * Test (Pass): upload file pass for .mp3
     */
    public function testUploadFilePassMp3()
    {
        $this->testStoreCreatePass(); // Create for upload
        
        $this->runSubtestUpload($this->files[2]);

        $this->call('GET', '/admin/medias/duration/1');
        $this->assertResponseOk();
    }

    /**
     * Test (Pass): check duration of .mp3
     */
    public function testDurationPass()
    {
        $this->testStoreCreatePass(); // Create for upload
        
        $this->runSubtestUpload($this->files[2]);

        $response = $this->call('GET', '/admin/medias/duration/1');
        $this->assertResponseOk();
        $result = json_decode($response->content());
        $this->assertTrue($result->status == 'success');
    }

    /**
     * Test (Fail): check duration of .mp3
     */
    public function testDurationFails()
    {
        $response = $this->call('GET', '/admin/medias/duration/1');
        $this->assertResponseOk();
        $result = json_decode($response->content());
        $this->assertTrue($result->status == 'error');
    }
    
    /**
     * Run upload test
     **/
    private function runSubtestUpload($item)
    {
        // Test Upload
        $_FILES = array(
            'file' => array(
                'name' => $item['name'],
                'type' => $item['type'],
                'size' => $item['size'],
                'tmp_name' => $this->path . $item['name'],
                'error' => 0,
                'mock' => true,
                'pathinfo' => $this->path
            )
        );

        $input = array(
            'name' => 'This is title'
        );

        $this->call('POST', '/admin/medias/upload/1', $input);

        $media = Media::find('1');
        $this->assertTrue($media->path == $item['name']);
        $this->assertTrue($media->mimetype == $item['type']);
        $this->assertTrue($media->options == $item['duration']);

        unset($_FILES);
    }
}
