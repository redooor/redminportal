<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Media;

class MediaControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest;
    
    private $path;
    private $files;
    
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/medias';
        $viewhas = array(
            'singular' => 'media',
            'plural' => 'models'
        );
        $input = array(
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
                'type' => 'audio/mp4',
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
        
        parent::__construct($page, $viewhas, $input);
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        parent::__destruct();
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
//    public function testUploadFilePassM4a()
//    {
//        $this->testStoreCreatePass(); // Create for upload
//        
//        $this->runSubtestUpload($this->files[1]);
//    }
    
    /**
     * Test (Pass): upload file pass for .mp3
     */
    public function testUploadFilePassMp3()
    {
        $this->testStoreCreatePass(); // Create for upload
        
        $this->runSubtestUpload($this->files[2]);
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
