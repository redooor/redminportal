<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Promotion;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Helpers\RImage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use DateTime;

class PromotionController extends Controller
{
    public function getIndex()
    {
        $promotions = Promotion::paginate(20);
        
        return view('redminportal::promotions/view')->with('promotions', $promotions);
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
        
        if (empty($promotion->options)) {
            $translated = null;
        } else {
            $translated = json_decode($promotion->options);
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
            
            $options = array();
            $translations       = \Config::get('redminportal::translation');
            foreach ($translations as $translation) {
                $lang = $translation['lang'];
                if ($lang == 'en') {
                    continue;
                }
                $options[$lang] = array(
                    'name'                  => \Input::get($lang . '_name'),
                    'short_description'     => \Input::get($lang . '_short_description'),
                    'long_description'      => \Input::get($lang . '_long_description')
                );
            }
            
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
            $promotion->options = json_encode($options);
            
            $promotion->save();
            
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
    
    public function getDelete($sid)
    {
        // Find the promotion using the id
        $promotion = Promotion::find($sid);
        
        if ($promotion == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "We are having problem deleting this entry. Please try again.");
            return redirect('admin/promotions')->withErrors($errors);
        }
        
        // Delete the promotion
        $promotion->delete();

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
