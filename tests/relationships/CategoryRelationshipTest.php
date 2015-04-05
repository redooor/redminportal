<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Category;

class CategoryRelationshipTest extends RedminTestCase
{
    protected function createNew()
    {
        $model = new Category;
        $model->name = 'This is main category';
        $model->short_description = 'This is the body';
        $model->active = true;

        $model->save();
    }

    public function testCreateSubCategory()
    {
        $this->createNew();

        $model = Category::find(1);

        $new_model = new Category;
        $new_model->name = 'This is sub category';
        $new_model->short_description = 'This is the body 1';
        $new_model->active = true;

        $model->categories()->save($new_model);

        $new_model_2 = new Category;
        $new_model_2->name = 'This is another sub category';
        $new_model_2->short_description = 'This is the body 2';
        $new_model_2->active = true;

        $model->categories()->save($new_model_2);

        $this->assertTrue($model->categories->count() == 2);
        $this->assertTrue(Category::where('category_id', $model->id)->count() == 2);

        foreach ($model->categories as $cat) {
            if ($cat->name == 'This is sub category') {
                $this->assertTrue($cat->short_description == 'This is the body 1');
            } elseif ($cat->name == 'This is another sub category') {
                $this->assertTrue($cat->short_description == 'This is the body 2');
            } else {
                $this->assertTrue(false);
            }
        }

        // Delete main category will delete all sub categories
        $model->delete();

        $this->assertTrue(Category::where('category_id', $model->id)->count() == 0);
    }
}
