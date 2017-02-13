<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Models\Promotion;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Translation;
use Redooor\Redminportal\App\Helpers\RImage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use DateTime;

class PromotionController extends Controller
{
    use SorterController, DeleterController;
    
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
        $sid = Input::get('id');
        
        $rules = array(
            'image'             => 'mimes:jpg,jpeg,png,gif|max:500',
            'name'              => 'required',
            'short_description' => 'required',
            'long_description'  => 'required',
            'start_date'        => 'required',
            'end_date'          => 'required',
        );
        
        $validation = Validator::make(Input::all(), $rules);
         
        if ($validation->passes()) {
            $name               = Input::get('name');
            $short_description  = Input::get('short_description');
            $long_description   = Input::get('long_description');
            $image              = Input::file('image');
            $active             = (Input::get('active') == '' ? false : true);
            
            $start_date = DateTime::createFromFormat('d/m/Y', Input::get('start_date'));
            $end_date   = DateTime::createFromFormat('d/m/Y', Input::get('end_date'));
            
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
            $translations = \Config::get('redminportal::translation');
            foreach ($translations as $translation) {
                $lang = $translation['lang'];
                if ($lang == 'en') {
                    continue;
                }

                $translated_content = array(
                    'name'                  => Input::get($lang . '_name'),
                    'short_description'     => Input::get($lang . '_short_description'),
                    'long_description'      => Input::get($lang . '_long_description')
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
            
            if (Input::hasFile('image')) {
                //Upload the file
                $helper_image = new RImage;
                $filename = $helper_image->upload($image, 'promotions/' . $promotion->id, true);

                if ($filename) {
                    // create photo
                    $newimage = new Image;
                    $newimage->path = $filename;

                    // save photo to the loaded model
                    $promotion->images()->save($newimage);
                }
            }
        //if it validate
        } else {
            if (isset($sid)) {
                return redirect('admin/promotions/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return redirect('admin/promotions/create')->withErrors($validation)->withInput();
            }
        }
        
        return redirect('admin/promotions');
    }
    
    public function getImgremove($sid)
    {
        $image = Image::find($sid);

        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return redirect('/admin/promotions')->withErrors($errors);
        }

        $model_id = $image->imageable_id;

        $image->delete();

        return redirect('admin/promotions/edit/' . $model_id);
    }
}
