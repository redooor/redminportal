<?php namespace Redooor\Redminportal\App\Http\Traits;

/*
 * Add uploading capability to controller
 */

use Exception;
use Illuminate\Support\Facades\Lang;
use Redooor\Redminportal\App\Classes\File as FileInfo;

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
        
        $fileName = $this->checkRequest("name", $_FILES["file"]["name"]);
        
        if ($model == null) {
            $result["message"] = Lang::get('redminportal::messages.error_find_no_such_record');
            return json_encode($result);
        } elseif (empty($_FILES) || $_FILES['file']['error']) {
            $result["message"] = Lang::get('redminportal::messages.upload_error_failed_no_file_found');
            return json_encode($result);
        } elseif (isset($_FILES["file"]["mock"]) and $_FILES["file"]["mock"]) {
            // For Unit testing
            $this->saveMedia($model, $_FILES["file"]["pathinfo"] . $fileName);
            // Return test successful
            $result["code"] = 1;
            $result["message"] = Lang::get('redminportal::messages.upload_success_test');
            return json_encode($result);
        }
        
        return $this->uploadChunk($model);
    }
    
    /**
     * Upload file chunk
     *
     * @param object Media
     **/
    protected function uploadChunk($model)
    {
        $result = [
            "code" => 0,
            "message" => "Unknown error."
        ];
        
        $sid = $model->id;
        $model_subfolder = $model->category_id . '/' . $sid;
        $model_tmp_folder = public_path() . '/assets/medias/tmp/' . $model_subfolder;
        $model_folder = public_path() . '/assets/medias/' . $model_subfolder;
        
        $fileName = $this->checkRequest("name", $_FILES["file"]["name"]);
        $filePath = $model_tmp_folder . "/$fileName";
        
        $chunk = $this->checkRequest("chunk");
        $chunks = $this->checkRequest("chunks", null);
        
        // Create the directory
        $this->checkFolder($model_tmp_folder);

        // Open temp file
        $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
        if ($out) {
            // Read binary input stream and append it to temp file
            $sin = @fopen($_FILES['file']['tmp_name'], "rb");

            if ($sin) {
                while ($buff = fread($sin, 4096)) {
                    fwrite($out, $buff);
                }
            } else {
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
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            rename("{$filePath}.part", $filePath);

            $this->saveMedia($model, $filePath);

            $file = new FileInfo($filePath);
            
            // Delete old media
            $file->deleteFiles($model_folder);

            // Create the directory
            $this->checkFolder($model_folder);

            $file->move($model_folder, $fileName);

            // Delete tmp media
            $file->deleteFiles($model_tmp_folder);
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
