<?php namespace Redooor\Redminportal\Test;

use Database\Seeders\RedminSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Media;

class MediaUploadFilesTest extends RedminBrowserTestCase
{
    private $category;
    private $media;
    private $mediaFolder;
    private $tmpFolder;
    private $tempFiles = array();
    private $extraFolders = array();

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RedminSeeder::class);
        Auth::guard('redminguard')->loginUsingId(1);

        $this->category = new Category;
        $this->category->id = 800000 + random_int(1, 99999);
        $this->category->name = 'Upload Test ' . uniqid();
        $this->category->short_description = 'Upload test category';
        $this->category->active = true;
        $this->category->save();

        $this->media = new Media;
        $this->media->name = 'Upload Test ' . uniqid();
        $this->media->path = 'broken.pdf';
        $this->media->short_description = 'Upload test media';
        $this->media->category_id = $this->category->id;
        $this->media->save();

        $this->tmpFolder = public_path() . '/assets/medias/tmp/'
            . $this->category->id . '/' . $this->media->id;
        $this->mediaFolder = public_path() . '/assets/medias/'
            . $this->category->id . '/' . $this->media->id;
    }

    public function tearDown(): void
    {
        $_FILES = array();
        $_REQUEST = array();

        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        if ($this->tmpFolder) {
            $this->deleteFolder($this->tmpFolder);
            @rmdir(dirname($this->tmpFolder));
        }

        if ($this->mediaFolder) {
            $this->deleteFolder($this->mediaFolder);
            @rmdir(dirname($this->mediaFolder));
        }

        foreach ($this->extraFolders as $folder) {
            $this->deleteFolder($folder);
        }

        $this->tempFiles = array();
        $this->extraFolders = array();
        $this->media = null;
        $this->category = null;
        $this->tmpFolder = null;
        $this->mediaFolder = null;

        parent::tearDown();
    }

    /**
     * Test (Pass): traversal names are contained inside the model folder
     */
    public function testTraversalNameStaysInsideModelFolder()
    {
        $result = $this->upload('../../escape.mp3', '../../escape.mp3', 'ID3' . str_repeat('A', 64));

        $this->assertSame(1, $result['code'], $result['message']);
        $this->assertFileExists($this->mediaFolder . '/escape.mp3');
        $this->assertSame('escape.mp3', Media::find($this->media->id)->path);

        $this->assertFileDoesNotExist(public_path('assets/medias/escape.mp3'));
        $this->assertFileDoesNotExist(public_path('assets/escape.mp3'));
        $this->assertFileDoesNotExist(public_path('escape.mp3'));
    }

    /**
     * Test (Fail): an executable extension is rejected without writing a file
     */
    public function testForbiddenExtensionIsRejected()
    {
        $result = $this->upload('shell.php', 'shell.php', '<?php echo "not allowed";');

        $this->assertSame(0, $result['code']);
        $this->assertSame('This file type is not allowed.', $result['message']);

        $this->assertFileDoesNotExist($this->tmpFolder . '/shell.php');
        $this->assertFileDoesNotExist($this->tmpFolder . '/shell.php.part');
        $this->assertFileDoesNotExist($this->mediaFolder . '/shell.php');
    }

    /**
     * Test (Pass): two chunks are assembled into the final file
     */
    public function testTwoChunkUploadAssemblesFile()
    {
        $this->prepareUpload('chunked.mp3', 'chunked.mp3', 'hello ', 0, 2);
        $result = $this->callUpload('chunked.mp3', 0, 2);

        $this->assertSame(1, $result['code'], $result['message']);
        $this->assertFileExists($this->tmpFolder . '/chunked.mp3.part');

        $this->prepareUpload('chunked.mp3', 'chunked.mp3', 'world', 1, 2);
        $result = $this->callUpload('chunked.mp3', 1, 2);

        $this->assertSame(1, $result['code'], $result['message']);
        $this->assertFileExists($this->mediaFolder . '/chunked.mp3');
        $this->assertSame('hello world', file_get_contents($this->mediaFolder . '/chunked.mp3'));
        $this->assertFileDoesNotExist($this->tmpFolder . '/chunked.mp3.part');
    }

    /**
     * Test (Fail): a file larger than the configured limit is rejected and its part removed
     */
    public function testUploadLargerThanMaxSizeIsRejected()
    {
        Config::set('redminportal::media.max_size', 5);

        $result = $this->upload('large.mp3', 'large.mp3', str_repeat('A', 20));

        $this->assertSame(0, $result['code']);
        $this->assertSame('This file is too large.', $result['message']);
        $this->assertFileDoesNotExist($this->tmpFolder . '/large.mp3.part');
        $this->assertFileDoesNotExist($this->mediaFolder . '/large.mp3');
    }

    /**
     * Test (Fail): negative chunk values are rejected
     */
    public function testNegativeChunkIsRejected()
    {
        $result = $this->upload('negative.mp3', 'negative.mp3', 'ID3', -1, 1);

        $this->assertSame(0, $result['code']);
        $this->assertFileDoesNotExist($this->tmpFolder . '/negative.mp3.part');
        $this->assertFileDoesNotExist($this->mediaFolder . '/negative.mp3');
    }

    /**
     * Test (Fail): a rejected chunk makes later chunks terminal
     */
    public function testChunkAfterSizeRejectionIsTerminal()
    {
        Config::set('redminportal::media.max_size', 5);

        $result = $this->upload('terminal.mp3', 'terminal.mp3', 'abc', 0, 3);
        $this->assertSame(1, $result['code'], $result['message']);

        $result = $this->upload('terminal.mp3', 'terminal.mp3', 'def', 1, 3);
        $this->assertSame(0, $result['code']);
        $this->assertSame('This file is too large.', $result['message']);
        $this->assertFileDoesNotExist($this->tmpFolder . '/terminal.mp3.part');

        $result = $this->upload('terminal.mp3', 'terminal.mp3', 'g', 2, 3);
        $this->assertSame(0, $result['code']);
        $this->assertSame('Failed to upload an empty file.', $result['message']);

        $this->assertFileDoesNotExist($this->tmpFolder . '/terminal.mp3.part');
        $this->assertFileDoesNotExist($this->mediaFolder . '/terminal.mp3');
        $this->assertSame('broken.pdf', Media::find($this->media->id)->path);
    }

    /**
     * Test (Fail): a later chunk sent without its first chunk is rejected
     */
    public function testChunkWithoutFirstChunkIsRejected()
    {
        $result = $this->upload('orphan.mp3', 'orphan.mp3', 'abc', 1, 2);

        $this->assertSame(0, $result['code']);
        $this->assertSame('Failed to upload an empty file.', $result['message']);
        $this->assertFileDoesNotExist($this->tmpFolder . '/orphan.mp3.part');
        $this->assertFileDoesNotExist($this->mediaFolder . '/orphan.mp3');
    }

    /**
     * Test (Fail): a chunk is rejected while another chunk holds the file lock
     */
    public function testChunkIsRejectedWhenLockIsHeld()
    {
        $fileName = 'locked.mp3';
        $partPath = $this->tmpFolder . '/' . $fileName . '.part';
        $lockPath = sys_get_temp_dir() . '/redmin-upload-' . md5($partPath) . '.lock';
        $lock = fopen($lockPath, 'c');

        $this->assertNotFalse($lock);
        $this->assertTrue(flock($lock, LOCK_EX | LOCK_NB));

        try {
            $result = $this->upload($fileName, $fileName, 'abc', 0, 1);

            $this->assertSame(0, $result['code']);
            $this->assertSame('Failed to open output stream.', $result['message']);
            $this->assertFileDoesNotExist($partPath);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
            @unlink($lockPath);
        }
    }

    /**
     * Test (Pass): uploading one media id keeps a sibling folder with a longer id
     */
    public function testUploadDoesNotDeleteSiblingMediaFolder()
    {
        $category = new Category;
        $category->id = 800000 + random_int(1, 99999);
        $category->name = 'Upload Sibling ' . uniqid();
        $category->short_description = 'Upload sibling category';
        $category->active = true;
        $category->save();

        $categoryFolder = public_path() . '/assets/medias/' . $category->id;
        $folder12 = $categoryFolder . '/12';
        $folder120 = $categoryFolder . '/120';
        $this->extraFolders[] = $categoryFolder;
        $this->extraFolders[] = public_path() . '/assets/medias/tmp/' . $category->id;

        mkdir($folder12, 0777, true);
        mkdir($folder120, 0777, true);
        file_put_contents($folder12 . '/old.mp3', 'old');
        file_put_contents($folder120 . '/keep.mp3', 'keep');

        foreach (array(12, 120) as $id) {
            $media = new Media;
            $media->id = $id;
            $media->name = 'Sibling Media ' . $id;
            $media->path = 'broken.pdf';
            $media->short_description = 'Sibling media';
            $media->category_id = $category->id;
            $media->save();
        }

        $result = $this->upload('sibling.mp3', 'sibling.mp3', 'ID3' . str_repeat('A', 64), 0, 1, 12);

        $this->assertSame(1, $result['code'], $result['message']);
        $this->assertFileExists($folder12 . '/sibling.mp3');
        $this->assertDirectoryExists($folder120);
        $this->assertFileExists($folder120 . '/keep.mp3');
    }

    /**
     * Prepare the raw superglobals used by the controller trait
     */
    private function prepareUpload($requestName, $fileName, $content, $chunk, $chunks)
    {
        $tmpName = tempnam(sys_get_temp_dir(), 'redmin_upload_');
        file_put_contents($tmpName, $content);
        $this->tempFiles[] = $tmpName;

        $_FILES = array(
            'file' => array(
                'name' => $fileName,
                'type' => 'application/octet-stream',
                'size' => strlen($content),
                'tmp_name' => $tmpName,
                'error' => 0,
            ),
        );

        $_REQUEST = array(
            'name' => $requestName,
            'chunk' => $chunk,
            'chunks' => $chunks,
        );
    }

    /**
     * Call the upload route and decode the JSON response
     */
    private function callUpload($requestName, $chunk, $chunks, $mediaId = null)
    {
        if ($mediaId === null) {
            $mediaId = $this->media->id;
        }

        $response = $this->call('POST', '/admin/medias/upload/' . $mediaId, array(
            'name' => $requestName,
            'chunk' => $chunk,
            'chunks' => $chunks,
        ));

        return json_decode($response->getContent(), true);
    }

    /**
     * Prepare and upload a single chunk
     */
    private function upload($requestName, $fileName, $content, $chunk = 0, $chunks = 1, $mediaId = null)
    {
        $this->prepareUpload($requestName, $fileName, $content, $chunk, $chunks);

        return $this->callUpload($requestName, $chunk, $chunks, $mediaId);
    }

    /**
     * Recursively delete only the folder provided
     */
    private function deleteFolder($folder)
    {
        if (! is_dir($folder)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($folder);
    }
}
