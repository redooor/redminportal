<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Classes\Imagine;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImagineUploadTest extends RedminTestCase
{
    private $uploadPath;
    private $tempFiles = array();

    public function setUp(): void
    {
        parent::setUp();

        $this->uploadPath = sys_get_temp_dir() . '/redmin_imagine_' . uniqid() . '/';
        Config::set('redminportal::image.upload_path', $this->uploadPath);
    }

    public function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        if ($this->uploadPath) {
            $this->deleteFolder($this->uploadPath);
        }

        $this->tempFiles = array();
        $this->uploadPath = null;

        parent::tearDown();
    }

    /**
     * Test (Pass): a PNG sent as image.html is stored with a PNG extension
     */
    public function testPngSentWithHtmlExtensionIsStoredAsPng()
    {
        $source = $this->createImage('png');
        $file = new UploadedFile($source, 'image.html', 'text/html', null, true);

        $path = (new Imagine)->upload($file, 'images', false, true);

        $this->assertNotNull($path);
        $this->assertSame('image.png', basename($path));
        $this->assertSame('assets/img/images/image.png', $path);
        $this->assertFileExists($this->uploadPath . 'images/image.png');
    }

    /**
     * Test (Pass): the original stem is sanitised before use
     */
    public function testOriginalStemIsSanitised()
    {
        $source = $this->createImage('png');
        $file = new UploadedFile($source, 'my photo.png', 'image/png', null, true);

        $path = (new Imagine)->upload($file, 'safe', false, true);

        $this->assertNotNull($path);
        $this->assertSame('my_photo.png', basename($path));
    }

    /**
     * Test (Fail): an HTML file sent as x.png is rejected and not moved
     */
    public function testHtmlSentAsPngReturnsNull()
    {
        $source = $this->tempFile('html');
        file_put_contents($source, '<html><body>not an image</body></html>');
        $file = new UploadedFile($source, 'x.png', 'image/png', null, true);

        $path = (new Imagine)->upload($file, 'images', false, true);

        $this->assertNull($path);
        $this->assertFileExists($source);
        $this->assertFileDoesNotExist($this->uploadPath . 'images/x.png');
    }

    /**
     * Test (Fail): unsafe directories are rejected and the file is not moved
     */
    public function testUnsafeDirectoriesReturnNull()
    {
        $directories = array(
            '..',
            '../escape',
            'images/../../escape',
            'images\\..\\escape',
            '/absolute',
            '\\absolute',
            "images\0escape",
        );

        foreach ($directories as $dir) {
            $source = $this->createImage('png');
            $file = new UploadedFile($source, 'unsafe.png', 'image/png', null, true);

            $path = (new Imagine)->upload($file, $dir, false, true);

            $this->assertNull($path, 'Unsafe directory was accepted: ' . $dir);
            $this->assertFileExists($source);
        }
    }

    /**
     * Test (Pass): two uploads with the same name are autonumbered
     */
    public function testSameNamedUploadsAreAutonumbered()
    {
        $imagine = new Imagine;

        $first = $imagine->upload(
            new UploadedFile($this->createImage('png'), 'name.png', 'image/png', null, true),
            'auto',
            false,
            true
        );
        $second = $imagine->upload(
            new UploadedFile($this->createImage('png'), 'name.png', 'image/png', null, true),
            'auto',
            false,
            true
        );

        $this->assertSame('name.png', basename($first));
        $this->assertSame('name_1.png', basename($second));
        $this->assertFileExists($this->uploadPath . 'auto/name.png');
        $this->assertFileExists($this->uploadPath . 'auto/name_1.png');
    }

    /**
     * Create a real image file using GD
     */
    private function createImage($type)
    {
        $path = $this->tempFile($type);

        $image = imagecreatetruecolor(10, 10);

        if ($type == 'png') {
            imagepng($image, $path);
        } elseif ($type == 'jpg') {
            imagejpeg($image, $path);
        } elseif ($type == 'gif') {
            imagegif($image, $path);
        }

        imagedestroy($image);

        return $path;
    }

    /**
     * Create an empty temporary file with the given extension
     */
    private function tempFile($extension)
    {
        $path = tempnam(sys_get_temp_dir(), 'redmin_imagine_file_');

        if ($path === false) {
            $this->fail('Unable to create a temporary file.');
        }

        if ($extension != '') {
            $newPath = $path . '.' . $extension;
            rename($path, $newPath);
            $path = $newPath;
        }

        $this->tempFiles[] = $path;

        return $path;
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
