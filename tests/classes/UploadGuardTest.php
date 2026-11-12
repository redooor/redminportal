<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Classes\UploadGuard;

class UploadGuardTest extends RedminTestCase
{
    private $tempFiles = array();

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        $this->tempFiles = array();

        parent::tearDown();
    }

    /**
     * Test (Pass): directory separators and traversal are removed
     */
    public function testSafeFileNameRemovesDirectoryTraversal()
    {
        $this->assertSame('x.mp3', UploadGuard::safeFileName('../../x.mp3'));
        $this->assertSame('x.mp3', UploadGuard::safeFileName('..\\..\\x.mp3'));
        $this->assertSame('passwd.mp3', UploadGuard::safeFileName('/etc/passwd.mp3'));
    }

    /**
     * Test (Pass): only the last dot is kept
     */
    public function testSafeFileNameReplacesInnerDots()
    {
        $this->assertSame('a_php.jpg', UploadGuard::safeFileName('a.php.jpg'));
        $this->assertSame('shell.php', UploadGuard::safeFileName('shell.php'));
    }

    /**
     * Test (Fail): hidden files, empty names and names without extensions are rejected
     */
    public function testSafeFileNameRejectsUnusableNames()
    {
        $this->assertNull(UploadGuard::safeFileName('.htaccess'));
        $this->assertNull(UploadGuard::safeFileName(''));
        $this->assertNull(UploadGuard::safeFileName('...'));
        $this->assertNull(UploadGuard::safeFileName('noextension'));
    }

    /**
     * Test (Pass): NUL and control characters are removed
     */
    public function testSafeFileNameRemovesControlCharacters()
    {
        $this->assertSame('badname.mp3', UploadGuard::safeFileName("bad\0name.mp3"));
        $this->assertSame('badname.mp3', UploadGuard::safeFileName("bad\nname.mp3"));
    }

    /**
     * Test (Pass): unsafe characters are replaced
     */
    public function testSafeFileNameReplacesUnsafeCharacters()
    {
        $this->assertSame('ok_name.mp3', UploadGuard::safeFileName('ok name.mp3'));
        $this->assertSame('abc___song.mp3', UploadGuard::safeFileName('abc我的 song.mp3'));
    }

    /**
     * Test (Pass): long names are capped but keep the extension
     */
    public function testSafeFileNameLimitsLengthAndKeepsExtension()
    {
        $name = UploadGuard::safeFileName(str_repeat('a', 300) . '.mp3');

        $this->assertNotNull($name);
        $this->assertLessThanOrEqual(150, strlen($name));
        $this->assertStringEndsWith('.mp3', $name);
    }

    /**
     * Test (Pass): leading dots and underscores are stripped
     */
    public function testSafeFileNameStripsLeadingDotsAndUnderscores()
    {
        $this->assertSame('x.mp3', UploadGuard::safeFileName('___x.mp3'));
        $this->assertSame('x.mp3', UploadGuard::safeFileName('...x.mp3'));
    }

    /**
     * Test (Pass): allowed media extensions are matched case-insensitively
     */
    public function testAllowedMediaExtensions()
    {
        $this->assertTrue(UploadGuard::isAllowedMediaExtension('song.mp3'));
        $this->assertTrue(UploadGuard::isAllowedMediaExtension('SONG.MP3'));
        $this->assertTrue(UploadGuard::isAllowedMediaExtension('document.pdf'));
        $this->assertTrue(UploadGuard::isAllowedMediaExtension('picture.png'));
    }

    /**
     * Test (Fail): executable, markup and script extensions are rejected
     */
    public function testForbiddenMediaExtensions()
    {
        $this->assertFalse(UploadGuard::isAllowedMediaExtension('shell.php'));
        $this->assertFalse(UploadGuard::isAllowedMediaExtension('shell.phtml'));
        $this->assertFalse(UploadGuard::isAllowedMediaExtension('page.html'));
        $this->assertFalse(UploadGuard::isAllowedMediaExtension('vector.svg'));
        $this->assertFalse(UploadGuard::isAllowedMediaExtension('script.js'));
        $this->assertFalse(UploadGuard::isAllowedMediaExtension('.htaccess'));
    }

    /**
     * Test (Pass): real image types are detected
     */
    public function testImageExtensionDetectsRealImages()
    {
        $this->assertSame('png', UploadGuard::imageExtension($this->createImage('png')));
        $this->assertSame('jpg', UploadGuard::imageExtension($this->createImage('jpg')));
        $this->assertSame('gif', UploadGuard::imageExtension($this->createImage('gif')));
    }

    /**
     * Test (Fail): markup and SVG content are not treated as images
     */
    public function testImageExtensionRejectsNonImages()
    {
        $html = $this->tempFile('html');
        file_put_contents($html, '<html><body>not an image</body></html>');
        $this->assertNull(UploadGuard::imageExtension($html));

        $svg = $this->tempFile('png');
        file_put_contents($svg, '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        $this->assertNull(UploadGuard::imageExtension($svg));
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
        $path = tempnam(sys_get_temp_dir(), 'redmin_guard_');

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
}
