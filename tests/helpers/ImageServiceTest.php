<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Classes\ImageService;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageServiceTest extends RedminTestCase
{
    private $uploadPath;
    private $tempFiles = array();

    public function setUp(): void
    {
        parent::setUp();

        $this->uploadPath = sys_get_temp_dir() . '/redmin_image_service_' . uniqid() . '/';
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
     * Test (Pass): a single upload creates one Image row under the model folder
     */
    public function testAttachSingleFileCreatesImage()
    {
        $category = $this->createCategory();
        $source = $this->createImage('png');
        $file = new UploadedFile($source, 'image.png', 'image/png', null, true);

        $images = (new ImageService)->attach($category, $file, 'categories');

        $expected = 'assets/img/categories/' . $category->getKey() . '/image.png';

        $this->assertCount(1, $images);
        $this->assertInstanceOf(Image::class, $images->first());
        $this->assertSame($expected, $images->first()->path);
        $this->assertSame($expected, $category->images()->first()->path);
        $this->assertFileExists($this->uploadPath . 'categories/' . $category->getKey() . '/image.png');
    }

    /**
     * Test (Pass): an array of uploads creates one Image row per file in order
     */
    public function testAttachArrayCreatesOneImagePerFile()
    {
        $category = $this->createCategory();
        $files = array(
            new UploadedFile($this->createImage('png'), 'first.png', 'image/png', null, true),
            new UploadedFile($this->createImage('jpg'), 'second.jpg', 'image/jpeg', null, true),
        );

        $images = (new ImageService)->attach($category, $files, 'categories');
        $folder = 'assets/img/categories/' . $category->getKey() . '/';

        $this->assertCount(2, $images);
        $this->assertSame(
            array($folder . 'first.png', $folder . 'second.jpg'),
            $images->pluck('path')->all()
        );
        $this->assertSame(2, $category->images()->count());
        $this->assertFileExists($this->uploadPath . 'categories/' . $category->getKey() . '/first.png');
        $this->assertFileExists($this->uploadPath . 'categories/' . $category->getKey() . '/second.jpg');
    }

    /**
     * Test (Pass): null and empty input create no rows
     */
    public function testAttachNullOrEmptyCreatesNothing()
    {
        $category = $this->createCategory();

        $this->assertCount(0, (new ImageService)->attach($category, null, 'categories'));
        $this->assertCount(0, (new ImageService)->attach($category, array(), 'categories'));
        $this->assertSame(0, $category->images()->count());
    }

    /**
     * Test (Fail): an invalid file is ignored rather than thrown
     */
    public function testAttachIgnoresInvalidFile()
    {
        $category = $this->createCategory();
        $source = $this->tempFile('png');
        file_put_contents($source, 'this is not an image');
        $file = new UploadedFile($source, 'image.png', 'image/png', null, true);

        $images = (new ImageService)->attach($category, $file, 'categories');

        $this->assertCount(0, $images);
        $this->assertSame(0, $category->images()->count());
        $this->assertFileDoesNotExist($this->uploadPath . 'categories/' . $category->getKey() . '/image.png');
    }

    /**
     * Create a saved Category for attaching images
     */
    private function createCategory()
    {
        $category = new Category;
        $category->name = 'Image service category';
        $category->short_description = 'Image service description';
        $category->save();

        return $category;
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
     * Create an empty temporary file with the given extension
     */
    private function tempFile($extension)
    {
        $path = tempnam(sys_get_temp_dir(), 'redmin_image_service_file_');

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
