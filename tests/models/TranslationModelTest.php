<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Translation;

class TranslationModelTest extends BaseModelTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $model = new Translation;
        $testcase = array(
            'lang' => 'cn',
            'content' => 'This is the content',
            'translatable_id' => 1,
            'translatable_type' => 'Type'
        );
        
        parent::__construct($model, $testcase);
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        parent::__destruct();
    }
}
