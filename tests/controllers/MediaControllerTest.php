<?php namespace Redooor\Redminportal\Test;

class MediaControllerTest extends BaseControllerTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/medias';
        $viewhas = array(
            'singular' => 'media',
            'plural' => 'medias'
        );
        $input = array(
            'create' => array(
                'name'                  => 'This is title',
                'short_description'     => 'This is body',
                'cn_name'               => 'CN name',
                'cn_short_description'  => 'CN short body',
                'category_id'           => 1,
                'sku'                   => 'UNIQUESKU001'
            ),
            'edit' => array(
                'id'        => 1,
                'name'                  => 'This is title',
                'short_description'     => 'This is body',
                'cn_name'               => 'CN name',
                'cn_short_description'  => 'CN short body',
                'category_id'           => 1,
                'sku'                   => 'UNIQUESKU001'
            )
        );
        
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
/*
    public function testUploadFileSuccess()
    {
        $this->testStoreCreatePass(); // Create for upload
        
        $path = __DIR__ . '/../dummy/';
        $filename = 'foo113a.pdf';
        $mimeType = 'application/pdf';

        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile(
            $path . $filename,
            $filename,
            $mimeType
        );

        $input = array(
            'name' => 'This is title'
        );

        $this->call('POST', '/admin/medias/upload/1', $input, array('file' => $file));

        $this->assertRedirectedTo('/admin/medias');
    }*/
}
