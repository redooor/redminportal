<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Classes\File;

class FileClassTest extends RedminTestCase
{
    private $path;
    private $files;
    
    public function __construct()
    {
        $this->path = __DIR__ . '/../dummy/';
        
        $this->files = [
            [
                'name' => 'foo113a.pdf',
                'type' => 'application/pdf',
                'size' => 11941,
                'duration' => null
            ],
            [
                'name' => 'foo113audio.m4a',
                'type' => 'audio/mp4',
                'size' => 225613,
                'duration' => '0:07'
            ],
            [
                'name' => 'foo113audio.mp3',
                'type' => 'audio/mpeg',
                'size' => 174923,
                'duration' => '0:07'
            ]
        ];
    }
    
    /**
     * Test (Pass): test getMimeType() get correct Mime Type
     */
    public function testGetMimeTypePass()
    {
        foreach ($this->files as $test) {
            $file = new File($this->path . $test['name']);
            
            $mimetype = $file->getMimeType();
            
            // Check Mime Type
            $this->assertTrue($mimetype == $test['type']);
        }
    }
    
    /**
     * Test (Pass): test getId3() get correct Mime Type
     */
    public function testGetId3Pass()
    {
        foreach ($this->files as $test) {
            $file = new File($this->path . $test['name']);
            
            $duration = $file->getPlaytime();
            $size = $file->getSize();
            
            // Check Mime Type
            $this->assertTrue($duration == $test['duration']);
            $this->assertTrue($size == $test['size']);
        }
    }
}
