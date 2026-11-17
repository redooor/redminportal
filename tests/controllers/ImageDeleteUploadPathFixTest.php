<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Classes\ImageService;
use Redooor\Redminportal\App\Helpers\RImage;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;

/**
 * Covers deleting original and resized image files when image.upload_path is absolute.
 */
class ImageDeleteUploadPathFixTest extends BaseControllerTest
{
    use CharacterizationSupport;

    private $originalCwd;
    private $originalUploadPath;
    private $outsidePath;
    private $workingPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalCwd = getcwd();
        $this->originalUploadPath = Config::get('redminportal::image.upload_path');
        $this->outsidePath = sys_get_temp_dir() . '/redmin_image_outside_' . uniqid() . '/';
        $this->workingPath = sys_get_temp_dir() . '/redmin_image_cwd_' . uniqid() . '/';

        mkdir($this->outsidePath, 0777, true);
        mkdir($this->workingPath, 0777, true);

        $this->page = '/admin/categories';
        $this->viewhas = array(
            'singular' => 'category',
            'plural' => 'categories'
        );
        $this->input = array(
            'create' => array(
                'name' => 'Image delete category',
                'short_description' => 'Short',
                'long_description' => 'Long',
                'active' => true,
                'order' => 1
            ),
            'edit' => array(
                'id' => 1,
                'name' => 'Image delete category',
                'short_description' => 'Short',
                'long_description' => 'Long',
                'active' => true,
                'order' => 1
            )
        );

