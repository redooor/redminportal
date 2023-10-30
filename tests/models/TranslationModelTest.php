<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Translation;

class TranslationModelTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $model = new Translation;
        $testcase = array(
            'lang' => 'cn',
            'content' => 'This is the content',
            'translatable_id' => 1,
            'translatable_type' => 'Type'
        );
        
        $this->prepare($model, $testcase);
    }
}
