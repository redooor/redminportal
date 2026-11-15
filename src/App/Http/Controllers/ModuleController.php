<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Redooor\Redminportal\App\Http\Concerns\PreparesEditData;
use Redooor\Redminportal\App\Http\Concerns\RedirectsNotFound;
use Redooor\Redminportal\App\Http\Concerns\RemovesGalleryImages;
use Redooor\Redminportal\App\Http\Concerns\SavesTranslations;
use Redooor\Redminportal\App\Http\Concerns\SyncsTags;
use Redooor\Redminportal\App\Http\Concerns\ValidatesInput;
use Redooor\Redminportal\App\Http\Validation\ModuleRules;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Media;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\ModuleMediaMembership;
use Redooor\Redminportal\App\Classes\ImageService;
use Redooor\Redminportal\App\Http\Traits\ImageGalleryController;
use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Helpers\RImage;

class ModuleController extends Controller
{
    use SorterController, ImageGalleryController, PreparesEditData, RedirectsNotFound, RemovesGalleryImages, SavesTranslations, SyncsTags, ValidatesInput;
    
    public function __construct(Module $model)
    {
        $this->model = $model;
        $this->sortBy = 'name';
        $this->orderBy = 'asc';
        $this->perpage = config('redminportal::pagination.size');
        $this->pageView = 'redminportal::modules.view';
        $this->pageRoute = 'admin/modules';
        
        // For sorting
        $this->query = $this->model
            ->LeftJoin('categories', 'modules.category_id', '=', 'categories.id')
            ->select('modules.*', 'categories.name as category_name');
    }
    
    public function getIndex()
    {
        $models = Module::orderBy($this->sortBy, $this->orderBy)->paginate($this->perpage);
        
        $data = [
            'models' => $models,
            'sortBy' => $this->sortBy,
            'orderBy' => $this->orderBy
        ];

        return view('redminportal::modules/view', $data);
    }

    public function getMedias($sid)
    {
        $medias = Media::where('category_id', $sid)->orderBy('name')->get();
        $memberships = Membership::orderBy('rank')->get();

        return view('redminportal::modules/medias')
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

        return view('redminportal::modules/medias')
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

        return view('redminportal::modules/create')
            ->with('categories', $categories)
            ->with('memberships', Membership::orderBy('rank')->get());
    }

    public function getEdit($sid)
    {
        // Find the module using the user id
        $module = Module::find($sid);

        // No such id
        if ($module == null) {
            return $this->redirectNotFoundWithMessage(
                '/admin/modules',
                "The module cannot be found because it does not exist or may have been deleted."
            );
        }

        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        $tagString = $this->tagStringFor($module);
        
        $translated = $this->translatedContentFor($module);

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

        return view('redminportal::modules/edit')
            ->with('module', $module)
            ->with('translated', $translated)
            ->with('categories', $categories)
            ->with('tagString', $tagString)
            ->with('pricelists', $pricelists)
            ->with('imagine', new RImage);
    }

    public function postStore()
    {
        $sid = Request::input('id');
        
        $rules = new ModuleRules;
        $failure = $this->validateInputOrRedirect($rules->rules($sid), $rules->messages(), isset($sid) ? 'admin/modules/edit/' . $sid : 'admin/modules/create');
        if ($failure) {
            return $failure;
        }
        
        $name               = Request::input('name');
        $sku                = Request::input('sku');
        $short_description  = Request::input('short_description');
        $long_description   = Request::input('long_description');
        $image              = Request::file('image');
        $featured           = (Request::input('featured') == '' ? false : true);
        $active             = (Request::input('active') == '' ? false : true);
        $category_id        = Request::input('category_id');
        $tags               = Request::input('tags');

        $module = (isset($sid) ? Module::find($sid) : new Module);
        
        if ($module == null) {
            return $this->redirectNotFoundWithMessage(
                '/admin/modules',
                "The module cannot be found because it does not exist or may have been deleted."
            );
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
        $this->saveTranslationsFromInput($module, array('name', 'short_description', 'long_description'));

        // Save pricelist
        foreach (Membership::all() as $membership) {
            $pricelist = Pricelist::where('module_id', $module->id)
                ->where('membership_id', $membership->id)->first();

            if ($pricelist == null) {
                $pricelist = new Pricelist;
                $pricelist->module_id = $module->id;
                $pricelist->membership_id = $membership->id;
            }
            
            $price_active = (Request::input('price_active_' . $membership->id) == '' ? false : true);
            $price = Request::input('price_' . $membership->id);

            if (! empty($price)) {
                $pricelist->active = $price_active;
                $pricelist->price = $price;
                $pricelist->save();
            } else {
                // Empty price means to delete, but check that it is not used
                $pricelist_used = DB::table('order_pricelist')->where('pricelist_id', $pricelist->id)->get();
                if (count($pricelist_used) == 0) {
                    $pricelist->delete();
                }
            }
        }

        // Save medias
        $media_checkbox = Request::input('media_checkbox');

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

        $this->syncTagsFromInput($module, $tags);

        $imageFiles = ImageService::filesFrom();

        if (! empty($imageFiles)) {
            (new ImageService)->attach($module, $imageFiles, 'modules');
        }

        return redirect('admin/modules');
    }

    public function getDelete($sid)
    {
        // Find the module using the user id
        $module = Module::find($sid);

        if ($module == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "We are having problem deleting this entry. Please try again.");
            return redirect()->back()->withErrors($errors);
        }

        $purchases = Order::join('order_pricelist', 'orders.id', '=', 'order_pricelist.id')
            ->join('pricelists', 'pricelists.id', '=', 'order_pricelist.pricelist_id')
            ->where('pricelists.module_id', $sid)
            ->get();

        if (count($purchases) > 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'deleteError',
                "This module has been purchased before. You cannot delete it. Please disable it instead."
            );
            return redirect()->back()->withErrors($errors);
        }
        
        // Delete the module
        $module->delete();

        return redirect()->back();
    }
    
    protected function galleryModelClass(): string
    {
        return Module::class;
    }

    protected function galleryPath(): string
    {
        return 'admin/modules';
    }
}
