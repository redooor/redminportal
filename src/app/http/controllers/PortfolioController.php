<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Portfolio;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Helpers\RImage;

class PortfolioController extends Controller
{
    public function getIndex()
    {
        $portfolios = Portfolio::orderBy('category_id')
            ->orderBy('name')
            ->paginate(20);

        return view('redminportal::portfolios/view')->with('portfolios', $portfolios);
    }

    public function getCreate()
    {
        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        return view('redminportal::portfolios/create')->with('categories', $categories);
    }

    public function getEdit($sid)
    {
        // Find the portfolio using the user id
        $portfolio = Portfolio::find($sid);

        if ($portfolio == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The portfolio cannot be found because it does not exist or may have been deleted."
            );
            return redirect('admin/portfolios')->withErrors($errors);
        }

        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        if (empty($portfolio->options)) {
            $portfolio_cn = (object) array(
                'name'                  => $portfolio->name,
                'short_description'     => $portfolio->short_description,
                'long_description'      => $portfolio->long_description
            );
        } else {
            $portfolio_cn = json_decode($portfolio->options);
        }

        return view('redminportal::portfolios/edit')
            ->with('portfolio', $portfolio)
            ->with('portfolio_cn', $portfolio_cn)
            ->with('imagine', new RImage)
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

            $cn_name               = \Input::get('cn_name');
            $cn_short_description  = \Input::get('cn_short_description');
            $cn_long_description   = \Input::get('cn_long_description');

            $options = array(
                'name'                  => $cn_name,
                'short_description'     => $cn_short_description,
                'long_description'      => $cn_long_description
            );

            $portfolio = (isset($sid) ? Portfolio::find($sid) : new Portfolio);
            
            if ($portfolio == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The portfolio cannot be found because it does not exist or may have been deleted."
                );
                return redirect('/admin/portfolios')->withErrors($errors);
            }

            $portfolio->name = $name;
            $portfolio->short_description = $short_description;
            $portfolio->long_description = $long_description;
            $portfolio->active = $active;
            $portfolio->category_id = $category_id;
            $portfolio->options = json_encode($options);

            $portfolio->save();

            if (\Input::hasFile('image')) {
                //Upload the file
                $helper_image = new RImage;
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
                return redirect('admin/portfolios/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return redirect('admin/portfolios/create')->withErrors($validation)->withInput();
            }
        }

        return redirect('admin/portfolios');
    }

    public function getDelete($sid)
    {
        // Find the portfolio using the user id
        $portfolio = Portfolio::find($sid);

        if ($portfolio == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The data cannot be deleted at this time.");
            return redirect('/admin/portfolios')->withErrors($errors);
        }
        
        // Delete the portfolio
        $portfolio->delete();

        return redirect('admin/portfolios');
    }

    public function getImgremove($sid)
    {
        $image = Image::find($sid);

        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return redirect('/admin/portfolios')->withErrors($errors);
        }

        $portfolio_id = $image->imageable_id;

        $image->delete();

        return redirect('admin/portfolios/edit/' . $portfolio_id);
    }
}