        $this->configureImageUploads();
    }

    public function tearDown(): void
    {
        if ($this->originalCwd) {
            chdir($this->originalCwd);
        }

        $this->cleanupImageUploads();

        if ($this->originalUploadPath !== null) {
            Config::set('redminportal::image.upload_path', $this->originalUploadPath);
        }

        $this->deleteFolder($this->outsidePath);
        $this->deleteFolder($this->workingPath);

        $this->originalCwd = null;
        $this->originalUploadPath = null;
        $this->outsidePath = null;
        $this->workingPath = null;

        parent::tearDown();
    }

    /**
     * Test (Pass): Image::delete removes the original and variants from an absolute upload path
     */
    public function testImageDeleteRemovesFilesFromAbsoluteUploadPath()
    {
        $category = $this->createCategory();
        $image = $this->uploadedImageTo($category, 'delete.png');

        $original = $this->storedFilePath($image->path);
        $variants = $this->variantPaths($image->path);

        $this->assertFileExists($original);
        $this->assertFilesExist($variants);

        $this->chdirAway();

        $this->assertTrue($image->delete());
        $this->assertNull(Image::find($image->id));
        $this->assertFileDoesNotExist($original);
        $this->assertFilesMissing($variants);
    }

    /**
     * Test (Pass): ImageService::remove removes the original and variants from an absolute upload path
     */
    public function testImageServiceRemoveRemovesFilesFromAbsoluteUploadPath()
    {
        $category = $this->createCategory();
        $image = $this->uploadedImageTo($category, 'remove.png');

        $original = $this->storedFilePath($image->path);
        $variants = $this->variantPaths($image->path);

        $this->assertFileExists($original);
        $this->assertFilesExist($variants);

        $this->chdirAway();

        $this->assertTrue((new ImageService)->remove($image));
        $this->assertNull(Image::find($image->id));
        $this->assertFileDoesNotExist($original);
        $this->assertFilesMissing($variants);
    }

    /**
     * Test (Pass): a failed attach removes the original and variants it had written
     */
    public function testFailedAttachRemovesUploadedFiles()
    {
        $category = $this->createCategory();
        $path = 'assets/img/categories/' . $category->id . '/cleanup.png';
        $original = $this->storedFilePath($path);
        $variants = $this->variantPaths($path);

        Image::creating(function () {
            throw new \RuntimeException('forced image save failure');
        });

        try {
            (new ImageService)->attach($category, $this->uploadedImage('cleanup.png'), 'categories');
            $this->fail('The forced image save failure did not propagate.');
        } catch (\RuntimeException $e) {
            $this->assertSame('forced image save failure', $e->getMessage());
        } finally {
            Image::flushEventListeners();
        }

        $this->chdirAway();

        $this->assertSame(0, Image::count());
        $this->assertSame(0, $category->images()->count());
        $this->assertFileDoesNotExist($original);
        $this->assertFilesMissing($variants);
    }

    /**
     * Create a saved category for the upload tests
     */
    private function createCategory($name = 'Image delete category')
    {
        return $this->createModel(Category::class, array(
            'name' => $name . ' ' . uniqid(),
            'short_description' => 'Short',
            'order' => 1
        ));
    }

    /**
     * Attach one real uploaded image through the service
     */
    private function uploadedImageTo($model, $name)
    {
        $images = (new ImageService)->attach($model, $this->uploadedImage($name), 'categories');

        $this->assertCount(1, $images);

        return $images->first();
    }

    /**
     * Get the on-disk original path for a stored path
     */
    private function storedFilePath($path)
    {
        $uploadDir = trim((string) Config::get('redminportal::image.upload_dir'), '/\\');
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if ($uploadDir !== '' && strpos($path, $uploadDir . '/') === 0) {
            return rtrim($this->characterizationUploadPath, '/\\') . '/' . substr($path, strlen($uploadDir) + 1);
        }

        return public_path($path);
    }

    /**
     * Get the absolute path of every configured resized variant
     */
    private function variantPaths($path)
    {
        $paths = array();

        foreach (array_keys(Config::get('redminportal::image.dimensions')) as $key) {
            $variant = (new RImage)->getUrl($path, $key, true);

            $this->assertNotNull($variant);
            $paths[$key] = $variant;
        }

        return $paths;
    }

    /**
     * Move the process working directory away from the project
     */
    private function chdirAway()
    {
        $this->assertTrue(chdir($this->workingPath));
    }

    /**
     * Assert every provided file exists
     */
    private function assertFilesExist(array $files)
    {
        foreach ($files as $name => $file) {
            $this->assertFileExists($file, 'Missing file [' . $name . '] at ' . $file);
        }
    }

    /**
     * Assert every provided file is gone
     */
    private function assertFilesMissing(array $files)
    {
        foreach ($files as $name => $file) {
            $this->assertFileDoesNotExist($file, 'File [' . $name . '] still exists at ' . $file);
        }
    }

    /**
     * Test (Pass): Category::delete removes its configured image folder
     */
    public function testCategoryDeleteRemovesConfiguredImageFolder()
    {
        $category = $this->createCategory();
        $image = $this->uploadedImageTo($category, 'category.png');
        $folder = rtrim($this->characterizationUploadPath, '/\\') . '/categories/' . $category->id;
        $original = $this->storedFilePath($image->path);
        $variants = $this->variantPaths($image->path);

        $this->assertDirectoryExists($folder);
        $this->assertFileExists($original);
        $this->assertFilesExist($variants);

        $this->chdirAway();

        $this->assertTrue($category->delete());
        $this->assertNull(Category::find($category->id));
        $this->assertSame(0, Image::count());
        $this->assertDirectoryDoesNotExist($folder);
        $this->assertFileDoesNotExist($original);
        $this->assertFilesMissing($variants);
    }

    /**
     * Test (Fail): a stored path containing '..' deletes nothing outside the upload path
     */
    public function testParentSegmentPathDeletesNothingOutsideUploadPath()
    {
        mkdir($this->characterizationUploadPath, 0777, true);

        $outside = $this->outsidePath . 'secret.png';
        $inside = rtrim($this->characterizationUploadPath, '/\\') . '/safe.png';
        file_put_contents($outside, 'outside');
        file_put_contents($inside, 'inside');

        $stored = 'assets/img/../' . basename(rtrim($this->outsidePath, '/\\')) . '/secret.png';
        $image = new Image;
        $image->path = $stored;

        $this->chdirAway();

        $image->deleteFiles($stored);

        $this->assertFileExists($outside);
        $this->assertFileExists($inside);

        $category = $this->createCategory();
        $saved = $this->attachImage($category, $stored);
        $saved->delete();

        $this->assertNull(Image::find($saved->id));
        $this->assertFileExists($outside);
        $this->assertFileExists($inside);
    }

    /**
     * Test (Fail): an absolute target outside the upload path is ignored
     */
    public function testAbsoluteTargetOutsideUploadPathIsIgnored()
    {
        mkdir($this->characterizationUploadPath, 0777, true);

        $outside = $this->outsidePath . 'absolute.png';
        $inside = rtrim($this->characterizationUploadPath, '/\\') . '/absolute.png';
        file_put_contents($outside, 'outside');
        file_put_contents($inside, 'inside');

        $image = new Image;

        $image->deleteFiles($outside);
        $this->assertFileExists($outside);

        $image->deleteFiles($inside);
        $this->assertFileDoesNotExist($inside);
    }

    /**
     * Test (Fail): a stored path outside the upload directory is not deleted through the public_path() fallback
     */
    public function testStoredPathOutsideUploadDirIsIgnored()
    {
        $stray = public_path('redmin_stray_' . uniqid() . '.txt');
        file_put_contents($stray, 'stray');

        try {
            (new Image)->deleteFiles(basename($stray));

            $this->assertFileExists($stray);
        } finally {
            @unlink($stray);
        }
    }

    /**
     * Test (Fail): a backslash absolute target outside the upload path is ignored
     */
    public function testBackslashAbsoluteTargetOutsideUploadPathIsIgnored()
    {
        $outside = $this->outsidePath . 'backslash.png';
        file_put_contents($outside, 'outside');

        (new Image)->deleteFiles(str_replace('/', '\\', $outside));

        $this->assertFileExists($outside);
    }

    /**
     * Test (Pass): a relative upload path resolves against the working directory for deletion
     */
    public function testRelativeUploadPathStillDeletesFiles()
    {
        chdir($this->workingPath);
        Config::set('redminportal::image.upload_dir', 'rel/img');
        Config::set('redminportal::image.upload_path', 'rel/img/');

        mkdir($this->workingPath . 'rel/img/pages/1', 0777, true);
        $file = $this->workingPath . 'rel/img/pages/1/x.png';
        file_put_contents($file, 'inside');

        (new Image)->deleteFiles('rel/img/pages/1/x.png');

        $this->assertFileDoesNotExist($file);
    }

    /**
     * Recursively delete only the folder provided
     */
    private function deleteFolder($folder)
    {
        if (! $folder || ! is_dir($folder)) {
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
