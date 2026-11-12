<?php namespace Redooor\Redminportal\App\Classes;

use Illuminate\Support\Facades\Config;

/*
 * Validate file names and types before they are stored.
 */
class UploadGuard
{
    const MAX_FILE_NAME_LENGTH = 150;

    /**
     * Return a safe file name or null when no usable name remains.
     *
     * @param string $name
     * @return string|null
     */
    public static function safeFileName($name)
    {
        if (! is_string($name) && ! is_numeric($name)) {
            return null;
        }

        $name = str_replace('\\', '/', (string) $name);
        $name = basename($name);

        if ($name == '') {
            return null;
        }

        if (preg_match('//u', $name)) {
            $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name);
            $name = preg_replace('/[^A-Za-z0-9._-]/u', '_', $name);
        } else {
            $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name);
            $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
        }

        if ($name === null || $name == '') {
            return null;
        }

        $lastDot = strrpos($name, '.');

        if ($lastDot === false || $lastDot === 0) {
            return null;
        }

        $extension = substr($name, $lastDot + 1);

        if ($extension == '') {
            return null;
        }

        $stem = str_replace('.', '_', substr($name, 0, $lastDot));
        $stem = ltrim($stem, '._');

        if ($stem == '') {
            return null;
        }

        return self::limitFileName($stem . '.' . $extension, $extension);
    }

    /**
     * Check whether a file extension is allowed for media uploads.
     *
     * @param string $fileName
     * @return bool
     */
    public static function isAllowedMediaExtension($fileName)
    {
        $allowed = Config::get('redminportal::media.allowed_extensions', array());

        if (! is_array($allowed)) {
            return false;
        }

        $extension = strtolower(pathinfo((string) $fileName, PATHINFO_EXTENSION));

        if ($extension == '') {
            return false;
        }

        foreach ($allowed as $item) {
            if (strtolower((string) $item) === $extension) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect the real image type of a local file.
     *
     * @param string $path
     * @return string|null
     */
    public static function imageExtension($path)
    {
        if (! is_string($path) || $path == '' || ! is_file($path) || ! is_readable($path)) {
            return null;
        }

        $imageType = @getimagesize($path);

        if (is_array($imageType) && isset($imageType[2])) {
            if ($imageType[2] === IMAGETYPE_JPEG) {
                return 'jpg';
            }

            if ($imageType[2] === IMAGETYPE_PNG) {
                return 'png';
            }

            if ($imageType[2] === IMAGETYPE_GIF) {
                return 'gif';
            }
        }

        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo !== false) {
                $mimeType = strtolower((string) @finfo_file($finfo, $path));
                @finfo_close($finfo);

                if ($mimeType == 'image/jpeg' || $mimeType == 'image/pjpeg') {
                    return 'jpg';
                }

                if ($mimeType == 'image/png' || $mimeType == 'image/x-png') {
                    return 'png';
                }

                if ($mimeType == 'image/gif') {
                    return 'gif';
                }
            }
        }

        return null;
    }

    /**
     * Cap a file name while keeping its extension.
     *
     * @param string $name
     * @param string $extension
     * @return string|null
     */
    private static function limitFileName($name, $extension)
    {
        if (strlen($name) <= self::MAX_FILE_NAME_LENGTH) {
            return $name;
        }

        $maxStemLength = self::MAX_FILE_NAME_LENGTH - strlen($extension) - 1;

        if ($maxStemLength < 1) {
            return null;
        }

        $stem = rtrim(substr($name, 0, $maxStemLength), '._');

        if ($stem == '') {
            return null;
        }

        return $stem . '.' . $extension;
    }
}
