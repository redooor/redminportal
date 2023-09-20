<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Portfolio;

class PortfolioControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest, TraitImageControllerTest;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/portfolios';
        $this->viewhas = array(
            'singular' => 'portfolio',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => array(
                'name'                  => 'This is title',
                'short_description'     => 'This is body',
                'cn_name'               => 'CN name',
                'cn_short_description'  => 'CN short body',
                'category_id'           => 1,
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
            ),
            'edit' => array(
                'id'   => 1,
                'name'                  => 'This is title',
                'short_description'     => 'This is body',
                'cn_name'               => 'CN name',
                'cn_short_description'  => 'CN short body',
                'category_id'           => 1,
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
            )
        );
        
        // For testing sort
        $this->sortBy = 'created_at';

        // For testing image
        $this->img_parent_model = new Portfolio;
        $this->img_parent_create = [
            'name' => 'This is the title',
            'short_description' => 'This is the body',
            'category_id' => 1
        ];
    }
    
    /**
     * Test (Fail): access postStore with input but no name
     */
    public function testStoreCreateFailsNameBlank()
    {
        $input = array(
            'name'                  => '',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1
        );

        $this->call('POST', '/admin/portfolios/store', $input);

        $this->assertRedirectedTo('/admin/portfolios/create');
        $this->assertSessionHasErrors();
    }
}
