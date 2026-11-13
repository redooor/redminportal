<?php namespace Redooor\Redminportal\Test;

use Database\Seeders\RedminSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;

class CategoryImageUploadTest extends RedminBrowserTestCase
{
    private $uploadPath;
    private $tempFiles = array();

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RedminSeeder::class);
        Auth::guard('redminguard')->loginUsingId(1);

        $this->uploadPath = sys_get_temp_dir() . '/redmin_category_images_' . uniqid() . '/';
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
     * Test (Pass): posting two files in images[] creates two image rows
     */
    public function testStoreCreatesOneImagePerMultipleUpload()
    {
        $name = 'Multiple image category ' . uniqid();

        $this->call('POST', '/admin/categories/store', array(
            'name' => $name,
            'short_description' => 'Multiple image category',
            'long_description' => 'Multiple image category',
            'active' => true,
            'order' => 1,
        ), array(), array(
            'images' => array(
                new UploadedFile($this->createImage('png'), 'first.png', 'image/png', null, true),
                new UploadedFile($this->createImage('jpg'), 'second.jpg', 'image/jpeg', null, true),
            ),
        ));

        $this->assertRedirectedTo('/admin/categories');

        $category = Category::where('name', $name)->first();

        $this->assertNotNull($category);
        $this->assertSame(2, $category->images()->count());
        $this->assertSame(array(1, 2), $this->integers($category->images()->pluck('sort_order')->all()));
        $this->assertSame(array(1, 0), $this->integers($category->images()->pluck('is_primary')->all()));
        $this->assertFileExists(
            $this->uploadPath . 'categories/' . $category->id . '/first.png'
        );
        $this->assertFileExists(
            $this->uploadPath . 'categories/' . $category->id . '/second.jpg'
        );
    }

    /**
     * Test (Fail): an invalid images[] file is rejected by the image rules
     */
    public function testStoreRejectsInvalidImageInImagesArray()
    {
        $name = 'Invalid image category ' . uniqid();
        $source = $this->tempFile('png');
        file_put_contents($source, 'this is not an image');
        $imagesBefore = Image::count();

        $this->call('POST', '/admin/categories/store', array(
            'name' => $name,
            'short_description' => 'Invalid image category',
            'long_description' => 'Invalid image category',
            'active' => true,
            'order' => 1,
        ), array(), array(
            'images' => array(
                new UploadedFile($source, 'invalid.png', 'image/png', null, true),
            ),
        ));

        $this->assertRedirectedTo('/admin/categories/create');
        $this->assertSessionHasErrors('images.0');
        $this->assertNull(Category::where('name', $name)->first());
        $this->assertSame($imagesBefore, Image::count());
    }

    /**
     * Convert database integer values to plain integers
     */
    private function integers(array $values)
    {
        return array_map(function ($value) {
            return (int) $value;
        }, $values);
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
        $path = tempnam(sys_get_temp_dir(), 'redmin_category_image_');

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
