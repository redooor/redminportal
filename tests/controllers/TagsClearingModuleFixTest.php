<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Module;

/**
 * Covers clearing and keeping tags on module save.
 */
class TagsClearingModuleFixTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/modules';
        $this->viewhas = array(
            'singular' => 'module',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => $this->moduleInput(array(
                'name' => 'Base module',
                'sku' => 'BASE-MODULE'
            )),
            'edit' => $this->moduleInput(array(
                'id' => 1,
                'name' => 'Base module',
                'sku' => 'BASE-MODULE'
            ))
        );

        $this->configureImageUploads();
    }

    public function tearDown(): void
    {
        $this->cleanupImageUploads();

        parent::tearDown();
    }

    /**
     * Test (Pass): omitted tags are kept, empty and null clear them and values replace them
     */
    public function testOmittedTagsAreKeptAndEmptyOrNullTagsClearThem()
    {
        $this->postStore($this->moduleInput(array(
            'name' => 'Tagged module',
            'sku' => 'TAGGED-MODULE',
            'tags' => 'alpha,beta'
        )));

        $this->assertRedirectedTo('/admin/modules');
        $module = Module::first();
        $this->assertSame(array('alpha', 'beta'), $this->tagNames($module));

        $this->postStore($this->moduleInput(array(
            'id' => $module->id,
            'name' => 'Tagged module updated',
            'sku' => 'TAGGED-MODULE'
        )));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame(array('alpha', 'beta'), $this->tagNames(Module::find($module->id)));

        $this->postStore($this->moduleInput(array(
            'id' => $module->id,
            'name' => 'Tagged module updated',
            'sku' => 'TAGGED-MODULE',
            'tags' => ''
        )));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame(array(), $this->tagNames(Module::find($module->id)));

        $this->postStore($this->moduleInput(array(
            'id' => $module->id,
            'name' => 'Tagged module updated',
            'sku' => 'TAGGED-MODULE',
            'tags' => 'gamma,delta'
        )));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame(array('gamma', 'delta'), $this->tagNames(Module::find($module->id)));

        $this->postStore($this->moduleInput(array(
            'id' => $module->id,
            'name' => 'Tagged module updated',
            'sku' => 'TAGGED-MODULE',
            'tags' => null
        )));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame(array(), $this->tagNames(Module::find($module->id)));
    }

    /**
     * Build a valid module input
     */
    private function moduleInput(array $overrides = array())
    {
        return array_merge(array(
            'name' => 'Module name',
            'sku' => 'MODULE-SKU',
            'short_description' => 'Short',
            'long_description' => 'Long',
            'category_id' => 1
        ), $overrides);
    }
}
