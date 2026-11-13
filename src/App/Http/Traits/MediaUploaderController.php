<?php namespace Redooor\Redminportal\App\Http\Traits;

/*
 * Add uploading capability to controller
 */

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Redooor\Redminportal\App\Classes\File as FileInfo;
use Redooor\Redminportal\App\Classes\UploadGuard;

trait MediaUploaderController
{
    /* -- Requires --
    protected $model;
    ----------------- */
    
    public function getUploadform($sid)
    {
        $model = $this->model->find($sid);

        if ($model == null) {
            return redirect('admin/medias');
        }
        
        $data = [
            'model' => $model
        ];

        return view('redminportal::medias/upload', $data);
    }
    
    public function postUpload($sid)
    {
        $model = $this->model->find($sid);
        $result = [
            "code" => 0,
            "message" => "Unknown error."
        ];

        if ($model == null) {
            $result["message"] = Lang::get('redminportal::messages.error_find_no_such_record');
            return json_encode($result);
        } elseif (empty($_FILES) || $_FILES['file']['error']) {
            $result["message"] = Lang::get('redminportal::messages.upload_error_failed_no_file_found');
            return json_encode($result);
        }

        $uploadedName = isset($_FILES["file"]["name"]) ? $_FILES["file"]["name"] : '';
        $fileName = UploadGuard::safeFileName($this->checkRequest("name", $uploadedName));

        if ($fileName === null) {
            $fileName = UploadGuard::safeFileName($uploadedName);
        }

        if ($fileName === null || ! UploadGuard::isAllowedMediaExtension($fileName)) {
            $result["message"] = Lang::get('redminportal::messages.upload_error_file_type_not_allowed');
            return json_encode($result);
        }

        if (isset($_FILES["file"]["mock"]) and $_FILES["file"]["mock"]) {
            // For Unit testing
            $this->saveMedia($model, $_FILES["file"]["pathinfo"] . $fileName);
            // Return test successful
            $result["code"] = 1;
            $result["message"] = Lang::get('redminportal::messages.upload_success_test');
            return json_encode($result);
        }
        
        return $this->uploadChunk($model, $fileName);
    }
    
    /**
     * Upload file chunk
     *
     * @param object Media
     * @param string File name (optional)
     **/
    protected function uploadChunk($model, $fileName = null)
    {
        $result = [
            "code" => 0,
            "message" => "Unknown error."
        ];
        
        if ($fileName === null) {
            $uploadedName = isset($_FILES["file"]["name"]) ? $_FILES["file"]["name"] : '';
            $fileName = UploadGuard::safeFileName($this->checkRequest("name", $uploadedName));

            if ($fileName === null) {
                $fileName = UploadGuard::safeFileName($uploadedName);
            }
        }

        if ($fileName === null || ! UploadGuard::isAllowedMediaExtension($fileName)) {
            $result["message"] = Lang::get('redminportal::messages.upload_error_file_type_not_allowed');
            return json_encode($result);
        }
        
        $sid = $model->id;
        $model_subfolder = (int) $model->category_id . '/' . (int) $sid;
        $model_tmp_folder = public_path() . '/assets/medias/tmp/' . $model_subfolder;
        $model_folder = public_path() . '/assets/medias/' . $model_subfolder;
        
        $filePath = $model_tmp_folder . "/$fileName";
        
        $chunk = $this->checkIntegerRequest("chunk", 0);
        $chunksProvided = isset($_REQUEST['chunks']);
        $chunks = $this->checkIntegerRequest("chunks", null);

        if ($chunk === null || $chunk < 0) {
            $result["message"] = Lang::get('redminportal::messages.upload_error_failed_no_file_found');
            return json_encode($result);
        }

        if ($chunksProvided && $chunks === null) {
            $result["message"] = Lang::get('redminportal::messages.upload_error_failed_no_file_found');
            return json_encode($result);
        }

        if ($chunks === null) {
            $chunks = 1;
        } elseif ($chunks < 1 || $chunk >= $chunks) {
            $result["message"] = Lang::get('redminportal::messages.upload_error_failed_no_file_found');
            return json_encode($result);
        }

        $partPath = "{$filePath}.part";
        $lockPath = sys_get_temp_dir() . '/redmin-upload-' . md5($model_tmp_folder) . '.lock';
        $lock = @fopen($lockPath, 'c');

        if (! $lock || ! @flock($lock, LOCK_EX | LOCK_NB)) {
            if ($lock) {
                @fclose($lock);
            }

            // Another chunk of this media is being written; ask the uploader to send this one again
            $result["message"] = Lang::get('redminportal::messages.upload_error_failed_to_open_output_stream');
            return response(json_encode($result), 503);
        }

        try {
            return $this->processUploadChunk(
                $model,
                $fileName,
                $filePath,
                $partPath,
                $model_tmp_folder,
                $model_folder,
                $chunk,
                $chunks
            );
        } finally {
            @flock($lock, LOCK_UN);
            @fclose($lock);
        }
    }

