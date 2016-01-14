<?php namespace Redooor\Redminportal\Test;

class PromotionControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest;
    
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/promotions';
        $viewhas = array(
            'singular' => 'promotion',
            'plural' => 'models'
        );
        $input = array(
            'create' => array(
                'name'                  => 'This is title',
                'short_description'     => 'This is body',
                'long_description'      => 'This is long body',
                'cn_name'               => 'CN name',
                'cn_short_description'  => 'CN short body',
                'cn_long_description'   => 'CN long body',
                'start_date'            => '29/02/2016',
                'end_date'              => '29/02/2016',
                'active'                => true,
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
            ),
            'edit' => array(
                'id'   => 1,
                'name'                  => 'This is title',
                'short_description'     => 'This is body',
                'long_description'      => 'This is long body',
                'cn_name'               => 'CN name',
                'cn_short_description'  => 'CN short body',
                'cn_long_description'   => 'CN long body',
                'start_date'            => '29/02/2016',
                'end_date'              => '29/02/2016',
                'active'                => true,
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
            )
        );
        
        // For testing sort
        $this->sortBy = 'end_date';
        
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
            'long_description'      => 'This is long body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'cn_long_description'   => 'CN long body',
            'start_date'            => '29/02/2016',
            'end_date'              => '29/02/2016',
            'active'                => true
        );

        $this->call('POST', '/admin/promotions/store', $input);

        $this->assertRedirectedTo('/admin/promotions/create');
        $this->assertSessionHasErrors();
    }
}
