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
     * Test (Pass): EXIF metadata is removed from an uploaded JPEG
     */
    public function testJpegExifMetadataIsStripped()
    {
        $source = $this->createImage('jpg');
        $this->insertApp1Segment($source, "Exif\x00\x00GPS" . str_repeat("\x00", 48));
        $file = new UploadedFile($source, 'photo.jpg', 'image/jpeg', null, true);

        $path = (new Imagine)->upload($file, 'exif', false, true);

        $this->assertNotNull($path);
        $this->assertFileExists($this->uploadPath . 'exif/photo.jpg');
        $this->assertStringNotContainsString('Exif', file_get_contents($this->uploadPath . 'exif/photo.jpg'));
    }

    /**
     * Test (Pass): a PNG stays a PNG with the same dimensions
     */
    public function testPngKeepsFormatAndDimensions()
    {
        $source = $this->createImage('png', 13, 7);
        $file = new UploadedFile($source, 'shape.png', 'image/png', null, true);

        $path = (new Imagine)->upload($file, 'shapes', false, true);

        $this->assertNotNull($path);
        $this->assertFileExists($this->uploadPath . 'shapes/shape.png');

        $size = getimagesize($this->uploadPath . 'shapes/shape.png');

        $this->assertSame(IMAGETYPE_PNG, $size[2]);
        $this->assertSame(13, $size[0]);
        $this->assertSame(7, $size[1]);
    }

    /**
     * Test (Pass): an animated GIF is moved as-is so it is not flattened
     */
    public function testAnimatedGifIsKeptWithoutReencoding()
    {
        $source = $this->createAnimatedGif();
        $bytes = file_get_contents($source);
        $file = new UploadedFile($source, 'animation.gif', 'image/gif', null, true);

        $path = (new Imagine)->upload($file, 'animated', false, true);

        $this->assertNotNull($path);
        $this->assertFileExists($this->uploadPath . 'animated/animation.gif');
        $this->assertSame($bytes, file_get_contents($this->uploadPath . 'animated/animation.gif'));
        $this->assertGreaterThan(1, substr_count($bytes, "\x00\x21\xF9\x04"));
    }

    /**
     * Test (Fail): a PNG header with a corrupt body returns null and stores nothing
     */
    public function testCorruptPngReturnsNullAndLeavesNoFile()
    {
        $valid = $this->createImage('png', 10, 10);
        $source = $this->tempFile('png');
        file_put_contents($source, substr(file_get_contents($valid), 0, 33) . str_repeat("\x00", 32));

        $file = new UploadedFile($source, 'broken.png', 'image/png', null, true);

        $path = (new Imagine)->upload($file, 'broken', false, true);

        $this->assertNull($path);
        $this->assertFileDoesNotExist($this->uploadPath . 'broken/broken.png');
    }

    /**
     * Test (Pass): a JPEG with EXIF orientation 6 is rotated
     */
    public function testJpegWithExifOrientationIsRotated()
    {
        $source = $this->createImage('jpg', 30, 10);
        $this->insertApp1Segment($source, $this->exifPayload(6));
        $file = new UploadedFile($source, 'rotated.jpg', 'image/jpeg', null, true);

        $path = (new Imagine)->upload($file, 'rotate', false, true);

        $this->assertNotNull($path);
        $size = getimagesize($this->uploadPath . 'rotate/rotated.jpg');

        $this->assertSame(10, $size[0]);
        $this->assertSame(30, $size[1]);
    }

    /**
     * Test (Fail): a corrupt upload must not delete an existing target file
     */
    public function testCorruptUploadLeavesExistingFileUntouched()
    {
        $dir = 'existing';
        $valid = $this->createImage('png', 12, 9);

        $first = (new Imagine)->upload(
            new UploadedFile($valid, 'keep.png', 'image/png', null, true),
            $dir,
            false,
            false
        );

        $this->assertNotNull($first);

        $target = $this->uploadPath . $dir . '/keep.png';
        $original = file_get_contents($target);

        $corrupt = $this->tempFile('png');
        $bytes = file_get_contents($this->createImage('png', 10, 10));
        file_put_contents($corrupt, substr($bytes, 0, 33) . str_repeat("\x00", 32));

        $path = (new Imagine)->upload(
            new UploadedFile($corrupt, 'keep.png', 'image/png', null, true),
            $dir,
            false,
            false
        );

        $this->assertNull($path);
        $this->assertFileExists($target);
        $this->assertSame($original, file_get_contents($target));
    }

    /**
     * Test (Fail): an image above image.max_pixels is rejected and not stored
     */
    public function testImageAboveMaxPixelsIsRejected()
    {
        Config::set('redminportal::image.max_pixels', 100);

        $source = $this->createImage('png', 20, 20);
        $file = new UploadedFile($source, 'big.png', 'image/png', null, true);

        $path = (new Imagine)->upload($file, 'limits', false, true);

        $this->assertNull($path);
        $this->assertFileDoesNotExist($this->uploadPath . 'limits/big.png');
    }

    /**
     * Test (Pass): an image below image.max_pixels is stored
     */
    public function testImageBelowMaxPixelsIsAccepted()
    {
        Config::set('redminportal::image.max_pixels', 100);

        $source = $this->createImage('png', 5, 5);
        $file = new UploadedFile($source, 'small.png', 'image/png', null, true);

        $path = (new Imagine)->upload($file, 'limits', false, true);

        $this->assertNotNull($path);
        $this->assertFileExists($this->uploadPath . 'limits/small.png');
    }

    /**
     * Test (Pass): a two-frame GIF without graphic control extensions is kept
     */
    public function testTwoFrameGifWithoutControlExtensionsIsKept()
    {
        $source = $this->createTwoFrameGif(false);
        $bytes = file_get_contents($source);
        $file = new UploadedFile($source, 'plain.gif', 'image/gif', null, true);

        $path = (new Imagine)->upload($file, 'plain', false, true);

        $this->assertNotNull($path);
        $this->assertFileExists($this->uploadPath . 'plain/plain.gif');
        $this->assertSame($bytes, file_get_contents($this->uploadPath . 'plain/plain.gif'));
    }

    /**
     * Test (Pass): a single-frame GIF is re-encoded instead of moved
     */
    public function testSingleFrameGifIsReencoded()
    {
        $source = $this->createImage('gif', 3, 2);
        $bytes = file_get_contents($source);
        $comment = "\x21\xFE\x05hello\x00";

        file_put_contents($source, substr($bytes, 0, -1) . $comment . "\x3B");

        $file = new UploadedFile($source, 'single.gif', 'image/gif', null, true);

        $path = (new Imagine)->upload($file, 'single', false, true);

        $this->assertNotNull($path);
        $this->assertFileExists($this->uploadPath . 'single/single.gif');
        $this->assertStringNotContainsString('hello', file_get_contents($this->uploadPath . 'single/single.gif'));
    }

    /**
     * Test (Fail): a malformed GIF is rejected and stores nothing
     */
    public function testCorruptGifIsRejected()
    {
        $source = $this->tempFile('gif');
        $bytes = "GIF89a" . pack('v', 1) . pack('v', 1) . "\x80\x00\x00"
            . str_repeat("\x00", 6)
            . "\x00\x21\xF9\x04"
            . "\x00\x21\xF9\x04"
            . str_repeat("\x00", 16);

        file_put_contents($source, $bytes);

        $file = new UploadedFile($source, 'corrupt.gif', 'image/gif', null, true);

        $path = (new Imagine)->upload($file, 'corruptgif', false, true);

        $this->assertNull($path);
        $this->assertFileDoesNotExist($this->uploadPath . 'corruptgif/corrupt.gif');
    }

    /**
     * Test (Pass): image.upload_quality controls the re-encoded JPEG size
     */
    public function testUploadQualityAffectsJpegSize()
    {
        $source = $this->tempFile('jpg');
        $image = imagecreatetruecolor(120, 120);

        for ($x = 0; $x < 120; $x++) {
            for ($y = 0; $y < 120; $y++) {
                $color = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
                imagesetpixel($image, $x, $y, $color);
            }
        }

        imagejpeg($image, $source, 100);
        imagedestroy($image);

        Config::set('redminportal::image.upload_quality', 30);
        $low = (new Imagine)->upload(
            new UploadedFile($source, 'noise.jpg', 'image/jpeg', null, true),
            'quality',
            false,
            true
        );

        Config::set('redminportal::image.upload_quality', 95);
        $high = (new Imagine)->upload(
            new UploadedFile($source, 'noise.jpg', 'image/jpeg', null, true),
            'quality',
            false,
            true
        );

        $this->assertNotNull($low);
        $this->assertNotNull($high);
        $this->assertLessThan(
            filesize($this->uploadPath . 'quality/' . basename($high)),
            filesize($this->uploadPath . 'quality/' . basename($low))
        );
    }

    /**
     * Create a real image file using GD
     */
    private function createImage($type, $width = 10, $height = 10)
    {
        $path = $this->tempFile($type);

        $image = imagecreatetruecolor($width, $height);

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
     * Insert an APP1 segment directly after the JPEG SOI marker
     */
    private function insertApp1Segment($source, $payload)
    {
        $jpeg = file_get_contents($source);
        $segment = "\xFF\xE1" . pack('n', strlen($payload) + 2) . $payload;

        file_put_contents($source, substr($jpeg, 0, 2) . $segment . substr($jpeg, 2));
    }

    /**
     * Build a minimal EXIF payload carrying an orientation value
     */
    private function exifPayload($orientation)
    {
        $tiff = "II\x2A\x00" . pack('V', 8)
            . pack('v', 1)
            . pack('v', 0x0112) . pack('v', 3) . pack('V', 1)
            . pack('v', $orientation) . "\x00\x00"
            . pack('V', 0);

        return "Exif\x00\x00" . $tiff;
    }

    /**
     * Build a minimal two-frame animated GIF
     */
    private function createAnimatedGif()
    {
        $path = $this->tempFile('gif');
        $control = "\x21\xF9\x04\x00\x0A\x00\x00\x00";
        $descriptor = "\x2C\x00\x00\x00\x00\x01\x00\x01\x00\x00";
        $data = "\x02\x02\x4C\x01\x00";
        $bytes = "GIF89a" . pack('v', 1) . pack('v', 1) . "\x80\x00\x00"
            . str_repeat("\x00", 6)
            . $control . $descriptor . $data
            . $control . $descriptor . $data
            . "\x3B";

        file_put_contents($path, $bytes);

        return $path;
    }

    /**
     * Build a minimal two-frame GIF, optionally with graphic control extensions
     */
    private function createTwoFrameGif($withControl = true)
    {
        $path = $this->tempFile('gif');
        $control = $withControl ? "\x21\xF9\x04\x00\x0A\x00\x00\x00" : '';
        $descriptor = "\x2C\x00\x00\x00\x00\x01\x00\x01\x00\x00";
        $data = "\x02\x02\x4C\x01\x00";
        $bytes = "GIF89a" . pack('v', 1) . pack('v', 1) . "\x80\x00\x00"
            . str_repeat("\x00", 6)
            . $control . $descriptor . $data
            . $control . $descriptor . $data
            . "\x3B";

        file_put_contents($path, $bytes);

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