    /**
     * Process a file chunk while its lock is held
     *
     * @param object Media
     * @param string File name
     * @param string Temporary file path
     * @param string Partial file path
     * @param string Temporary folder
     * @param string Media folder
     * @param integer Chunk index
     * @param integer Number of chunks
     *
     * @return string JSON response
     **/
    protected function processUploadChunk($model, $fileName, $filePath, $partPath, $model_tmp_folder, $model_folder, $chunk, $chunks)
    {
        $result = [
            "code" => 0,
            "message" => "Unknown error."
        ];

        if ($chunk > 0 && ! file_exists($partPath)) {
            $result["message"] = Lang::get('redminportal::messages.upload_error_failed_no_file_found');
            return json_encode($result);
        }
        
        // Create the directory
        $this->checkFolder($model_tmp_folder);

        $maxSize = (int) Config::get('redminportal::media.max_size', 1073741824);
        $incomingSize = @filesize($_FILES['file']['tmp_name']);

        if ($incomingSize === false) {
            $incomingSize = isset($_FILES['file']['size']) ? (int) $_FILES['file']['size'] : 0;
        }

        $existingSize = ($chunk > 0 && file_exists($partPath)) ? (int) @filesize($partPath) : 0;

        if ($maxSize > 0 && ($existingSize + $incomingSize) > $maxSize) {
            @unlink($partPath);
            $result["message"] = Lang::get('redminportal::messages.upload_error_file_too_large');
            return json_encode($result);
        }

        // Open temp file
        $out = @fopen($partPath, $chunk == 0 ? "wb" : "ab");
        if ($out) {
            // Read binary input stream and append it to temp file
            $sin = @fopen($_FILES['file']['tmp_name'], "rb");

            if ($sin) {
                while ($buff = fread($sin, 4096)) {
                    fwrite($out, $buff);
                }
            } else {
                @fclose($out);
                $result["message"] = Lang::get('redminportal::messages.upload_error_failed_to_open_input_stream');
                return json_encode($result);
            }

            @fclose($sin);
            @fclose($out);

            @unlink($_FILES['file']['tmp_name']);
        } else {
            $result["message"] = Lang::get('redminportal::messages.upload_error_failed_to_open_output_stream');
            return json_encode($result);
        }

        // Check if file has been uploaded
        if ($chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            if (! @rename($partPath, $filePath)) {
                $result["message"] = Lang::get('redminportal::messages.upload_error_failed_to_open_output_stream');
                return json_encode($result);
            }

            $expectedFolder = realpath($model_tmp_folder);
            $finalFile = realpath($filePath);

            if ($expectedFolder === false || $finalFile === false
                || dirname($finalFile) !== $expectedFolder) {
                @unlink($filePath);
                $result["message"] = Lang::get('redminportal::messages.upload_error_failed_to_open_output_stream');
                return json_encode($result);
            }

            $this->saveMedia($model, $filePath);

            $file = new FileInfo($filePath);
            
            // Delete old media
            $file->deleteFiles($model_folder);

            // Create the directory
            $this->checkFolder($model_folder);

            $mediaRoot = realpath(public_path() . '/assets/medias');
            $destinationFolder = realpath($model_folder);

            if ($mediaRoot === false || $destinationFolder === false
                || ($destinationFolder !== $mediaRoot
                    && strpos($destinationFolder, $mediaRoot . DIRECTORY_SEPARATOR) !== 0)) {
                @unlink($filePath);
                $result["message"] = Lang::get('redminportal::messages.upload_error_failed_to_open_output_stream');
                return json_encode($result);
            }

            $file->move($model_folder, $fileName);

            // Remove the tmp folder when nothing else is waiting in it
            @rmdir($model_tmp_folder);
        }
        
        $result["code"] = 1;
        $result["message"] = Lang::get('redminportal::messages.upload_success');
        return json_encode($result);
    }
    
    /**
     * Check if folder exist, otherwise create folder
     *
     * @param string Folder name
     **/
    protected function checkFolder($folderPath)
    {
        // Create the directory if not exist
        if (! file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
        
        return;
    }
    
    /**
     * Get value by request name or return default
     *
     * @param string Request Name
     * @param string Default value (optional)
     *
     * @return
     **/
    protected function checkRequest($name, $default = 0)
    {
        if ($name == 'name') {
            return (isset($_REQUEST[$name]) ? ($_REQUEST[$name]) : $default);
        }
        
        return (isset($_REQUEST[$name]) ? intval($_REQUEST[$name]) : $default);
    }
    
    /**
     * Get a non-negative integer request value or null if it is invalid
     *
     * @param string Request Name
     * @param integer Default value (optional)
     *
     * @return integer|null
     **/
    protected function checkIntegerRequest($name, $default = null)
    {
        if (! isset($_REQUEST[$name])) {
            return $default;
        }

        $value = $_REQUEST[$name];

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^\d+$/', $value)) {
            return (int) $value;
        }

        return null;
    }
    
    /**
     * Save Media info into database
     *
     * @param object Media
     * @param string File path
     *
     * @return bool True if successful
     **/
    protected function saveMedia($model, $filePath)
    {
        $file = new FileInfo($filePath);

        // Save the media link
        $model->path = $file->getFilename();
        
        // Get mime type of the file
        $model->mimetype = $file->getMimeType();
        
        // Get playtime of the file
        $playtime = $file->getPlaytime();
        
        $options = json_decode($model->options);
        if (! is_array($options)) {
            $options = array(); // Create a new array
        }
        $options['duration'] = ($playtime) ? $playtime : '';
        $model->options = json_encode($options);
        
        try {
            $model->save();
        } catch (Exception $exp) {
            return false;
        }
        
        return true;
    }
}
