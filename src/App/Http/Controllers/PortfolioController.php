<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Redooor\Redminportal\App\Http\Concerns\PreparesEditData;
use Redooor\Redminportal\App\Http\Concerns\RedirectsNotFound;
use Redooor\Redminportal\App\Http\Concerns\RemovesGalleryImages;
use Redooor\Redminportal\App\Http\Concerns\SavesTranslations;
use Redooor\Redminportal\App\Http\Concerns\ValidatesInput;
use Redooor\Redminportal\App\Http\Validation\PortfolioRules;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Models\Portfolio;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Classes\ImageService;
use Redooor\Redminportal\App\Http\Traits\ImageGalleryController;
use Redooor\Redminportal\App\Helpers\RImage;

class PortfolioController extends Controller
{
    use SorterController, DeleterController, ImageGalleryController, PreparesEditData, RedirectsNotFound, RemovesGalleryImages, SavesTranslations, ValidatesInput;
    
    public function __construct(Portfolio $model)
    {
        $this->model = $model;
        $this->sortBy = 'created_at';
        $this->orderBy = 'desc';
        $this->perpage = config('redminportal::pagination.size');
        $this->pageView = 'redminportal::portfolios.view';
        $this->pageRoute = 'admin/portfolios';
        
        // For sorting
        $this->query = $this->model
            ->LeftJoin('categories', 'portfolios.category_id', '=', 'categories.id')
            ->select('portfolios.*', 'categories.name as category_name');
    }
    
    public function getIndex()
    {
        $models = Portfolio::orderBy($this->sortBy, $this->orderBy)->paginate($this->perpage);
        
        $data = [
            'models' => $models,
            'sortBy' => $this->sortBy,
            'orderBy' => $this->orderBy
        ];

        return view('redminportal::portfolios/view', $data);
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
            return $this->redirectNotFoundWithMessage(
                'admin/portfolios',
                "The portfolio cannot be found because it does not exist or may have been deleted."
            );
        }

        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        $translated = $this->translatedContentFor($portfolio);

        return view('redminportal::portfolios/edit')
            ->with('portfolio', $portfolio)
            ->with('translated', $translated)
            ->with('imagine', new RImage)
            ->with('categories', $categories);
    }

    public function postStore()
    {
        $sid = Request::input('id');

        $rules = new PortfolioRules;
        $failure = $this->validateInputOrRedirect($rules->rules($sid), $rules->messages(), isset($sid) ? 'admin/portfolios/edit/' . $sid : 'admin/portfolios/create');
        if ($failure) {
            return $failure;
        }

        $name               = Request::input('name');
        $short_description  = Request::input('short_description');
        $long_description   = Request::input('long_description');
        $image              = Request::file('image');
        $active             = (Request::input('active') == '' ? false : true);
        $category_id        = Request::input('category_id');

        $portfolio = (isset($sid) ? Portfolio::find($sid) : new Portfolio);
        
        if ($portfolio == null) {
            return $this->redirectNotFoundWithMessage(
                '/admin/portfolios',
                "The portfolio cannot be found because it does not exist or may have been deleted."
            );
        }

        $portfolio->name = $name;
        $portfolio->short_description = $short_description;
        $portfolio->long_description = $long_description;
        $portfolio->active = $active;
        $portfolio->category_id = $category_id;

        $portfolio->save();
        
        // Save translations
        $this->saveTranslationsFromInput($portfolio, array('name', 'short_description', 'long_description'));

        $imageFiles = ImageService::filesFrom();

        if (! empty($imageFiles)) {
            (new ImageService)->attach($portfolio, $imageFiles, 'portfolios');
        }

        return redirect('admin/portfolios');
    }

    protected function galleryModelClass(): string
    {
        return Portfolio::class;
    }

    protected function galleryPath(): string
    {
        return 'admin/portfolios';
    }
}
