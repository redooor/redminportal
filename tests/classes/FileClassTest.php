<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Classes\File;

class FileClassTest extends RedminTestCase
{
    private $path = __DIR__ . '/../dummy/';
    private $files = [
        [
            'name' => 'foo113a.pdf',
            'type' => 'application/pdf',
            'size' => 11941,
            'duration' => null
        ],
        [
            'name' => 'foo113audio.m4a',
            'type' => 'audio/x-m4a',
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
    
    /**
     * Test (Pass): test getMimeType() get correct Mime Type for .pdf
     */
    public function testGetMimeTypePassPdf()
    {
        $this->runSubtestGetMimeType($this->files[0]);
    }
    
    /**
     * Test (Pass): test getMimeType() get correct Mime Type for .m4a
     *
     * PHP7 returns "audio/x-m4a" as MimeType
     * PHP5 returns "audio/mp4" as MimeType
     * Don't test this for now. Inconsistency due to external plugin.
     */
   public function testGetMimeTypePassM4a()
   {
       $this->runSubtestGetMimeType($this->files[1]);
   }
    
    /**
     * Test (Pass): test getMimeType() get correct Mime Type for .mp3
     */
    public function testGetMimeTypePassMp3()
    {
        $this->runSubtestGetMimeType($this->files[2]);
    }
    
    /**
     * Test (Pass): test getId3() get correct Mime Type for .pdf
     */
    public function testGetId3PassPdf()
    {
        $this->runSubtestGetId3($this->files[0]);
    }
    
    /**
     * Test (Pass): test getId3() get correct Mime Type for .m4a
     */
    public function testGetId3PassM4a()
    {
        $this->runSubtestGetId3($this->files[1]);
    }
    
    /**
     * Test (Pass): test getId3() get correct Mime Type for .mp3
     */
    public function testGetId3PassMp3()
    {
        $this->runSubtestGetId3($this->files[2]);
    }
    
    /**
     * Test (Pass): deleting a folder only removes its own contents
     */
    public function testDeleteFilesOnlyRemovesTargetFolder()
    {
        $base = sys_get_temp_dir() . '/redmin_file_delete_' . uniqid();
        $target = $base . '/12';
        $sibling = $base . '/120';

        mkdir($target . '/nested', 0777, true);
        mkdir($sibling, 0777, true);
        file_put_contents($target . '/old.mp3', 'old');
        file_put_contents($target . '/nested/nested.mp3', 'nested');
        file_put_contents($sibling . '/keep.mp3', 'keep');

        try {
            $file = new File($this->path . $this->files[0]['name']);
            $file->deleteFiles($target);

            $this->assertDirectoryDoesNotExist($target);
            $this->assertDirectoryExists($sibling);
            $this->assertFileExists($sibling . '/keep.mp3');
        } finally {
            @unlink($sibling . '/keep.mp3');
            @rmdir($sibling);
            @unlink($target . '/nested/nested.mp3');
            @unlink($target . '/old.mp3');
            @rmdir($target . '/nested');
            @rmdir($target);
            @rmdir($base);
        }
    }

    /**
     * Get Mime Type
     **/
    private function runSubtestGetMimeType($test)
    {
        $file = new File($this->path . $test['name']);
        
        $mimetype = $file->getMimeType();

        // Check Mime Type
        $this->assertTrue($mimetype == $test['type'], 'Type mismatched: ' . $mimetype . ' vs ' . $test['type']);
    }
    
    /**
     * Get Id3 and Playtime
     **/
    private function runSubtestGetId3($test)
    {
        $file = new File($this->path . $test['name']);
        
        $duration = $file->getPlaytime();
        $size = $file->getSize();

        // Check Mime Type
        $this->assertTrue($duration == $test['duration'], 'Duration mismatched.');
        $this->assertTrue($size == $test['size'], 'Size mismatched.');
    }
}
