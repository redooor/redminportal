<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Media;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\ModuleMediaMembership;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Translation;
use Redooor\Redminportal\App\Models\Tag;
use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\UserPricelist;
use Redooor\Redminportal\App\Helpers\RImage;

class ModuleController extends Controller
{
    public function getIndex()
    {
        $modules = Module::orderBy('category_id')->orderBy('name')->paginate(20);

        return \View::make('redminportal::modules/view')->with('modules', $modules);
    }

    public function getMedias($sid)
    {
        $medias = Media::where('category_id', $sid)->orderBy('name')->get();
        $memberships = Membership::orderBy('rank')->get();

        return \View::make('redminportal::modules/medias')
            ->with('medias', $medias)
            ->with('memberships', $memberships);
    }

    public function getEditmedias($sid, $module_id)
    {
        $medias = Media::where('category_id', $sid)->orderBy('name')->get();
        $memberships = Membership::orderBy('rank')->get();

        $modMediaMembership = array();
        foreach (ModuleMediaMembership::where('module_id', $module_id)->get() as $mmm) {
            $modMediaMembership[$mmm->media_id][$mmm->membership_id] = true;
        }

        return \View::make('redminportal::modules/medias')
            ->with('medias', $medias)
            ->with('memberships', $memberships)
            ->with('modMediaMembership', $modMediaMembership);
    }

    public function getCreate()
    {
        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        return \View::make('redminportal::modules/create')
            ->with('categories', $categories)
            ->with('memberships', Membership::orderBy('rank')->get());
    }

    public function getEdit($sid)
    {
        // Find the module using the user id
        $module = Module::find($sid);

        // No such id
        if ($module == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The module cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/modules')->withErrors($errors);
        }

        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        $tagString = "";
        foreach ($module->tags as $tag) {
            if (! empty($tagString)) {
                $tagString .= ",";
            }

            $tagString .= $tag->name;
        }
        
        $translated = array();
        foreach ($module->translations as $translation) {
            $translated[$translation->lang] = json_decode($translation->content);
        }

        $pricelists = array();
        foreach (Membership::orderBy('rank')->get() as $membership) {
            $pricelist = Pricelist::where('module_id', $module->id)
                ->where('membership_id', $membership->id)
                ->first();
            if ($pricelist == null) {
                $pricelists[] = array(
                    'id'      => $membership->id,
                    'name'    => $membership->name,
                    'price'   => '',
                    'active'  => false
                );
            } else {
                $pricelists[] = array(
                    'id'      => $membership->id,
                    'name'    => $membership->name,
                    'price'   => $pricelist->price,
                    'active'  => $pricelist->active
                );
            }
        }

        return \View::make('redminportal::modules/edit')
            ->with('module', $module)
            ->with('translated', $translated)
            ->with('categories', $categories)
            ->with('tagString', $tagString)
            ->with('pricelists', $pricelists)
            ->with('imagine', new RImage);
    }

