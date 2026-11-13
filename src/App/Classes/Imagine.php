<?php namespace Redooor\Redminportal\App\Classes;
 
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File as Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Imagine\Filter\Basic\Autorotate;
use Imagine\Image\ImageInterface;
use Imagine\Image\Box;
use Redooor\Redminportal\App\Helpers\RHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Imagine
{
    /**
     * Instance of the Imagine package
     * @var Imagine\Gd\Imagine
     */
    protected $imagine;
 
    /**
     * Type of library used by the service
     * @var string
     */
    protected $library;
 
    /**
     * Initialize the image service
     * @return void
     */
    public function __construct()
    {
        $this->setLibrary();
    }
    
    protected function setLibrary()
    {
        if (! $this->imagine) {
            $this->library = Config::get('redminportal::image.library', 'gd');
 
            // Now create the instance
            if ($this->library == 'imagick') {
                $this->imagine = new \Imagine\Imagick\Imagine();
            } elseif ($this->library == 'gmagick') {
                $this->imagine = new \Imagine\Gmagick\Imagine();
            } elseif ($this->library == 'gd') {
                $this->imagine = new \Imagine\Gd\Imagine();
            } else {
                $this->imagine = new \Imagine\Gd\Imagine();
            }
        }
    }
    
    /**
     * Resize an image
     * @param  string  $url
     * @param  integer $width
     * @param  integer $height
     * @param  boolean $crop
     * @return string
     */
    public function resize($url, $width = 100, $height = null, $crop = false, $quality = 90)
    {
        if ($url) {
            // URL info
            $info = pathinfo($url);
     
            // The size
            if (! $height) {
                $height = $width;
            }
     
            // Quality
            $quality = Config::get('redminportal::image.quality', $quality);
     
            // Directories and file names
            $fileName       = $info['basename'];
            $sourceDirPath  = public_path() . '/' . $info['dirname'];
            $sourceFilePath = $sourceDirPath . '/' . $fileName;
            $targetDirName  = $width . 'x' . $height . ($crop ? '_crop' : '');
            $targetDirPath  = $sourceDirPath . '/' . $targetDirName . '/';
            $targetFilePath = $targetDirPath . $fileName;
            $targetUrl      = asset($info['dirname'] . '/' . $targetDirName . '/' . $fileName);
     
            // Create directory if missing
            try {
                // Create dir if missing
                if (! Filesystem::isDirectory($targetDirPath) and $targetDirPath) {
                    @Filesystem::makeDirectory($targetDirPath);
                }
     
                // Set the size
                $size = new Box($width, $height);
     
                // Now the mode
                $mode = $crop ? ImageInterface::THUMBNAIL_OUTBOUND : ImageInterface::THUMBNAIL_INSET;
     
                if (! Filesystem::exists($targetFilePath) or
                    (Filesystem::lastModified($targetFilePath) < Filesystem::lastModified($sourceFilePath))) {
                    $this->imagine->open($sourceFilePath)
                        ->thumbnail($size, $mode)
                        ->save($targetFilePath, array('quality' => $quality));
                }
            } catch (\Exception $e) {
                Log::error('[IMAGE SERVICE] Failed to resize image "' . $url . '" [' . $e->getMessage() . ']');
            }
     
            return $targetUrl;
        }
    }
    /**
    * Helper for creating thumbs
    * @param string $url
    * @param integer $width
    * @param integer $height
    * @return string
    */
    public function thumb($url, $width, $height = null)
    {
        return $this->resize($url, $width, $height, true);
    }

    /**
     * Upload an image to the public storage
     * @param  UploadedFile $file
     * @param string Directory (optional, defaults to null)
     * @param bool Whether to create all dimensions (optional, defaults to false)
     * @param bool Automatically appends number if file already exists (optional, defaults to true)
     * @return string
     */
    public function upload($file, $dir = null, $createDimensions = false, $autonumber = true)
    {
        if ($file) {
            $extension = UploadGuard::imageExtension($file->getPathname());

            if ($extension === null) {
                return null;
            }

            if ($dir !== null && (! is_string($dir) || ($dir !== '' && ! $this->isRelativeDirectory($dir)))) {
                return null;
            }

            $size = @getimagesize($file->getPathname());
            $maxPixels = (int) Config::get('redminportal::image.max_pixels', 40000000);

            if (! is_array($size) || ! isset($size[0], $size[1])) {
                Log::warning('[IMAGE SERVICE] Unable to read image dimensions "' . $file->getClientOriginalName() . '"');

                return null;
            }

            if ($maxPixels > 0 && ($size[0] * $size[1]) > $maxPixels) {
                Log::warning('[IMAGE SERVICE] Image exceeds the maximum pixel count "' . $file->getClientOriginalName() . '"');

                return null;
            }

            // Generate random dir
            if (! $dir) {
                $dir = Str::random(8);
            }

            $originalName = $file->getClientOriginalName();
            $stem = pathinfo($originalName, PATHINFO_FILENAME);
            $safeName = UploadGuard::safeFileName($stem . '.' . $extension);

            if ($safeName === null) {
                $stem = 'image';
            } else {
                $stem = pathinfo($safeName, PATHINFO_FILENAME);
            }

            $filename = $stem . '.' . $extension;

            // Get file info and try to move
            $destination = Config::get('redminportal::image.upload_path') . $dir;

            if ($autonumber) {
                $filename = $this->autoNumberFile($destination, $filename);
            }

            $path = RHelper::joinPaths(Config::get('redminportal::image.upload_dir'), $dir, $filename);
            $target = $destination . '/' . $filename;
            $temp = $target . '.tmp-' . Str::random(8);
            $uploaded = false;

            try {
                // Create destination dir if missing
                if (! is_dir($destination)) {
                    if (! @mkdir($destination, 0755, true) && ! is_dir($destination)) {
                        throw new \RuntimeException('Unable to create the upload directory');
                    }
                }

                $frames = null;

                if ($extension === 'gif') {
                    $frames = $this->gifFrameCount($file->getPathname());

                    if ($frames === null) {
                        Log::warning('[IMAGE SERVICE] Malformed GIF upload "' . $originalName . '"');

                        return null;
                    }
                }

                if ($extension === 'gif' && $frames > 1) {
                    // Confirm the first frame decodes before keeping the animation
                    $image = $this->imagine->open($file->getPathname());

                    if (! @copy($file->getPathname(), $temp)) {
                        throw new \RuntimeException('Unable to copy the animated image');
                    }
                } else {
                    $image = $this->imagine->open($file->getPathname());

                    // Correct the pixels before the EXIF orientation is stripped
                    if (in_array($extension, array('jpg', 'jpeg'), true)) {
                        $image = (new Autorotate)->apply($image);
                    }

                    $image->strip();

                    $options = array('format' => $extension);

                    if (in_array($extension, array('jpg', 'jpeg'), true)) {
                        $options['quality'] = (int) Config::get('redminportal::image.upload_quality', 90);
                    }

                    $image->save($temp, $options);
                }

                if (! is_file($temp) || ! @rename($temp, $target)) {
                    throw new \RuntimeException('Unable to store the uploaded image');
                }

                $uploaded = true;
            } catch (\Exception $e) {
                $this->deletePartialUpload($temp);

                Log::warning('[IMAGE SERVICE] Failed to process uploaded image "' . $originalName . '"');

                return null;
            }

            unset($image);

            if (! $uploaded || ! is_file($target)) {
                $this->deletePartialUpload($temp);

                Log::warning('[IMAGE SERVICE] Uploaded image was not saved "' . $originalName . '"');

                return null;
            }

            if ($createDimensions) {
                $this->createDimensions($path);
            }

            return $path;
        }
    }

    /**
     * Count the frames in a GIF by walking its block structure
     * @param  string $path
     * @return integer|null Frame count, or null when malformed or truncated
     */
    private function gifFrameCount($path)
    {
        $bytes = @file_get_contents($path);

        if ($bytes === false) {
            return null;
        }

        $length = strlen($bytes);
        $header = substr($bytes, 0, 6);

        if ($length < 13 || ($header !== 'GIF87a' && $header !== 'GIF89a')) {
            return null;
        }

        $position = 13;
        $packed = ord($bytes[10]);

        if ($packed & 0x80) {
            $position += 3 * (1 << (($packed & 0x07) + 1));
        }

        if ($position > $length) {
            return null;
        }

        $frames = 0;

        while ($position < $length) {
            $block = ord($bytes[$position]);
            $position++;

            if ($block === 0x3B) {
                return $frames;
            }

            if ($block === 0x21) {
                if ($position >= $length) {
                    return null;
                }

                $position++; // Extension label

                if (! $this->skipGifSubBlocks($bytes, $position, $length)) {
                    return null;
                }

                continue;
            }

            if ($block === 0x2C) {
                if ($position + 9 > $length) {
                    return null;
                }

                $packed = ord($bytes[$position + 8]);
                $position += 9;

                if ($packed & 0x80) {
                    $position += 3 * (1 << (($packed & 0x07) + 1));
                }

                if ($position >= $length) {
                    return null;
                }

                $position++; // LZW minimum code size

                if (! $this->skipGifSubBlocks($bytes, $position, $length)) {
                    return null;
                }

                $frames++;
                continue;
            }

            return null;
        }

        return null;
    }

    /**
     * Advance past a GIF data sub-block chain
     * @param  string  $bytes
     * @param  integer $position
     * @param  integer $length
     * @return bool
     */
    private function skipGifSubBlocks($bytes, &$position, $length)
    {
        while (true) {
            if ($position >= $length) {
                return false;
            }

            $size = ord($bytes[$position]);
            $position++;

            if ($size === 0) {
                return true;
            }

            $position += $size;

            if ($position > $length) {
                return false;
            }
        }
    }

    /**
     * Delete a partially written upload
     * @param  string $path
     * @return void
     */
    private function deletePartialUpload($path)
    {
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Check that a directory is relative and has no parent path segments
     * @param  string $dir
     * @return bool
     */
    private function isRelativeDirectory($dir)
    {
        if (strpos($dir, "\0") !== false) {
            return false;
        }

        if ($dir[0] === '/' || $dir[0] === '\\') {
            return false;
        }

        $segments = preg_split('/[\\\\\/]+/', $dir);

        return ! in_array('..', $segments, true);
    }
    
    /**
     * Automatically appends number if file already exists
     * @param  string $path
     * @param  string $filename
     * @return string New Filename
     */
    private function autoNumberFile($path, $filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $stem = pathinfo($filename, PATHINFO_FILENAME);
        
        $counter = 1;
        while (file_exists($path . '/' . $filename)) {
            $filename = $stem . '_' . $counter . '.' . $extension;
            $counter++;
        }
        
        return $filename;
    }
    
    /**
     * Creates image dimensions based on a configuration
     * @param  string $url
     * @param  array  $dimensions
     * @return void
     */
    public function createDimensions($url, $dimensions = array())
    {
        // Get default dimensions
        $defaultDimensions = Config::get('redminportal::image.dimensions');
     
        if (is_array($defaultDimensions)) {
            $dimensions = array_merge($defaultDimensions, $dimensions);
        }
     
        foreach ($dimensions as $dimension) {
            // Get dimmensions and quality
            $width   = (int) $dimension[0];
            $height  = isset($dimension[1]) ?  (int) $dimension[1] : $width;
            $crop    = isset($dimension[2]) ? (bool) $dimension[2] : false;
            $quality = isset($dimension[3]) ?  (int) $dimension[3] : Config::get('redminportal::image.quality');
     
            // Run resizer
            $this->resize($url, $width, $height, $crop, $quality);
        }
    }
    
    /**
     * Get URL of specified size
     * @param string $url
     * @param string $size
     * @return string $url
     */
    public function getUrl($url, $size = "thumb", $fullUrl = false)
    {
        // Get default dimensions
        $defaultDimensions = Config::get('redminportal::image.dimensions');
        
        // Get upload path
        $upload_path = Config::get('redminportal::image.upload_path');

        $dimension = $defaultDimensions[$size];

        // Get dimmensions and quality
        $width   = (int) $dimension[0];
        $height  = isset($dimension[1]) ?  (int) $dimension[1] : $width;
        $crop    = isset($dimension[2]) ? (bool) $dimension[2] : false;

        // URL info
        $info = pathinfo($url);

        // The size
        if (! $height) {
            $height = $width;
        }

        // Directories and file names
        $fileName       = $info['basename'];
        $targetDirName  = $width . 'x' . $height . ($crop ? '_crop' : '');
        $targetUrl      = RHelper::joinPaths($upload_path . $info['dirname'], $targetDirName, $fileName);
        $relativeUrl    = RHelper::joinPaths($info['dirname'], $targetDirName, $fileName);

        return ($fullUrl ? $targetUrl : $relativeUrl);
    }
}
