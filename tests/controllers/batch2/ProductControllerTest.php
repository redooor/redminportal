<?php namespace Redooor\Redminportal\Test;

class ProductControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest, TraitProductVariantControllerTest;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/products';
        $this->viewhas = array(
            'singular' => 'product',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => array(
                'name'                  => 'This is title',
                'short_description'     => 'This is body',
                'cn_name'               => 'CN name',
                'cn_short_description'  => 'CN short body',
                'category_id'           => 1,
                'sku'                   => 'UNIQUESKU001',
                'cn_name'               => 'This is cn name',
                'cn_short_description'  => 'This is cn short description',
                'cn_long_description'   => 'This is cn long description',
                'weight_unit'           => 'kg',
                'volume_unit'           => 'm',
                'length'                => 9.99,
                'width'                 => 8.44,
                'height'                => 2.33
            ),
            'edit' => array(
                'id'   => 1,
                'name'                  => 'This is title',
                'short_description'     => 'This is body',
                'cn_name'               => 'CN name',
                'cn_short_description'  => 'CN short body',
                'category_id'           => 1,
                'sku'                   => 'UNIQUESKU001',
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description',
                'weight_unit'           => 'g',
                'volume_unit'           => 'mm',
                'length'                => 8.99,
                'width'                 => 7.44,
                'height'                => 1.33
            )
        );
        
        // For testing sort
        $this->sortBy = 'name';
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
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001'
        );

        $this->call('POST', '/admin/products/store', $input);

        $this->assertRedirectedTo('/admin/products/create');
        $this->assertSessionHasErrors();
    }
}
