<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\Media;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\ModuleMediaMembership;

class ModuleControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest, TraitImageControllerTest;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/modules';
        $this->viewhas = array(
            'singular' => 'module',
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
                'sku'                   => 'UNIQUESKU001',
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
            )
        );
        
        // For testing sort
        $this->sortBy = 'name';

        // For testing image
        $this->img_parent_model = new Module;
        $this->img_parent_create = [
            'name' => 'This is the title',
            'sku' => 'UNIQUESKU001',
            'short_description' => 'This is the body',
            'category_id' => 1,
            'active' => true
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
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001'
        );

        $this->call('POST', '/admin/modules/store', $input);

        $this->assertRedirectedTo('/admin/modules/create');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access postStore to create with input and price for 1 membership
     */
    public function testStorePricingPass()
    {
        // Add membership
        $membership = new Membership;
        $membership->name = "Gold";
        $membership->rank = 5;
        $membership->save();

        $input = array(
            'name'                  => 'This is title',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001',
            'price_1'               => 99.99,
            'price_active_1'        => true
        );

        $this->call('POST', '/admin/modules/store', $input);

        $this->assertRedirectedTo('/admin/modules');

        $pricelist = Pricelist::where('module_id', 1)->where('membership_id', 1)->first();

        $this->assertTrue($pricelist != null);
        $this->assertTrue($pricelist->module_id == 1);
        $this->assertTrue($pricelist->membership_id == 1);
        $this->assertTrue($pricelist->price == 99.99);
        $this->assertTrue($pricelist->active == true);
    }

    /**
     * Test (Pass): access postStore to edit with input and price for 1 membership
     */
    public function testEditPricingPass()
    {
        $this->testStorePricingPass();

        $input = array(
            'id'                    => 1,
            'name'                  => 'This is title',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001',
            'price_1'               => 88.88,
            'price_active_1'        => false
        );

        $this->call('POST', '/admin/modules/store', $input);

        $this->assertRedirectedTo('/admin/modules');

        $pricelist = Pricelist::where('module_id', 1)->where('membership_id', 1)->first();

        $this->assertTrue($pricelist != null);
        $this->assertTrue($pricelist->module_id == 1);
        $this->assertTrue($pricelist->membership_id == 1);
        $this->assertTrue($pricelist->price == 88.88);
        $this->assertTrue($pricelist->active == false);
    }
    
    /**
     * Test (Pass): access postStore to create with input, price for 1 membership and medias
     */
    public function testStoreMediasPass()
    {
        // Add membership
        $membership = new Membership;
        $membership->name = "Gold";
        $membership->rank = 5;
        $membership->save();

        // Add media
        $media = new Media;
        $media->name = 'This is the title';
        $media->path = 'path/to/somewhere';
        $media->sku = 'UNIQUESKU001';
        $media->short_description = 'This is the body';
        $media->category_id = 1;
        $media->active = true;
        $media->save();

        $input = array(
            'name'                  => 'This is title',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001',
            'price_1'               => 99.99,
            'media_checkbox'        => array('1_1'),
            'price_active_1'        => true
        );

        $this->call('POST', '/admin/modules/store', $input);

        $this->assertRedirectedTo('/admin/modules');

        $pricelist = Pricelist::where('module_id', 1)
            ->where('membership_id', 1)->first();

        $this->assertTrue($pricelist != null);
        $this->assertTrue($pricelist->module_id == 1);
        $this->assertTrue($pricelist->membership_id == 1);
        $this->assertTrue($pricelist->price == 99.99);
        $this->assertTrue($pricelist->active == true);

        $modMediaMembership = ModuleMediaMembership::where('module_id', 1)
            ->where('membership_id', 1)
            ->where('media_id', 1)
            ->first();

        $this->assertTrue($modMediaMembership != null);
    }
}
