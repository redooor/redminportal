<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Media;
use Redooor\Redminportal\App\Models\ModuleMediaMembership;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Tag;
use Redooor\Redminportal\App\Helpers\RImage;
use \GetId3\GetId3Core as GetId3;

class MediaController extends Controller
{
    public function getIndex()
    {
        $medias = Media::orderBy('created_at', 'desc')->orderBy('category_id')->orderBy('name')->paginate(20);

        return view('redminportal::medias/view')->with('medias', $medias);
    }

    public function getCreate()
    {
        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        return view('redminportal::medias/create')
            ->with('categories', $categories);
    }

    public function getEdit($sid)
    {
        // Find the media using the user id
        $media = Media::find($sid);

        // No such id
        if ($media == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The media cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/medias')->withErrors($errors);
        }

        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        $tagString = "";
        foreach ($media->tags as $tag) {
            if (! empty($tagString)) {
                $tagString .= ",";
            }

            $tagString .= $tag->name;
        }

        if (empty($media->options)) {
            $media_cn = (object) array(
                'name'                  => $media->name,
                'short_description'     => $media->short_description,
                'long_description'      => $media->long_description
            );
        } else {
            $media_cn = json_decode($media->options);
        }

        return view('redminportal::medias/edit')
            ->with('media', $media)
            ->with('media_cn', $media_cn)
            ->with('categories', $categories)
            ->with('tagString', $tagString)
            ->with('imagine', new RImage);
    }

    public function postStore()
    {
        $sid = \Input::get('id');

        /*
         * Validate
         */
        $rules = array(
            'image'             => 'mimes:jpg,jpeg,png,gif|max:500',
            'name'              => 'required|unique:medias,name' . (isset($sid) ? ',' . $sid : ''),
            'short_description' => 'required',
            'sku'               => 'required|alpha_dash|unique:medias,sku' . (isset($sid) ? ',' . $sid : ''),
            'category_id'       => 'required',
            'tags'              => 'regex:/^[a-z,0-9 -]+$/i',
        );

        $validation = \Validator::make(\Input::all(), $rules);

        if ($validation->passes()) {
            $name               = \Input::get('name');
            $sku                = \Input::get('sku');
            $short_description  = \Input::get('short_description');
            $long_description   = \Input::get('long_description');
            $image              = \Input::file('image');
            $featured           = (\Input::get('featured') == '' ? false : true);
            $active             = (\Input::get('active') == '' ? false : true);
            $category_id        = \Input::get('category_id');
            $tags               = \Input::get('tags');

            $cn_name               = \Input::get('cn_name');
            $cn_short_description  = \Input::get('cn_short_description');
            $cn_long_description   = \Input::get('cn_long_description');

            $options = array(
                'name'                  => $cn_name,
                'short_description'     => $cn_short_description,
                'long_description'      => $cn_long_description
            );

            $media = (isset($sid) ? Media::find($sid) : new Media);
            
            if ($media == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The media cannot be found because it does not exist or may have been deleted."
                );
                return redirect('/admin/medias')->withErrors($errors);
            }
            
            $media->name = $name;
            $media->sku = $sku;
            $media->short_description = $short_description;
            $media->long_description = $long_description;
            $media->featured = $featured;
            $media->active = $active;
            $media->options = json_encode($options);

            if (isset($sid)) {
                // Check if category has changed
                if ($media->category_id != $category_id) {
                    $old_cat_folder = public_path() . '/assets/medias/' . $media->category_id . '/' . $sid;
                    $new_cat_folder = public_path() . '/assets/medias/' . $category_id . '/' . $sid;
                    // Create the directory
                    if (!file_exists($new_cat_folder)) {
                        mkdir($new_cat_folder, 0777, true);
                    }
                    // Copy existing media to new category
                    \File::copyDirectory($old_cat_folder, $new_cat_folder);
                    // Delete old media folder
                    \File::deleteDirectory($old_cat_folder);
                }
            } else {
                $media->path = 'broken.pdf';
            }

            $media->category_id = $category_id;

            // Create or save changes
            $media->save();
            
            if (! empty($tags)) {
                // Delete old tags
                $media->tags()->detach();

                // Save tags
                foreach (explode(',', $tags) as $tagName) {
                    Tag::addTag($media, $tagName);
                }
            }

            if (\Input::hasFile('image')) {
                //Upload the file
                $helper_image = new RImage;
                $filename = $helper_image->upload($image, 'medias/' . $media->id, true);

                if ($filename) {
                    // create photo
                    $newimage = new Image;
                    $newimage->path = $filename;

                    // save photo to the loaded model
                    $media->images()->save($newimage);
                }
            }
        //if it validate
        } else {
            if (isset($sid)) {
                return redirect('admin/medias/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return redirect('admin/medias/create')->withErrors($validation)->withInput();
            }
        }

        return redirect('admin/medias');
    }

