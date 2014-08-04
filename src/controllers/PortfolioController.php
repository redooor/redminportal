<?php namespace Redooor\Redminportal;

class PortfolioController extends BaseController {

    protected $model;
    
    public function __construct(Portfolio $portfolio)
    {
        $this->model = $portfolio;
    }
    
    public function getIndex()
    {
        $portfolios = Portfolio::orderBy('category_id')->orderBy('name')->paginate(20);
        
        return \View::make('redminportal::portfolios/view')->with('portfolios', $portfolios);
    }
    
    public function getCreate()
    {
        $categories = array();
        
        foreach (Category::all() as $category) {
            $categories[$category->id] = $category->name;
        }
        
        return \View::make('redminportal::portfolios/create')->with('categories', $categories);
    }
    
    public function getEdit($id)
    {
        // Find the portfolio using the user id
        $portfolio = Portfolio::find($id);
        
        if($portfolio == null) {
            return \View::make('redminportal::pages/404');
        }
        
        $categories = array();
        
        foreach (Category::all() as $category) {
            $categories[$category->id] = $category->name;
        }
        
		if(empty($portfolio->options))
        {
            $portfolio_cn = (object) array(
                'name'                  => $portfolio->name,
                'short_description'     => $portfolio->short_description,
                'long_description'      => $portfolio->long_description
            );
        }
        else
        {
            $portfolio_cn = json_decode($portfolio->options);
        }
		
        return \View::make('redminportal::portfolios/edit')
            ->with('portfolio', $portfolio)
			->with('portfolio_cn', $portfolio_cn)
            ->with('imagine', new Helper\Image())
            ->with('categories', $categories);
    }
    
    public function postStore()
    {
        $id = \Input::get('id');
        
        /*
         * Validate
         */
        $rules = array(
            'image'             => 'mimes:jpg,jpeg,png,gif|max:500',
            'name'              => 'required',
            'short_description' => 'required',
            'category_id'       => 'required',
        );
        
        $validation = \Validator::make(\Input::all(), $rules);
         
        if( $validation->passes() )
        {
            $name               = \Input::get('name');
            $short_description  = \Input::get('short_description');
            $long_description   = \Input::get('long_description');
            $image              = \Input::file('image');
            $active             = (\Input::get('active') == '' ? FALSE : TRUE);
            $category_id        = \Input::get('category_id');
            
			$cn_name               = \Input::get('cn_name');
            $cn_short_description  = \Input::get('cn_short_description');
            $cn_long_description   = \Input::get('cn_long_description');
			
			$options = array(
                'name'                  => $cn_name,
                'short_description'     => $cn_short_description,
                'long_description'      => $cn_long_description
            );
			
            $portfolio = (isset($id) ? Portfolio::find($id) : new Portfolio);
            
            if($portfolio == null) {
                return \Redirect::to('/admin/portfolios/edit/' . $id)->withErrors($validation)->withInput();
            }
            
            $portfolio->name = $name;
            $portfolio->short_description = $short_description;
            $portfolio->long_description = $long_description;
            $portfolio->active = $active;
            $portfolio->category_id = $category_id;
            $portfolio->options = json_encode($options);
			
            $portfolio->save();
            
            if(\Input::hasFile('image'))
            {
                // Delete all existing images for edit
                //if(isset($id)) $portfolio->deleteAllImages();
                
                //Upload the file
                $helper_image = new Helper\Image();
                $filename = $helper_image->upload($image, 'portfolios/' . $portfolio->id, true);
                
                if( $filename )
                {
                    // create photo
                    $newimage = new Image;
                    $newimage->path = $filename;
                    
                    // save photo to the loaded model
                    $portfolio->images()->save($newimage);
                }
            }

        }//if it validate
        else {
            if(isset($id))
            {
                return \Redirect::to('admin/portfolios/edit/' . $id)->withErrors($validation)->withInput();
            }
            else
            {
                return \Redirect::to('admin/portfolios/create')->withErrors($validation)->withInput();
            }
        }
        
        if(isset($id))
        {
            return \Redirect::to('admin/portfolios/edit/' . $id);
        }
        
        return \Redirect::to('admin/portfolios');
    }
    
    public function getDelete($id)
    {
        // Find the portfolio using the user id
        $portfolio = Portfolio::find($id);
        
        if($portfolio == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The data cannot be deleted at this time.");
            return \Redirect::to('/admin/portfolios')->withErrors($errors);
        }
        
        // Delete all images first
        $portfolio->deleteAllImages();
        
        // Delete image folder if still exist
        $img_folder = \Config::get('redminportal::image.upload_path') . "/portfolios/" . $id;
        $portfolio->deleteImageFolder($img_folder);
        
        // Delete the portfolio
        $portfolio->delete();

        return \Redirect::to('admin/portfolios');
    }
    
    public function getImgremove($id)
    {
        $image = Image::find($id);
        
        if($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return \Redirect::to('/admin/portfolios')->withErrors($errors);
        }
        
        $portfolio_id = $image->imageable_id;
        
        $image->remove();
        
        return \Redirect::to('admin/portfolios/edit/' . $portfolio_id);
    }

}
