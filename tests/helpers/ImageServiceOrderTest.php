<?php namespace Redooor\Redminportal\Test;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Redooor\Redminportal\App\Classes\ImageService;
use Redooor\Redminportal\App\Helpers\RImage;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageServiceOrderTest extends RedminTestCase
{
    private $uploadPath;
    private $tempFiles = array();
    private $relativeRoot;

    public function setUp(): void
    {
        parent::setUp();

        $this->uploadPath = sys_get_temp_dir() . '/redmin_image_order_' . uniqid() . '/';
        $this->relativeRoot = 'assets/img/redmin_image_order_' . uniqid();
        Config::set('redminportal::image.upload_path', $this->uploadPath);
    }

    public function tearDown(): void
    {
        Image::flushEventListeners();

        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        if ($this->uploadPath) {
            $this->deleteFolder($this->uploadPath);
        }

        if ($this->relativeRoot) {
            $root = getcwd() . '/' . $this->relativeRoot;
            $this->deleteFolder($root);
            @rmdir(dirname($root));
            @rmdir(dirname(dirname($root)));
        }

        $this->tempFiles = array();
        $this->uploadPath = null;
        $this->relativeRoot = null;

        parent::tearDown();
    }

    /**
     * Test (Pass): the image table has order and primary columns with legacy defaults
     */
    public function testImageTableHasOrderAndPrimaryColumnsWithDefaults()
    {
        $this->assertTrue(Schema::hasColumn('images', 'sort_order'));
        $this->assertTrue(Schema::hasColumn('images', 'is_primary'));

        $category = $this->createCategory();
        $image = new Image;
        $image->path = 'assets/img/default.png';
        $category->images()->save($image);

        $row = DB::table('images')->where('id', $image->id)->first();

        $this->assertSame(0, (int) $row->sort_order);
        $this->assertSame(0, (int) $row->is_primary);
    }

    /**
     * Test (Pass): two new files get sequential order and only the first is primary
     */
    public function testAttachTwoFilesSetsOrderAndPrimary()
    {
        $category = $this->createCategory();
        $files = array(
            $this->uploadedFile('png', 'first.png'),
            $this->uploadedFile('jpg', 'second.jpg'),
        );

        (new ImageService)->attach($category, $files, 'categories');

        $images = $category->images()->get();

        $this->assertCount(2, $images);
        $this->assertSame(array(1, 2), $this->integers($images->pluck('sort_order')->all()));
        $this->assertSame(array(1, 0), $this->integers($images->pluck('is_primary')->all()));
        $this->assertSame('first.png', basename($images->first()->path));
    }

    /**
     * Test (Pass): later uploads append while the original primary is kept
     */
    public function testAttachMoreFilesAppendsAndKeepsPrimary()
    {
        $category = $this->createCategory();
        (new ImageService)->attach($category, array(
            $this->uploadedFile('png', 'first.png'),
            $this->uploadedFile('jpg', 'second.jpg'),
        ), 'categories');

        $firstId = $category->images()->first()->id;

        (new ImageService)->attach($category, array(
            $this->uploadedFile('gif', 'third.gif'),
        ), 'categories');

        $images = $category->images()->get();

        $this->assertCount(3, $images);
        $this->assertSame(array(1, 2, 3), $this->integers($images->pluck('sort_order')->all()));
        $this->assertSame(array(1, 0, 0), $this->integers($images->pluck('is_primary')->all()));
        $this->assertSame($firstId, $category->images()->first()->id);
    }

    /**
     * Test (Pass): the relation first() follows the primary image, even with a higher id
     */
    public function testFirstReturnsPrimaryAfterMakePrimary()
    {
        $category = $this->createCategory();
        $images = (new ImageService)->attach($category, array(
            $this->uploadedFile('png', 'first.png'),
            $this->uploadedFile('jpg', 'second.jpg'),
            $this->uploadedFile('gif', 'third.gif'),
        ), 'categories');

        $third = $images->last();

        (new ImageService)->makePrimary($third);

        $first = $category->images()->first();

        $this->assertSame($third->id, $first->id);
        $this->assertSame(1, (int) $first->is_primary);
        $this->assertSame(0, (int) Image::find($images->first()->id)->is_primary);
    }

    /**
     * Test (Pass): the same loaded objects can switch the primary image repeatedly
     */
    public function testMakePrimaryDoesNotRelyOnDirtyTracking()
    {
        $category = $this->createCategory();
        $images = (new ImageService)->attach($category, array(
            $this->uploadedFile('png', 'a.png'),
            $this->uploadedFile('jpg', 'b.jpg'),
        ), 'categories');

        $a = $images[0];
        $b = $images[1];
        $service = new ImageService;

        $service->makePrimary($b);
        $service->makePrimary($a);
        $service->makePrimary($b);

        $this->assertSame(1, $category->images()->where('is_primary', 1)->count());
        $this->assertSame(1, (int) Image::find($b->id)->is_primary);
        $this->assertSame(0, (int) Image::find($a->id)->is_primary);
    }

    /**
     * Test (Pass): two sequential attach() calls still leave exactly one primary
     */
    public function testSequentialAttachKeepsSinglePrimary()
    {
        $category = $this->createCategory();
        $service = new ImageService;

        $service->attach($category, $this->uploadedFile('png', 'one.png'), 'categories');
        $service->attach($category, $this->uploadedFile('jpg', 'two.jpg'), 'categories');

        $this->assertSame(2, $category->images()->count());
        $this->assertSame(1, $category->images()->where('is_primary', 1)->count());
        $this->assertSame(1, (int) $category->images()->first()->is_primary);
    }

    /**
     * Test (Pass): a partial reorder puts the listed images first and dedupes ids
     */
    public function testReorderPartialListPutsListedImagesFirst()
    {
        $category = $this->createCategory();
        $first = $this->storedImage($category, 'one.png', 0);
        $second = $this->storedImage($category, 'two.png', 0);
        $third = $this->storedImage($category, 'three.png', 0);
        $fourth = $this->storedImage($category, 'four.png', 0);

        $foreignCategory = $this->createCategory('Foreign reorder category');
        $foreign = $this->storedImage($foreignCategory, 'foreign.png', 9);

        $service = new ImageService;
        $service->reorder($category, array($fourth->id, $third->id));

        $expected = array($fourth->id, $third->id, $first->id, $second->id);
        $this->assertSame(
            $expected,
            array_map('intval', $category->images()->pluck('id')->all())
        );

        $service->reorder($category, array($fourth->id, $third->id, $fourth->id, $foreign->id));

        $this->assertSame(
            $expected,
            array_map('intval', $category->images()->pluck('id')->all())
        );
        $this->assertSame(9, (int) Image::find($foreign->id)->sort_order);
    }

    /**
     * Test (Pass): reorder numbers only images that belong to the model
     */
    public function testReorderIgnoresForeignImages()
    {
        $category = $this->createCategory();
        $images = (new ImageService)->attach($category, array(
            $this->uploadedFile('png', 'first.png'),
            $this->uploadedFile('jpg', 'second.jpg'),
            $this->uploadedFile('gif', 'third.gif'),
        ), 'categories');

        $foreignCategory = $this->createCategory('Foreign image category');
        $foreign = $this->storedImage($foreignCategory, 'foreign.png', 9);

        (new ImageService)->reorder($category, array(
            $images[2]->id,
            $foreign->id,
            $images[0]->id,
            $images[1]->id,
        ));

        $this->assertSame(1, (int) Image::find($images[2]->id)->sort_order);
        $this->assertSame(2, (int) Image::find($images[0]->id)->sort_order);
        $this->assertSame(3, (int) Image::find($images[1]->id)->sort_order);
        $this->assertSame(9, (int) Image::find($foreign->id)->sort_order);
        $this->assertSame($images[0]->id, $category->images()->first()->id);
    }

    /**
     * Test (Pass): removing the primary deletes its files and promotes the next image
     */
    public function testRemovePrimaryDeletesFilesAndPromotesNext()
    {
        $category = $this->createCategory();
        $primary = $this->storedImage($category, 'primary.png', 1, true);
        $next = $this->storedImage($category, 'next.png', 2);
        $last = $this->storedImage($category, 'last.png', 3);

        $original = getcwd() . '/' . $primary->path;
        $thumbnail = getcwd() . '/' . (new RImage)->getUrl($primary->path, 'thumb');

        (new ImageService)->remove($primary);

        $this->assertNull(Image::find($primary->id));
        $this->assertFileDoesNotExist($original);
        $this->assertFileDoesNotExist($thumbnail);
        $this->assertSame(1, (int) Image::find($next->id)->is_primary);
        $this->assertSame(0, (int) Image::find($last->id)->is_primary);
        $this->assertSame($next->id, $category->images()->first()->id);
    }

    /**
     * Test (Pass): removing a non-primary image leaves the primary in place
     */
    public function testRemoveNonPrimaryKeepsPrimary()
    {
        $category = $this->createCategory();
        $primary = $this->storedImage($category, 'primary.png', 1, true);
        $removed = $this->storedImage($category, 'removed.png', 2);
        $this->storedImage($category, 'last.png', 3);

        (new ImageService)->remove($removed);

        $this->assertNull(Image::find($removed->id));
        $this->assertSame(1, (int) Image::find($primary->id)->is_primary);
        $this->assertSame($primary->id, $category->images()->first()->id);
        $this->assertSame(2, $category->images()->count());
    }

    /**
     * Test (Pass): Image::delete() uses the service and removes the same files
     */
    public function testImageDeleteRemovesFilesThroughService()
    {
        $category = $this->createCategory();
        $image = $this->storedImage($category, 'delete.png', 1, true);

        $original = getcwd() . '/' . $image->path;
        $thumbnail = getcwd() . '/' . (new RImage)->getUrl($image->path, 'thumb');

        $this->assertTrue($image->delete());
        $this->assertNull(Image::find($image->id));
        $this->assertFileDoesNotExist($original);
        $this->assertFileDoesNotExist($thumbnail);
    }

    /**
     * Test (Pass): Image::delete() honours a deleting listener veto
     */
    public function testImageDeleteHonoursDeletingVeto()
    {
        $category = $this->createCategory();
        $image = $this->storedImage($category, 'veto.png', 1, true);
        $original = getcwd() . '/' . $image->path;

        Image::deleting(function () {
            return false;
        });

        $this->assertFalse($image->delete());
        $this->assertNotNull(Image::find($image->id));
        $this->assertFileExists($original);
    }

    /**
     * Test (Pass): Image::delete() fires the deleted event on a normal delete
     */
    public function testImageDeleteFiresDeletedEvent()
    {
        $category = $this->createCategory();
        $image = $this->storedImage($category, 'event.png', 1, true);
        $fired = false;

        Image::deleted(function () use (&$fired) {
            $fired = true;
        });

        $this->assertTrue($image->delete());
        $this->assertTrue($fired);
        $this->assertNull(Image::find($image->id));
    }

    /**
     * Test (Pass): filesFrom() reads image and images[] uploads in that order
     */
    public function testFilesFromReadsSingleAndMultipleUploads()
    {
        $single = $this->uploadedFile('png', 'single.png');
        $one = $this->uploadedFile('jpg', 'one.jpg');
        $two = $this->uploadedFile('gif', 'two.gif');

        $request = Request::create('/upload', 'POST', array(), array(), array(
            'image' => $single,
            'images' => array($one, $two),
        ));

        $files = ImageService::filesFrom($request);

        $this->assertCount(3, $files);
        $this->assertSame(
            array('single.png', 'one.jpg', 'two.gif'),
            array_map(function ($file) {
                return $file->getClientOriginalName();
            }, $files)
        );
        $this->assertContainsOnlyInstancesOf(UploadedFile::class, $files);
    }

    /**
     * Test (Pass): filesFrom() falls back to the current request when none is given
     */
    public function testFilesFromUsesCurrentRequestByDefault()
    {
        $single = $this->uploadedFile('png', 'single.png');
        $request = Request::create('/upload', 'POST', array(), array(), array('image' => $single));
        $this->app->instance('request', $request);

        $files = ImageService::filesFrom();

        $this->assertCount(1, $files);
        $this->assertSame('single.png', $files[0]->getClientOriginalName());
    }

    /**
     * Test (Pass): legacy rows without order or primary still return the lowest id first
     */
    public function testLegacyImagesKeepIdOrder()
    {
        $category = $this->createCategory();
        $first = new Image;
        $first->path = 'assets/img/legacy-first.png';
        $category->images()->save($first);

        $second = new Image;
        $second->path = 'assets/img/legacy-second.png';
        $category->images()->save($second);

        $this->assertSame($first->id, $category->images()->first()->id);
        $this->assertSame(0, (int) $category->images()->first()->sort_order);
        $this->assertSame(0, (int) $category->images()->first()->is_primary);
    }

    /**
     * Create a saved Category for attaching images
     */
    private function createCategory($name = 'Image order category')
    {
        $category = new Category;
        $category->name = $name . ' ' . uniqid();
        $category->short_description = 'Image order description';
        $category->save();

        return $category;
    }

    /**
     * Create a saved Image and its original and thumbnail files under the current directory
     */
    private function storedImage($model, $filename, $sortOrder = 0, $isPrimary = false)
    {
        $path = $this->relativeRoot . '/stored/' . $filename;
        $absolute = getcwd() . '/' . $path;
        $thumbnail = getcwd() . '/' . (new RImage)->getUrl($path, 'thumb');

        foreach (array($absolute, $thumbnail) as $file) {
            if (! is_dir(dirname($file))) {
                mkdir(dirname($file), 0777, true);
            }
            file_put_contents($file, 'test image file');
        }

        $image = new Image;
        $image->path = $path;
        $image->sort_order = $sortOrder;
        $image->is_primary = $isPrimary;
        $model->images()->save($image);

        return $image;
    }

    /**
     * Wrap a real image file as an UploadedFile
     */
    private function uploadedFile($type, $name)
    {
        return new UploadedFile($this->createImage($type), $name, 'image/' . $type, null, true);
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
        $path = tempnam(sys_get_temp_dir(), 'redmin_image_order_file_');

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
     * Convert database integer values to plain integers
     */
    private function integers(array $values)
    {
        return array_map(function ($value) {
            return (int) $value;
        }, $values);
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