    public function getDelete($sid)
    {
        // Find the media using the user id
        $media = Media::find($sid);

        if ($media == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "We are having problem deleting this entry. Please try again.");
            return redirect('admin/medias')->withErrors($errors);
        }

        // Check if used by Module
        $mmms = ModuleMediaMembership::where('media_id', $sid)->get();
        if (count($mmms) > 0) {
            // Check for orphan
            $orphan = true;
            foreach ($mmms as $mmm) {
                if ($mmm->module != null) {
                    $orphan = false;
                    break;
                }
            }
            if (! $orphan) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add('deleteError', "This media cannot be deleted because it is link to a module.");
                return redirect('admin/medias')->withErrors($errors);
            }
        }
        
        // Delete the media
        $media->delete();

        return redirect('admin/medias');
    }

    public function getUploadform($sid)
    {
        $media = Media::find($sid);

        if ($media == null) {
            return redirect('admin/medias');
        }

        return view('redminportal::medias/upload')
            ->with('media', $media);
    }
    
    public function postUpload($sid)
    {
        $media = Media::find($sid);

        if ($media == null) {
            die('{"OK": 0, "info": "Unable to find this media record in the database. It could have been deleted."}');
        }

        $media_tmp_folder = public_path() . '/assets/medias/tmp/' . $media->category_id . '/' . $sid;
        $media_folder = public_path() . '/assets/medias/' . $media->category_id . '/' . $sid;

        if (empty($_FILES) || $_FILES['file']['error']) {
            die('{"OK": 0, "info": "Failed to move uploaded file."}');
        }

        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];
        //$filePath = public_path() . '/assets/medias' . "/$fileName";
        $filePath = $media_tmp_folder . "/$fileName";

        // Create the directory
        if (!file_exists($media_tmp_folder)) {
            mkdir($media_tmp_folder, 0777, true);
        }
        
        // For Unit testing
        if ($fileName == 'foo113a.pdf') {
            die('{"OK": 1, "info": "Upload successful."}');
        }

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
                die('{"OK": 0, "info": "Failed to open input stream."}');
            }

            @fclose($sin);
            @fclose($out);

            @unlink($_FILES['file']['tmp_name']);
        } else {
            die('{"OK": 0, "info": "Failed to open output stream."}');
        }


        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            rename("{$filePath}.part", $filePath);

            // Get mime type of the file
            $file = new \Symfony\Component\HttpFoundation\File\File($filePath);
            $mime = $file->getMimeType();

            // Save the media link
            $media->path = $fileName;
            $media->mimetype = $mime;
            $media->options = json_encode($this->retrieveId3Info($file));
            $media->save();
            
            $deleteFolder = new Image;
            // Delete old media
            $deleteFolder->deleteFiles($media_folder);

            // Create the directory
            if (!file_exists($media_folder)) {
                mkdir($media_folder, 0777, true);
            }

            \File::move($filePath, $media_folder . "/$fileName");

            // Delete tmp media
            $deleteFolder->deleteFiles($media_tmp_folder);
        }

        die('{"OK": 1, "info": "Upload successful."}');
    }

    public function getDuration($sid)
    {
        $media = Media::find($sid);
        $status = array();
        $status['data'] = '';

        if ($media == null) {
            $status['status'] = 'error';
            return $status;
        }

        $media_folder = public_path() . '/assets/medias/' . $media->category_id . '/' . $sid;
        $file = $media_folder . "/" . $media->path;

        if (file_exists($file) && $media->mimetype != 'application/pdf') {
            $object = $this->retrieveId3Info($file);
            $media->options = json_encode($object);
            $media->save();
            if (isset($object['duration'])) {
                $status['data'] = $object['duration'];
            }
        }

        $status['status'] = 'success';
        return $status;
    }

    protected function retrieveId3Info($file)
    {
        $object = array();

        if (file_exists($file)) {
            // Getting ID3 of media
            $getID3 = new GetId3();

            $ThisFileInfo = $getID3->analyze($file);

            $len = @$ThisFileInfo['playtime_string'];
            if ($len != null) {
                $object['duration'] = $len;
            }
        }

        return $object;
    }
    
    public function getImgremove($sid)
    {
        $image = Image::find($sid);

        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return redirect('/admin/medias')->withErrors($errors);
        }

        $model_id = $image->imageable_id;

        $image->delete();

        return redirect('admin/medias/edit/' . $model_id);
    }
}