    public function postStore()
    {
        $sid = \Input::get('id');
        
        $rules = array(
            'image'             => 'mimes:jpg,jpeg,png,gif|max:500',
            'name'              => 'required|unique:modules,name' . (isset($sid) ? ',' . $sid : ''),
            'short_description' => 'required',
            'sku'               => 'required|alpha_dash|unique:modules,sku' . (isset($sid) ? ',' . $sid : ''),
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

            $module = (isset($sid) ? Module::find($sid) : new Module);
            
            if ($module == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The module cannot be found because it does not exist or may have been deleted."
                );
                return \Redirect::to('/admin/modules')->withErrors($errors);
            }
            
            $module->name = $name;
            $module->sku = $sku;
            $module->short_description = $short_description;
            $module->long_description = $long_description;
            $module->featured = $featured;
            $module->active = $active;
            $module->category_id = $category_id;

            // Create or save changes
            $module->save();
            
            // Save translations
            $translations = \Config::get('redminportal::translation');
            foreach ($translations as $translation) {
                $lang = $translation['lang'];
                if ($lang == 'en') {
                    continue;
                }

                $translated_content = array(
                    'name'                  => \Input::get($lang . '_name'),
                    'short_description'     => \Input::get($lang . '_short_description'),
                    'long_description'      => \Input::get($lang . '_long_description')
                );

                // Check if lang exist
                $translated_model = $module->translations->where('lang', $lang)->first();
                if ($translated_model == null) {
                    $translated_model = new Translation;
                }

                $translated_model->lang = $lang;
                $translated_model->content = json_encode($translated_content);

                $module->translations()->save($translated_model);
            }

            // Save pricelist
            foreach (Membership::all() as $membership) {
                $pricelist = Pricelist::where('module_id', $module->id)
                    ->where('membership_id', $membership->id)->first();

                if ($pricelist == null) {
                    $pricelist = new Pricelist;
                    $pricelist->module_id = $module->id;
                    $pricelist->membership_id = $membership->id;
                }
                
                $price_active = (\Input::get('price_active_' . $membership->id) == '' ? false : true);
                $price = \Input::get('price_' . $membership->id);

                if (! empty($price)) {
                    $pricelist->active = $price_active;
                    $pricelist->price = $price;
                    $pricelist->save();
                } else {
                    // Empty price means to delete, but check that it is not used
                    $pricelist_used = UserPricelist::where('pricelist_id', $pricelist->id)->get();
                    if (count($pricelist_used) == 0) {
                        $pricelist->delete();
                    }
                }
            }

            // Save medias
            $media_checkbox = \Input::get('media_checkbox');

            if (isset($sid)) {
                // Remove all existing medias
                $existing_medias = ModuleMediaMembership::where('module_id', $module->id)->get();
                foreach ($existing_medias as $remove_media) {
                    $remove_media->delete();
                }
            }

            if (is_array($media_checkbox)) {
                foreach ($media_checkbox as $check) {
                    $media_pair = explode('_', $check);
                    $media_id = $media_pair[0];
                    $membership_id = $media_pair[1];

                    $modMediaMembership = new ModuleMediaMembership;
                    $modMediaMembership->module_id = $module->id;
                    $modMediaMembership->membership_id = $membership_id;
                    $modMediaMembership->media_id = $media_id;
                    $modMediaMembership->save();
                }
            }

            if (! empty($tags)) {
                // Delete old tags
                $module->tags()->detach();

                // Save tags
                foreach (explode(',', $tags) as $tagName) {
                    Tag::addTag($module, $tagName);
                }
            }

            if (\Input::hasFile('image')) {
                //Upload the file
                $helper_image = new RImage;
                $filename = $helper_image->upload($image, 'modules/' . $module->id, true);

                if ($filename) {
                    // create photo
                    $newimage = new Image;
                    $newimage->path = $filename;

                    // save photo to the loaded model
                    $module->images()->save($newimage);
                }
            }
        //if it validate
        } else {
            if (isset($sid)) {
                return \Redirect::to('admin/modules/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return \Redirect::to('admin/modules/create')->withErrors($validation)->withInput();
            }
        }

        return \Redirect::to('admin/modules');
    }

    public function getDelete($sid)
    {
        // Find the module using the user id
        $module = Module::find($sid);

        if ($module == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "We are having problem deleting this entry. Please try again.");
            return \Redirect::to('admin/modules')->withErrors($errors);
        }

        $purchases = UserPricelist::join('order_pricelist', 'orders.id', '=', 'order_pricelist.id')
            ->join('pricelists', 'pricelists.id', '=', 'order_pricelist.pricelist_id')
            ->where('pricelists.module_id', $sid)
            ->get();

        if (count($purchases) > 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'deleteError',
                "This module has been purchased before. You cannot delete it. Please disable it instead."
            );
            return \Redirect::to('admin/modules')->withErrors($errors);
        }
        
        // Delete the module
        $module->delete();

        return \Redirect::to('admin/modules');
    }
    
    public function getImgremove($sid)
    {
        $image = Image::find($sid);

        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return redirect('/admin/modules')->withErrors($errors);
        }

        $model_id = $image->imageable_id;

        $image->delete();

        return redirect('admin/modules/edit/' . $model_id);
    }
}
