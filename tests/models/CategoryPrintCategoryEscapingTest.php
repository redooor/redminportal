<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Category;

class CategoryPrintCategoryEscapingTest extends RedminTestCase
{
    public function testPrintCategoryEscapesNamesAndUsesNumericId()
    {
        $parent = new Category;
        $parent->name = '<img src=x onerror=alert(1)>';
        $parent->short_description = 'Parent';
        $parent->active = true;
        $parent->order = 1;
        $parent->save();

        $child = new Category;
        $child->name = '<script>alert(2)</script>';
        $child->short_description = 'Child';
        $child->active = true;
        $child->order = 1;
        $child->category_id = $parent->id;
        $child->save();

        $html = $parent->printCategory();

        $this->assertStringContainsString("href='" . (int) $parent->id . "'", $html);
        $this->assertStringContainsString('&lt;img', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(2)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }
}
