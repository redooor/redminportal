<?php namespace Redooor\Redminportal\App\Classes;

use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Helpers\RHelper;

/*
 * Map a stored image path to its on-disk location through the image configuration.
 */
class ImagePath
{
    /**
     * Resolve a stored path or an absolute filesystem path
     *
     * @param  string|null $path
     * @return string|null
     */
    public static function resolve($path)
    {
        if (! is_string($path) || $path === '' || strpos($path, "\0") !== false) {
            return null;
        }

        $path = str_replace('\\', '/', $path);

        // Never follow a stored path that tries to climb out of its directory
        if (self::hasParentSegment($path)) {
            return null;
        }

        if (self::isAbsolute($path)) {
            return self::normalizeAbsolute($path);
        }

        $path = self::normalizeRelative($path);
        $uploadDir = self::normalizeRelative((string) Config::get('redminportal::image.upload_dir'));

        if ($uploadDir !== '' && self::hasPathPrefix($path, $uploadDir)) {
            $suffix = ltrim(substr($path, strlen($uploadDir)), '/');
            $uploadPath = rtrim((string) Config::get('redminportal::image.upload_path'), '/\\');

            if ($uploadPath !== '') {
                return RHelper::joinPaths($uploadPath, $suffix);
            }
        }

        return public_path($path);
    }

    /**
     * Check that a stored path starts with the configured upload directory
     *
     * @param  string $path
     * @return bool
     */
    public static function isUnderUploadDir($path)
    {
        if (! is_string($path) || $path === '' || self::isAbsolute($path) || self::hasParentSegment($path)) {
            return false;
        }

        $path = self::normalizeRelative($path);
        $uploadDir = self::normalizeRelative((string) Config::get('redminportal::image.upload_dir'));

        return $uploadDir !== '' && self::hasPathPrefix($path, $uploadDir);
    }

    /**
     * Check that a resolved target is inside the configured upload path
     *
     * @param  string $target
     * @return bool
     */
    public static function isInsideUploadPath($target)
    {
        return self::isInsidePath($target, (string) Config::get('redminportal::image.upload_path'));
    }

    /**
     * Check that a resolved target is inside a root directory
     *
     * @param  string $target
     * @param  string $root
     * @return bool
     */
    private static function isInsidePath($target, $root)
    {
        if ($root === '') {
            return false;
        }

        $root = self::canonical($root);
        $target = self::canonical($target);

        if ($root === null || $target === null || $target === $root) {
            return false;
        }

        return strpos($target, rtrim($root, '/') . '/') === 0;
    }

    /**
     * Get the canonical form of an absolute path, following symlinks when possible
     *
     * @param  string $path
     * @return string|null
     */
    private static function canonical($path)
    {
        $path = str_replace('\\', '/', (string) $path);

        if ($path === '' || self::hasParentSegment($path) || ! self::isAbsolute($path)) {
            return null;
        }

        $real = realpath($path);

        if ($real !== false) {
            return rtrim(str_replace('\\', '/', $real), '/');
        }

        return rtrim(self::normalizeAbsolute($path), '/');
    }

    /**
     * Normalize an absolute path without resolving symlinks
     *
     * @param  string $path
     * @return string
     */
    private static function normalizeAbsolute($path)
    {
        $path = str_replace('\\', '/', $path);
        $drive = '';

        if (preg_match('#^[A-Za-z]:/#', $path)) {
            $drive = substr($path, 0, 2);
            $path = substr($path, 2);
        }

        $segments = array();

        foreach (explode('/', $path) as $segment) {
            if ($segment !== '' && $segment !== '.') {
                $segments[] = $segment;
            }
        }

        return $drive . '/' . implode('/', $segments);
    }

    /**
     * Normalize a relative path
     *
     * @param  string $path
     * @return string
     */
    private static function normalizeRelative($path)
    {
        $path = str_replace('\\', '/', $path);
        $segments = array();

        foreach (explode('/', $path) as $segment) {
            if ($segment !== '' && $segment !== '.') {
                $segments[] = $segment;
            }
        }

        return implode('/', $segments);
    }

    /**
     * Check whether a path starts with a directory at a path boundary
     *
     * @param  string $path
     * @param  string $prefix
     * @return bool
     */
    private static function hasPathPrefix($path, $prefix)
    {
        $path = rtrim($path, '/');
        $prefix = rtrim($prefix, '/');

        return $path === $prefix || strpos($path, $prefix . '/') === 0;
    }

    /**
     * Check whether a path contains a parent directory segment
     *
     * @param  string $path
     * @return bool
     */
    private static function hasParentSegment($path)
    {
        return in_array('..', explode('/', str_replace('\\', '/', $path)), true);
    }

    /**
     * Check whether a path is absolute
     *
     * @param  string $path
     * @return bool
     */
    public static function isAbsolute($path)
    {
        return isset($path[0])
            && ($path[0] === '/' || preg_match('#^[A-Za-z]:/#', $path) === 1);
    }
}
