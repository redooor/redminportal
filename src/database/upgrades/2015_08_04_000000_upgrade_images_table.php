<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Redooor\Redminportal\App\Helpers\RHelper;
use Redooor\Redminportal\App\Helpers\RImage;
use Illuminate\Support\Facades\File;

class UpgradeImagesTable extends Migration
{
    /**
     * Run the migrations.
     * Upgrade images table from Redminportal 0.1 to 0.2/0.3
     *
     * @return void
     */
    public function up()
    {
        // Check if the images table exists
        if (Schema::hasTable('images')) {
            if (Schema::hasColumn('images', 'imageable_type')) {
                $images = DB::select('select * from images');
                foreach ($images as $image) {
                    // Get the model name
                    $new_type_array = explode('\\', $image->imageable_type);
                    $last_model = array_pop($new_type_array);
                    
                    // Move image to new folder
                    $image_folder = Config::get('redminportal::image.upload_dir');
                    
                    switch ($last_model) {
                        case 'Category':
                            $move_file = $this->moveFileNewToOld($image_folder, $image, 'categories');
                            break;
                        case 'Media':
                            $move_file = $this->moveFileNewToOld($image_folder, $image, 'medias');
                            break;
                        case 'Module':
                            $move_file = $this->moveFileNewToOld($image_folder, $image, 'modules');
                            break;
                        case 'Promotion':
                            $move_file = $this->moveFileNewToOld($image_folder, $image, 'promotions');
                            break;
                        default:
                            $move_file = false;
                            break;
                    }
                    
                    // Create dimensions
                    if ($move_file) {
                        $img_helper = new RImage;
                        $img_helper->createDimensions($move_file);
                    }
                    
                    // Points to new namespace Redooor\\Redminportal\\App\\Models\\
                    $new_type = 'Redooor\\Redminportal\\App\\Models\\' . $last_model;
                    DB::table('images')
                        ->where('id', $image->id)
                        ->update([
                            'imageable_type' => $new_type,
                            'path' => ($move_file ? $move_file : $image->path)
                        ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Check if the images table exists
        if (Schema::hasTable('images')) {
            if (Schema::hasColumn('images', 'imageable_type')) {
                $images = DB::select('select * from images');
                foreach ($images as $image) {
                    // Get the model name
                    $old_type_array = explode('\\', $image->imageable_type);
                    $last_model = array_pop($old_type_array);
                    
                    // Move image back to old folder
                    $image_folder = Config::get('redminportal::image.upload_dir');
                    $filename_array = explode('/', $image->path);
                    $filename = array_pop($filename_array);
                    
                    switch ($last_model) {
                        case 'Category':
                            $move_file = $this->moveFileOldToNew($image_folder, $image, 'categories');
                            break;
                        case 'Media':
                            $move_file = $this->moveFileOldToNew($image_folder, $image, 'medias');
                            break;
                        case 'Module':
                            $move_file = $this->moveFileOldToNew($image_folder, $image, 'modules');
                            break;
                        case 'Promotion':
                            $move_file = $this->moveFileOldToNew($image_folder, $image, 'promotions');
                            break;
                        default:
                            $move_file = false;
                            break;
                    }
                    
                    // Remove folder if move succeed
                    if ($move_file) {
                        $this->recursiveRemoveDirectory(public_path() . '/' . $move_file);
                    }
                    
                    // Points to old namespace Redooor\\Redminportal\\
                    $old_type = 'Redooor\\Redminportal\\' . $last_model;
                    DB::table('images')
                        ->where('id', $image->id)
                        ->update([
                            'imageable_type' => $old_type,
                            'path' => ($move_file ? $filename : $image->path)
                        ]);
                }
            }
        }
    }
    
    private function moveFileNewToOld($image_folder, $image, $model_type)
    {
        $move_file = false;
        
        $filename_array = explode('/', $image->path);
        $filename = array_pop($filename_array);
        
        $old_img_dir = RHelper::joinPaths($image_folder, $model_type, $filename);
        $new_img_dir = RHelper::joinPaths($image_folder, $model_type, $image->imageable_id, $filename);
        $new_img_folder = RHelper::joinPaths(public_path(), $image_folder, $model_type, $image->imageable_id);

        if (! is_dir($new_img_folder)) {
            mkdir($new_img_folder, 0777, true);
        }

        if (file_exists(public_path() . '/' . $old_img_dir)) {
            $move_file = rename(public_path() . '/' . $old_img_dir, public_path() . '/' . $new_img_dir);
        }
        
        if ($move_file) {
            return $new_img_dir;
        } else {
            return false;
        }
    }
    
    private function moveFileOldToNew($image_folder, $image, $model_type)
    {
        $move_file = false;
        
        $filename_array = explode('/', $image->path);
        $filename = array_pop($filename_array);
        
        $old_img_dir = RHelper::joinPaths($image_folder, $model_type, $filename);
        $new_img_dir = RHelper::joinPaths($image_folder, $model_type, $image->imageable_id, $filename);
        $new_img_folder = RHelper::joinPaths($image_folder, $model_type, $image->imageable_id);

        if (file_exists(public_path() . '/' . $new_img_dir)) {
            $move_file = rename(public_path() . '/' . $new_img_dir, public_path() . '/' . $old_img_dir);
        }
        
        if ($move_file) {
            return $new_img_folder;
        } else {
            return false;
        }
    }
    
    private function recursiveRemoveDirectory($target)
    {
        if (is_dir($target)) {
            $files = glob($target . '*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned

            foreach ($files as $file) {
                $this->recursiveRemoveDirectory($file);
            }

            if (is_dir($target)) {
                rmdir($target);
            }

        } elseif (is_file($target)) {
            unlink($target);
        }
    }
}
