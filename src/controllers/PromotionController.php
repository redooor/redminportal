<?php namespace Redooor\Redminportal;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Cartalyst\Sentry\Facades\Laravel\Sentry;
use DateTime;

class PromotionController extends BaseController {

    protected $model;
    
    public function __construct(Promotion $promotion)
    {
        $this->model = $promotion;
    }
    
    public function getIndex()
    {
        $promotions = Promotion::paginate(20);
        
        return View::make('redminportal::promotions/view')->with('promotions', $promotions);
    }
    
    public function getCreate()
    {
        return View::make('redminportal::promotions/create');
    }
    
    public function getEdit($id)
    {
        // Find the promotion using the user id
        $promotion = Promotion::find($id);
        
		if(empty($promotion->options))
        {
            $promotion_cn = (object) array(
                'name'                  => $promotion->name,
                'short_description'     => $promotion->short_description,
                'long_description'      => $promotion->long_description
            );
        }
        else
        {
            $promotion_cn = json_decode($promotion->options);
        }
		
        return View::make('redminportal::promotions/edit')
            ->with('promotion', $promotion)
			->with('promotion_cn', $promotion_cn)
            ->with('imageUrl', 'assets/img/promotions/')
            ->with('start_date', new DateTime($promotion->start_date))
            ->with('end_date', new DateTime($promotion->end_date));
    }
    
    public function postStore()
    {
        $id = Input::get('id');
        
        /*
         * Validate
         */
        $rules = array(
            'image'             => 'mimes:jpg,jpeg,png,gif|max:500',
            'name'              => 'required',
            'short_description' => 'required',
            'long_description'  => 'required',
            'start_date'        => 'required',
            'end_date'          => 'required',
        );
        
        $validation = Validator::make(Input::all(), $rules);
         
        if( $validation->passes() )
        {
            $name               = Input::get('name');
            $short_description  = Input::get('short_description');
            $long_description   = Input::get('long_description');
            $image              = Input::file('image');
            $active             = (Input::get('active') == '' ? FALSE : TRUE);
            
			$cn_name               = Input::get('cn_name');
            $cn_short_description  = Input::get('cn_short_description');
            $cn_long_description   = Input::get('cn_long_description');
			
			$options = array(
                'name'                  => $cn_name,
                'short_description'     => $cn_short_description,
                'long_description'      => $cn_long_description
            );
			
            $start_date = DateTime::createFromFormat('d/m/Y', Input::get('start_date'));
            $end_date   = DateTime::createFromFormat('d/m/Y', Input::get('end_date'));
            
            $promotion = (isset($id) ? Promotion::find($id) : new Promotion);
            $promotion->name = $name;
            $promotion->start_date = $start_date;
            $promotion->end_date = $end_date;
            $promotion->short_description = $short_description;
            $promotion->long_description = $long_description;
            $promotion->active = $active;
            $promotion->options = json_encode($options);
			
            $promotion->save();
            
            if(Input::hasFile('image'))
            {
                // Delete all existing images for edit
                if(isset($id)) $promotion->deleteAllImages();
                
                //set the name of the file
                $originalFilename = $image->getClientOriginalName();
                $filename = 'promo' . date("dmY") . '_' . Str::random(20) .'.'. File::extension($originalFilename);
                
                //Upload the file
                $isSuccess = $image->move('assets/img/promotions', $filename);
                
                if( $isSuccess )
                {
                    // create photo
                    $newimage = new Image;
                    $newimage->path = $filename;
                    
                    // save photo to the loaded model
                    $promotion->images()->save($newimage);
                }
            }

        }//if it validate
        else {
            if(isset($id))
            {
                return Redirect::to('admin/promotions/edit/' . $id)->withErrors($validation)->withInput();
            }
            else
            {
                return Redirect::to('admin/promotions/create')->withErrors($validation)->withInput();
            }
        }
        
        return Redirect::to('admin/promotions');
    }
    
    public function getDelete($id)
    {
        // Find the promotion using the id
        $promotion = Promotion::find($id);
        
        // Delete all images
        $promotion->deleteAllImages();
        
        // Delete the promotion
        $promotion->delete();

        return Redirect::to('admin/promotions');
    }

}
