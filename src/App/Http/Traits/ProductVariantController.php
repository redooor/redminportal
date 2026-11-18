<?php namespace Redooor\Redminportal\App\Http\Traits;

/*
 * Add Product Variant capability to controller
 */

use Exception;
use Illuminate\Support\Facades\Lang;
use Redooor\Redminportal\App\Http\Concerns\RendersFragments;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Helpers\RImage;
use Illuminate\Support\MessageBag;

trait ProductVariantController
{
    use RendersFragments;

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

        return $this->fragmentResponse('redminportal::products/create-variant', $data);
    }
    
    public function getEditVariant($product_id, $sid)
    {
        // Find the product using the user id
        $product = Product::find($sid);

        // No such id
        if ($product == null) {
            $errors = new MessageBag;
            $errors->add('errorNoSuchProduct', Lang::get('redminportal::messages.error_no_such_product'));
            return redirect($this->pageRoute)->withErrors($errors);
        }
        
        // Find the parent product
        $parent = Product::find($product_id);
        
        // No such parent id
        if ($parent == null) {
            $errors = new MessageBag;
            $errors->add('errorNoSuchProduct', Lang::get('redminportal::messages.error_no_such_product'));
            return redirect($this->pageRoute)->withErrors($errors);
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

        return $this->fragmentResponse('redminportal::products/edit-variant', $data);
    }
    
    public function getViewVariant($sid)
    {
        // Find the product using the user id
        $product = Product::find($sid);

        // No such id
        if ($product == null) {
            $errors = new MessageBag;
            $errors->add('errorNoSuchProduct', Lang::get('redminportal::messages.error_no_such_product'));
            return redirect($this->pageRoute)->withErrors($errors);
        }

        $translated = array();
        foreach ($product->translations as $translation) {
            $translated[$translation->lang] = json_decode($translation->content);
        }

        $parent = $product->variantParents->first();

        return $this->fragmentResponse('redminportal::products/view-variant', array(
            'product' => $product,
            'translated' => $translated,
            'imagine' => new RImage,
            'parent_id' => ($parent != null) ? $parent->id : null
        ));
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
        $image = $this->findOwnedImage($sid, Product::class);
        $product = Product::find($product_id);

        $is_variant = $image != null
            && $product != null
            && $product->variants->contains('id', $image->imageable_id);

        if (! $is_variant) {
            $errors = new MessageBag;
            $errors->add('errorDeleteImage', Lang::get('redminportal::messages.error_delete_image'));
            $url = ($product != null) ? $this->pageRoute . '/edit/' . $product_id : $this->pageRoute;
            return redirect($url)->withErrors($errors);
        }
        
        $model_id = $image->imageable_id;

        $image->delete();
        
        return redirect($this->pageRoute . '/edit-variant/' . $product_id . '/' . $model_id);
    }
}
