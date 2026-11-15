<?php namespace Redooor\Redminportal\Test;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Models\Image;

/**
 * Helpers shared by the admin controller characterization tests.
 *
 * Every helper records current behaviour only. It is intentionally generic so
 * later batches (Category, Module, Media, Bundle, Product) can reuse it through
 * $this->page and a model class.
 */
trait CharacterizationSupport
{
    protected $characterizationUploadPath;
    protected $characterizationTempFiles = array();

    /**
     * Post to $this->page . '/store'
     */
    protected function postStore(array $input, array $files = array(), array $server = array())
    {
        return $this->call('POST', $this->page . '/store', $input, array(), $files, $server);
    }

    /**
     * Post to store while asking for JSON like an API client would
     */
    protected function postStoreAsJson(array $input, array $files = array())
    {
        return $this->postStore($input, $files, array('HTTP_ACCEPT' => 'application/json'));
    }

    /**
     * The default error bag for the last request
     */
    protected function defaultErrors()
    {
        return session('errors')->getBag('default');
    }

    /**
     * Assert a validation failure, its exact messages and that files are not flashed
     */
    protected function assertValidationFailure(
        $expectedRedirect,
        array $input,
        array $messages,
        array $files = array(),
        array $server = array()
    ) {
        $this->postStore($input, $files, $server);

        $this->assertRedirectedTo($expectedRedirect);
        $this->assertSessionHasErrors(array_keys($messages), null, 'default');

        foreach ($messages as $field => $message) {
            $this->assertSame($message, $this->defaultErrors()->first($field));
        }

        $this->assertErrorBagIsDefaultOnly();
        $this->assertOldInputHasNoFiles();
    }

    /**
     * Assert the exact first message for a field in a bag
     */
    protected function assertErrorMessage($field, $message, $bag = 'default')
    {
        $this->assertSame($message, session('errors')->getBag($bag)->first($field));
    }

    /**
     * The default bag is used, a posted _error_bag does not create a custom bag
     */
    protected function assertErrorBagIsDefaultOnly()
    {
        $this->assertArrayHasKey('default', session('errors')->getBags());
        $this->assertSame(array(), session('errors')->getBag('custom')->all());
    }

    /**
     * Flashed old input for the last redirect
     */
    protected function oldInput()
    {
        return session()->getOldInput();
    }

    /**
     * Assert scalar old input values
     */
    protected function assertOldInput(array $expected)
    {
        $old = $this->oldInput();

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $old, "Old input is missing [{$key}].");
            $this->assertEquals($value, $old[$key], "Old input [{$key}] does not match.");
        }
    }

    /**
     * Old input never contains uploaded files
     */
    protected function assertOldInputHasNoFiles()
    {
        $old = $this->oldInput();

        foreach (array('image', 'images') as $field) {
            $this->assertArrayNotHasKey($field, $old, "Old input should not contain [{$field}].");
        }
    }

    /**
     * Count all rows of a model
     */
    protected function countRows($modelClass)
    {
        return $modelClass::count();
    }

    /**
     * Create and save a model through its class
     */
    protected function createModel($modelClass, array $attributes)
    {
        $model = new $modelClass;

        foreach ($attributes as $key => $value) {
            $model->$key = $value;
        }

        $model->save();

        return $model;
    }

    /**
     * Assert one translation row per expected language with exact decoded content
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function assertTranslations($model, array $expected)
    {
        $translations = $model->translations()->get();

        $this->assertCount(count($expected), $translations);

        $actual = array();
        foreach ($translations as $translation) {
            $actual[$translation->lang] = json_decode($translation->content, true);
        }

        ksort($actual);
        ksort($expected);

        $this->assertSame($expected, $actual);

        return $translations;
    }

    /**
     * Tag names ordered by tag id
     */
    protected function tagNames($model)
    {
        return $model->tags()->orderBy('tags.id')->pluck('name')->all();
    }

    /**
     * Configure a throw-away image upload directory
     */
    protected function configureImageUploads()
    {
        $this->characterizationUploadPath = sys_get_temp_dir() . '/redmin_characterization_' . uniqid() . '/';
        Config::set('redminportal::image.upload_path', $this->characterizationUploadPath);
    }

    /**
     * Build a real uploaded image file
     */
    protected function uploadedImage($name = 'image.png', $type = 'png')
    {
        $path = $this->characterizationTempFile($type);
        $image = imagecreatetruecolor(10, 10);

        if ($type === 'png') {
            imagepng($image, $path);
        } elseif ($type === 'jpg' || $type === 'jpeg') {
            imagejpeg($image, $path);
            $type = 'jpeg';
        } elseif ($type === 'gif') {
            imagegif($image, $path);
        }

        imagedestroy($image);

        return new UploadedFile($path, $name, 'image/' . $type, null, true);
    }

    /**
     * Build an uploaded file that is not a real image
     */
    protected function invalidUploadedFile($name = 'invalid.png')
    {
        $path = $this->characterizationTempFile('png');
        file_put_contents($path, 'this is not an image');

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    /**
     * Attach a saved Image row to a model
     */
    protected function attachImage($model, $path = 'dummy.jpg')
    {
        $image = new Image;
        $image->path = $path;
        $model->images()->save($image);

        return $image;
    }

    /**
     * Remove the temporary image upload directory and source files
     */
    protected function cleanupImageUploads()
    {
        foreach ($this->characterizationTempFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        if ($this->characterizationUploadPath) {
            $this->deleteCharacterizationFolder($this->characterizationUploadPath);
        }

        $this->characterizationTempFiles = array();
        $this->characterizationUploadPath = null;
    }

    /**
     * Create an empty temporary file with the given extension
     */
    private function characterizationTempFile($extension)
    {
        $path = tempnam(sys_get_temp_dir(), 'redmin_characterization_file_');

        if ($path === false) {
            $this->fail('Unable to create a temporary file.');
        }

        if ($extension !== '') {
            $newPath = $path . '.' . $extension;
            rename($path, $newPath);
            $path = $newPath;
        }

        $this->characterizationTempFiles[] = $path;

        return $path;
    }

    /**
     * Recursively delete only the folder provided
     */
    private function deleteCharacterizationFolder($folder)
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
