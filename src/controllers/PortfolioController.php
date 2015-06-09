<?php namespace Redooor\Redminportal;

class PortfolioController extends BaseController
{
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
        $categories = Category::where('active', true)->where('category_id', 0)->orWhere('category_id', null)->orderBy('name')->get();

        return \View::make('redminportal::portfolios/create')->with('categories', $categories);
    }

    public function getEdit($sid)
    {
        // Find the portfolio using the user id
        $portfolio = Portfolio::find($sid);

        if ($portfolio == null) {
            return \View::make('redminportal::pages/404');
        }

        $categories = Category::where('active', true)->where('category_id', 0)->orWhere('category_id', null)->orderBy('name')->get();

        if (empty($portfolio->options)) {
            $translated = null;
        } else {
            $translated = json_decode($portfolio->options);
        }

        return \View::make('redminportal::portfolios/edit')
            ->with('portfolio', $portfolio)
            ->with('translated', $translated)
            ->with('imagine', new Helper\Image())
            ->with('categories', $categories);
    }

    public function postStore()
    {
        $sid = \Input::get('id');

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

        if ($validation->passes()) {
            $name               = \Input::get('name');
            $short_description  = \Input::get('short_description');
            $long_description   = \Input::get('long_description');
            $image              = \Input::file('image');
            $active             = (\Input::get('active') == '' ? false : true);
            $category_id        = \Input::get('category_id');

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

            $portfolio = (isset($sid) ? Portfolio::find($sid) : new Portfolio);
            
            if ($portfolio == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The portfolio cannot be found because it does not exist or may have been deleted."
                );
                return \Redirect::to('/admin/portfolios')->withErrors($errors);
            }

            $portfolio->name = $name;
            $portfolio->short_description = $short_description;
            $portfolio->long_description = $long_description;
            $portfolio->active = $active;
            $portfolio->category_id = $category_id;
            $portfolio->options = json_encode($options);

            $portfolio->save();

            if (\Input::hasFile('image')) {
                // Delete all existing images for edit
                //if(isset($sid)) $portfolio->deleteAllImages();

                //Upload the file
                $helper_image = new Helper\Image();
                $filename = $helper_image->upload($image, 'portfolios/' . $portfolio->id, true);

                if ($filename) {
                    // create photo
                    $newimage = new Image;
                    $newimage->path = $filename;

                    // save photo to the loaded model
                    $portfolio->images()->save($newimage);
                }
            }
        //if it validate
        } else {
            if (isset($sid)) {
                return \Redirect::to('admin/portfolios/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return \Redirect::to('admin/portfolios/create')->withErrors($validation)->withInput();
            }
        }

        return \Redirect::to('admin/portfolios');
    }

    public function getDelete($sid)
    {
        // Find the portfolio using the user id
        $portfolio = Portfolio::find($sid);

        if ($portfolio == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The data cannot be deleted at this time.");
            return \Redirect::to('/admin/portfolios')->withErrors($errors);
        }

        // Delete all images first
        $portfolio->deleteAllImages();

        // Delete image folder if still exist
        $img_folder = \Config::get('redminportal::image.upload_path') . "/portfolios/" . $sid;
        $portfolio->deleteImageFolder($img_folder);

        // Delete the portfolio
        $portfolio->delete();

        return \Redirect::to('admin/portfolios');
    }

    public function getImgremove($sid)
    {
        $image = Image::find($sid);

        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return \Redirect::to('/admin/portfolios')->withErrors($errors);
        }

        $portfolio_id = $image->imageable_id;

        $image->remove();

        return \Redirect::to('admin/portfolios/edit/' . $portfolio_id);
    }
}
