<?php namespace Redooor\Redminportal\App\Classes;

use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Illuminate\Support\Facades\File as IlluminateFile;
use getID3;

/*
 * Extends the file information with media meta
 */
class File extends SymfonyFile
{
    protected $getID3Info;
    
    /**
     * Constructs a new file from the given path.
     *
     * @param string $path      The path to the file
     * @param bool   $checkPath Whether to check the path or not
     *
     * @throws FileNotFoundException If the given path is not a file
     */
    public function __construct($path, $checkPath = true)
    {
        $this->getID3Info = new getID3();
        
        parent::__construct($path, $checkPath);
    }
    
    /**
     * Returns the media meta of the file.
     *
     * The media meta is retrieved using GetId3.
     * Refer to http://www.getid3.org/
     *
     * @return array|null Retrieved Meta data
     */
    public function retrieveId3()
    {
        if (file_exists($this)) {
            return $this->getID3Info->analyze($this);
        }

        return null;
    }
    
    /**
     * Returns the playtime of the media.
     *
     * The media playtime meta is retrieved using GetId3.
     * Refer to http://www.getid3.org/
     *
     * @return string|null Retrieved playtime info
     */
    public function getPlaytime()
    {
        if (file_exists($this)) {
            $ThisFileInfo = $this->getID3Info->analyze($this);
            
            $len = @$ThisFileInfo['playtime_string'];
            
            if ($len != null) {
                return $len;
            }
        }

        return null;
    }
    
    /*
     * Delete directories and files recursively
     *
     * @param string Target path
     */
    public function deleteFiles($target)
    {
        if (is_dir($target)) {
            $files = glob($target . '*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned

            foreach ($files as $file) {
                $this->deleteFiles($file);
            }

            if (is_dir($target)) {
                rmdir($target);
            }

        } elseif (is_file($target)) {
            unlink($target);
        }
    }
}
