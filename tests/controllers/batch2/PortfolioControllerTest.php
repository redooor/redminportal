<?php namespace Redooor\Redminportal\Test;

class PortfolioControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest;
    
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/portfolios';
        $viewhas = array(
            'singular' => 'portfolio',
            'plural' => 'models'
        );
        $input = array(
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
        
        parent::__construct($page, $viewhas, $input);
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        parent::__destruct();
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
