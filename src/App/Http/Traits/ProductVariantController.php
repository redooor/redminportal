<?php namespace Redooor\Redminportal\App\Http\Traits;

/*
 * Add Product Variant capability to controller
 */

use Lang;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Helpers\RImage;
use Illuminate\Support\MessageBag;

trait ProductVariantController
{
    /* -- Requires --
    protected $model;
    protected $pageRoute;
    protected $pageView;
    ----------------- */
    
    public function getCreateVariant($product_id)
    {
        $product = Product::find($product_id);
        // No such id
        if ($product == null) {
            $errors = new MessageBag;
            $errors->add('errorNoSuchProduct', Lang::get('redminportal::messages.error_no_such_product'));
            return redirect($this->pageRoute)->withErrors($errors);
        }
        
        $data = array(
            'product' => $product,
            'product_id' => $product_id,
            'weight_units' => $this->weight_units,
            'volume_units' => $this->volume_units
        );

        return view('redminportal::products/create-variant', $data);
    }
    
    public function getEditVariant($product_id, $sid)
    {
        // Find the product using the user id
        $product = Product::find($sid);

        // No such id
        if ($product == null) {
            $errors = new MessageBag;
            $errors->add('errorNoSuchProduct', Lang::get('redminportal::messages.error_no_such_product'));
            return view('redminportal::products/edit-variant')->withErrors($errors);
        }
        
        // Find the parent product
        $parent = Product::find($product_id);
        
        // No such parent id
        if ($parent == null) {
            $errors = new MessageBag;
            $errors->add('errorNoSuchProduct', Lang::get('redminportal::messages.error_no_such_product'));
            return view('redminportal::products/edit-variant')->withErrors($errors);
        }

        $tagString = "";
        foreach ($product->tags as $tag) {
            if (! empty($tagString)) {
                $tagString .= ",";
            }

            $tagString .= $tag->name;
        }

        $translated = array();
        foreach ($product->translations as $translation) {
            $translated[$translation->lang] = json_decode($translation->content);
        }
        
        $data = array(
            'product_id' => $product_id,
            'parent' => $parent,
            'product' => $product,
            'translated' => $translated,
            'tagString'=> $tagString,
            'imagine' => new RImage,
            'weight_units' => $this->weight_units,
            'volume_units' => $this->volume_units
        );

        return view('redminportal::products/edit-variant', $data);
    }
    
    public function getViewVariant($sid)
    {
        // Find the product using the user id
        $product = Product::find($sid);

        // No such id
        if ($product == null) {
            $errors = new MessageBag;
            $errors->add('errorNoSuchProduct', Lang::get('redminportal::messages.error_no_such_product'));
            return view('redminportal::products/view-variant')->withErrors($errors);
        }

        $translated = array();
        foreach ($product->translations as $translation) {
            $translated[$translation->lang] = json_decode($translation->content);
        }

        return view('redminportal::products/view-variant')
            ->with('product', $product)
            ->with('translated', $translated)
            ->with('imagine', new RImage);
    }
    
    /*
     * Return a table of variants belonging to the given product id
     * @param integer product id
     * @return view
     */
    public function getListVariants($sid)
    {
        $product = Product::find($sid);
        
        // No such id
        if ($product == null) {
            $errors = new MessageBag;
            $errors->add('errorNoSuchProduct', Lang::get('redminportal::messages.error_no_such_product'));
            return redirect($this->pageRoute)->withErrors($errors);
        }
        
        $data = array(
            'variantParent' => $product,
            'variants' => $product->variants,
            'imagine' => new RImage
        );
        return view('redminportal::products/list-variants', $data);
    }
    
    /*
     * Delete a variant
     * @param integer id
     * @return json (bool status, string message)
     */
    public function getDeleteVariantJson($sid)
    {
        $status = false;
        $message = Lang::get('redminportal::messages.error_delete_entry');
        
        $product = Product::find($sid);
        
        // No such id
        if ($product == null) {
            return json_encode(array('status' => $status, 'message' => $message));
        }
        
        // Check if there's any order related to this product
        if (count($product->orders) > 0) {
            $message = Lang::get('redminportal::messages.error_delete_product_already_ordered');
            return json_encode(array('status' => $status, 'message' => $message));
        }
        
        // Delete the product
        $result = $product->delete();
        
        if ($result) {
            $status = true;
            $message = Lang::get('redminportal::messages.success_delete_record');
        }

        return json_encode(array('status' => $status, 'message' => $message));
    }
    
    public function getVariantImgremove($product_id, $sid)
    {
        $image = Image::find($sid);

        if ($image == null) {
            $errors = new MessageBag;
            $errors->add('errorDeleteImage', Lang::get('redminportal::messages.error_delete_image'));
            return redirect($this->pageRoute . '/edit-variant/'. $product_id . '/' . $model_id)->withErrors($errors);
        }
        
        $model_id = $image->imageable_id;

        $image->delete();
        
        return redirect($this->pageRoute . '/edit-variant/' . $product_id . '/' . $model_id);
    }
}
