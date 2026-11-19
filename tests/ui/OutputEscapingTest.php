<?php namespace Redooor\Redminportal\Test;

class OutputEscapingTest extends RedminTestCase
{
    public function testLanguageSelectorEscapesNamesAndSanitisesDescriptions()
    {
        $translatable = (object) [
            'name' => '<img src=x onerror=alert(1)>',
            'short_description' => '<script>alert(1)</script>Short',
            'long_description' => '<p>ok</p><script>alert(1)</script><img src=x onerror=alert(1)>',
        ];
        $translated = [
            'cn' => (object) [
                'name' => '<script>alert(2)</script>',
                'short_description' => '<script>alert(2)</script>中文摘要',
                'long_description' => '<b>粗体</b><iframe src="https://x.test"></iframe>',
            ],
        ];

        $html = view('redminportal::partials.lang-selector-view', [
            'translatable' => $translatable,
            'translated' => $translated,
        ])->render();

        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(2)&lt;/script&gt;', $html);
        $this->assertStringContainsString('<p>ok</p>', $html);
        $this->assertStringContainsString('Short', $html);
        $this->assertStringContainsString('中文摘要', $html);
        $this->assertStringContainsString('<b>粗体</b>', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringNotContainsString('<script>alert(2)', $html);
        $this->assertStringNotContainsString('<iframe', $html);
        $this->assertNotRegExp('/<[^>]*\sonerror\s*=/i', $html);
    }

    public function testOrderPluginBuildsRowsWithDomTextApis()
    {
        $source = file_get_contents(dirname(__DIR__) . '/../src/resources/views/plugins/tablesinput-orders.blade.php');

        $this->assertStringContainsString('findOrderRow', $source);
        $this->assertStringContainsString('attr(\'data-row-id\', $select_row_id)', $source);
        $this->assertStringContainsString('text($select_name)', $source);
        $this->assertStringContainsString('text-right', $source);
        $this->assertStringContainsString('btn-xs', $source);
        $this->assertStringNotContainsString('$product_cells', $source);
        $this->assertStringNotContainsString("<input type='hidden'", $source);
        $this->assertStringNotContainsString('$(\'#\' + $select_row_id)', $source);
        $this->assertStringNotContainsString('.html($product_cells)', $source);
    }
}
