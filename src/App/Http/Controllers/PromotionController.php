<?php namespace Redooor\Redminportal\App\Http\Controllers;

use DateTime;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Models\Promotion;
use Redooor\Redminportal\App\Classes\ImageService;
use Redooor\Redminportal\App\Http\Traits\ImageGalleryController;
use Redooor\Redminportal\App\Models\Translation;
use Redooor\Redminportal\App\Helpers\RImage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Config;

class PromotionController extends Controller
{
    use SorterController, DeleterController, ImageGalleryController;
    
    public function __construct(Promotion $model)
    {
        $this->model = $model;
        $this->sortBy = 'end_date';
        $this->orderBy = 'desc';
        $this->perpage = config('redminportal::pagination.size');
        $this->pageView = 'redminportal::promotions.view';
        $this->pageRoute = 'admin/promotions';
        
        // For sorting
        $this->query = $this->model;
    }
    
    public function getIndex()
    {
        $models = Promotion::orderBy($this->sortBy, $this->orderBy)->paginate($this->perpage);
        
        $data = [
            'models' => $models,
            'sortBy' => $this->sortBy,
            'orderBy' => $this->orderBy
        ];
        
        return view('redminportal::promotions/view', $data);
    }
    
    public function getCreate()
    {
        return view('redminportal::promotions/create');
    }
    
    public function getEdit($sid)
    {
        // Find the promotion using the user id
        $promotion = Promotion::find($sid);
        
        if ($promotion == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The promotion cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/promotions')->withErrors($errors);
        }
        
        $translated = array();
        foreach ($promotion->translations as $translation) {
            $translated[$translation->lang] = json_decode($translation->content);
        }
        
        return view('redminportal::promotions/edit')
            ->with('promotion', $promotion)
            ->with('translated', $translated)
            ->with('start_date', new DateTime($promotion->start_date))
            ->with('end_date', new DateTime($promotion->end_date))
            ->with('imagine', new RImage);
    }
    
    public function postStore()
    {
        $sid = Request::input('id');
        
        $rules = array(
            'image'             => 'mimes:jpg,jpeg,png,gif|max:500',
            'images'            => 'array',
            'images.*'          => 'mimes:jpg,jpeg,png,gif|max:500',
            'name'              => 'required',
            'short_description' => 'required',
            'long_description'  => 'required',
            'start_date'        => 'required',
            'end_date'          => 'required',
        );
        
        $validation = Validator::make(Request::all(), $rules);
         
        if ($validation->fails()) {
            if (isset($sid)) {
                return redirect('admin/promotions/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return redirect('admin/promotions/create')->withErrors($validation)->withInput();
            }
        }

        $name               = Request::input('name');
        $short_description  = Request::input('short_description');
        $long_description   = Request::input('long_description');
        $image              = Request::file('image');
        $active             = (Request::input('active') == '' ? false : true);
        
        $start_date = DateTime::createFromFormat('d/m/Y', Request::input('start_date'));
        $end_date   = DateTime::createFromFormat('d/m/Y', Request::input('end_date'));
        
        $promotion = (isset($sid) ? Promotion::find($sid) : new Promotion);
        
        if ($promotion == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The promotion cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/promotions')->withErrors($errors);
        }
        
        $promotion->name = $name;
        $promotion->start_date = $start_date;
        $promotion->end_date = $end_date;
        $promotion->short_description = $short_description;
        $promotion->long_description = $long_description;
        $promotion->active = $active;
        
        $promotion->save();
        
        // Save translations
        $translations = Config::get('redminportal::translation');
        foreach ($translations as $translation) {
            $lang = $translation['lang'];
            if ($lang == 'en') {
                continue;
            }

            $translated_content = array(
                'name'                  => Request::input($lang . '_name'),
                'short_description'     => Request::input($lang . '_short_description'),
                'long_description'      => Request::input($lang . '_long_description')
            );

            // Check if lang exist
            $translated_model = $promotion->translations->where('lang', $lang)->first();
            if ($translated_model == null) {
                $translated_model = new Translation;
            }

            $translated_model->lang = $lang;
            $translated_model->content = json_encode($translated_content);

            $promotion->translations()->save($translated_model);
        }
        
        $imageFiles = ImageService::filesFrom();

        if (! empty($imageFiles)) {
            (new ImageService)->attach($promotion, $imageFiles, 'promotions');
        }
        
        return redirect('admin/promotions');
    }
    
    public function getImgremove($sid)
    {
        $image = $this->findOwnedImage($sid, Promotion::class);

        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return redirect('/admin/promotions')->withErrors($errors);
        }

        $model_id = $image->imageable_id;

        $image->delete();

        return redirect('admin/promotions/edit/' . $model_id);
    }

    protected function galleryModelClass(): string
    {
        return Promotion::class;
    }

    protected function galleryPath(): string
    {
        return 'admin/promotions';
    }
}
