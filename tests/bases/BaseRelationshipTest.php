<?php namespace Redooor\Redminportal\Test;

class BaseRelationshipTest extends RedminTestCase
{
    /**
     * A template to create a new record
     */
    protected function createNewModel($model, $testcase)
    {
        foreach ($testcase as $key => $value) {
            $model->$key = $value;
        }

        $model->save();
        
        return $model;
    }
    
    /**
     * A template to check all testcases
     */
    protected function assertTrueModelAllTestcases($model, $testcase)
    {
        foreach ($testcase as $key => $value) {
            $this->assertTrue($model->$key == $value);
        }
    }
    
    /**
     * A fake test to avoid PHPUnit failure
     */
    public function testFakecall()
    {
        $this->assertTrue(true); // Always true
    }
}
