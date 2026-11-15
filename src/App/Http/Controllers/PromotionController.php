<?php namespace Redooor\Redminportal\App\Http\Controllers;

use DateTime;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Models\Promotion;
use Redooor\Redminportal\App\Classes\ImageService;
use Redooor\Redminportal\App\Http\Traits\ImageGalleryController;
use Redooor\Redminportal\App\Http\Concerns\PreparesEditData;
use Redooor\Redminportal\App\Http\Concerns\RedirectsNotFound;
use Redooor\Redminportal\App\Http\Concerns\RemovesGalleryImages;
use Redooor\Redminportal\App\Http\Concerns\SavesTranslations;
use Redooor\Redminportal\App\Http\Concerns\ValidatesInput;
use Redooor\Redminportal\App\Http\Validation\PromotionRules;
use Redooor\Redminportal\App\Helpers\RImage;
use Illuminate\Support\Facades\Request;

class PromotionController extends Controller
{
    use SorterController, DeleterController, ImageGalleryController, PreparesEditData, RedirectsNotFound, RemovesGalleryImages, SavesTranslations, ValidatesInput;
    
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
            return $this->redirectNotFoundWithMessage(
                '/admin/promotions',
                "The promotion cannot be found because it does not exist or may have been deleted."
            );
        }
        
        $translated = $this->translatedContentFor($promotion);
        
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
        
        $rules = new PromotionRules;
        $failure = $this->validateInputOrRedirect($rules->rules($sid), $rules->messages(), isset($sid) ? 'admin/promotions/edit/' . $sid : 'admin/promotions/create');
        if ($failure) {
            return $failure;
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
            return $this->redirectNotFoundWithMessage(
                '/admin/promotions',
                "The promotion cannot be found because it does not exist or may have been deleted."
            );
        }
        
        $promotion->name = $name;
        $promotion->start_date = $start_date;
        $promotion->end_date = $end_date;
        $promotion->short_description = $short_description;
        $promotion->long_description = $long_description;
        $promotion->active = $active;
        
        $promotion->save();
        
        // Save translations
        $this->saveTranslationsFromInput($promotion, array('name', 'short_description', 'long_description'));
        
        $imageFiles = ImageService::filesFrom();

        if (! empty($imageFiles)) {
            (new ImageService)->attach($promotion, $imageFiles, 'promotions');
        }
        
        return redirect('admin/promotions');
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
