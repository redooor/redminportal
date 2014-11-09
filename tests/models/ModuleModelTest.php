<?php

use Redooor\Redminportal\Module;

class ModuleModelTest extends \RedminTestCase {

    public function testAll()
    {
        $modules = Module::all();
        $this->assertTrue($modules != null);
    }

    public function testFind1Fails()
    {
        $module = Module::find(1);
        $this->assertTrue($module == null);
    }

    public function testCreateNew()
    {
        $module = new Module;
        $module->name = 'This is the title';
        $module->sku = 'UNIQUESKU001';
        $module->short_description = 'This is the body';
        $module->category_id = 1;
        $module->active = true;

        $result = $module->save();

        $this->assertTrue($module->id == 1);
        $this->assertTrue($module->name == 'This is the title');
        $this->assertTrue($module->sku == 'UNIQUESKU001');
        $this->assertTrue($module->short_description == 'This is the body');
        $this->assertTrue($module->category_id == 1);
        $this->assertTrue($module->featured == false);
        $this->assertTrue($module->active == true);
        $this->assertTrue($result == 1);
    }

    public function testFind1()
    {
        $this->testCreateNew(); //Create new first

        $module = Module::find(1);

        $this->assertTrue($module != null);
        $this->assertTrue($module->id == 1);
        $this->assertTrue($module->name == 'This is the title');
    }

    public function testPagniate()
    {
        $modules = Module::paginate(20);
    }

    public function testOrderBy()
    {
        $modules = Module::orderBy('updated_at');
    }

    public function testOrderByThenPaginate()
    {
        $modules = Module::orderBy('updated_at')->paginate(20);
    }

    public function testDestroy()
    {
        $this->testCreateNew(); //Create new first

        $module = Module::find('1');
        $module->delete();

        $result = Module::find('1');

        $this->assertTrue($result == null);
    }

}
