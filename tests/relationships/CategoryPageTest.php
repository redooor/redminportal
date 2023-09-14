<?php namespace Redooor\Redminportal\Test;

use DB;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Page;

class CategoryPageTest extends BaseRelationshipTest
{
    protected $model;
    protected $testcase;
    protected $testmodel;
    protected $db_table;
    
    /**
     * Setup initial data for use in tests
     */
    public function setup(): void
    {
        parent::setup();
        
        $this->model = $this->createNewModel(new Category, array(
            'name' => 'This is main category',
            'short_description' => 'This is the body',
            'long_description' => 'This is long description',
            'order' => 1,
            'active' => true
        ));
        
        $this->db_table = 'pages';
        $this->testmodel = new Page;
        
        $this->testcase = array(
            'title' => 'This is the title',
            'slug' => 'this_is_a_slug',
            'content' => 'This is the body',
            'private' => false,
            'category_id' => $this->model->id
        );
    }
    
    public function testCreateBundle()
    {
        $check_id = $this->model->id;
        
        $this->createNewModel($this->testmodel, $this->testcase);

        $this->assertTrue(count($this->model->bundles()) == 1);
        
        $check_count = DB::table($this->db_table)->where('category_id', $check_id)->count();
        $this->assertTrue($check_count == 1);
        
        foreach ($this->model->{$this->db_table} as $item) {
            $this->assertTrueModelAllTestcases($item, $this->testcase);
        }
        
        // Delete main category will delete all sub categories
        $this->model->delete();

        $check_count = DB::table($this->db_table)->where('category_id', $check_id)->count();
        $this->assertTrue($check_count == 0);
    }
}
