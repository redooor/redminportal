<?php namespace Redooor\Redminportal\Test;

class FormLabelIdsMarkupTest extends RedminTestCase
{
    /**
     * Test (Pass): the form input label follows the effective input id.
     */
    public function testFormInputLabelMatchesId()
    {
        $html = view('redminportal::partials.form-input', [
            'label' => 'Title',
            'input_name' => 'name',
        ])->render();

        $this->assertLabelMatchesId($html, 'name');
        $this->assertStringContainsString('name="name"', $html);

        $html = view('redminportal::partials.form-input', [
            'label' => 'Title',
            'input_name' => 'name',
            'input_id' => 'variant-name',
        ])->render();

        $this->assertLabelMatchesId($html, 'variant-name');
        $this->assertStringContainsString('name="name"', $html);
    }

    /**
     * Test (Pass): the select label follows the effective select id.
     */
    public function testFormSelectLabelMatchesId()
    {
        $html = view('redminportal::partials.form-select-option', [
            'label' => 'Unit',
            'select_name' => 'weight_unit',
            'select_options' => ['kg' => 'kg'],
        ])->render();

        $this->assertLabelMatchesId($html, 'weight_unit');
        $this->assertStringContainsString('name="weight_unit"', $html);

        $html = view('redminportal::partials.form-select-option', [
            'label' => 'Unit',
            'select_name' => 'weight_unit',
            'select_id' => 'variant-weight_unit',
            'select_options' => ['kg' => 'kg'],
        ])->render();

        $this->assertLabelMatchesId($html, 'variant-weight_unit');
        $this->assertStringContainsString('name="weight_unit"', $html);
    }

    /**
     * Test (Pass): the language selector fields and labels use the id prefix.
     */
    public function testPrefixedLangSelectorLabelMatchesId()
    {
        $html = view('redminportal::partials.lang-selector-form', [
            'id_prefix' => 'variant-',
        ])->render();
        $names = $this->fieldNames();

        $this->assertEveryLabelMatchesAnId($html);
        $this->assertCount(count($names), $this->labelTargets($html));

        foreach ($names as $name) {
            $this->assertStringContainsString('name="' . $name . '"', $html);
            $this->assertLabelMatchesId($html, 'variant-' . $name);
        }
    }

    /**
     * Test (Pass): without a prefix the language selector keeps the existing ids.
     */
    public function testUnprefixedLangSelectorKeepsExistingIds()
    {
        $html = view('redminportal::partials.lang-selector-form')->render();
        $names = $this->fieldNames();

        $this->assertEveryLabelMatchesAnId($html);
        $this->assertCount(count($names), $this->labelTargets($html));
        $this->assertStringNotContainsString('variant-', $html);

        foreach ($names as $name) {
            $this->assertStringContainsString('name="' . $name . '"', $html);
            $this->assertLabelMatchesId($html, $name);
        }
    }

    /**
     * Assert that a label points at a field with the same id.
     */
    private function assertLabelMatchesId($html, $id)
    {
        $quoted = preg_quote($id, '/');

        $this->assertMatchesRegularExpression('/<label\b[^>]*\bfor="' . $quoted . '"/', $html);
        $this->assertMatchesRegularExpression('/<(?:input|select|textarea)\b[^>]*\bid="' . $quoted . '"/', $html);
    }

    /**
     * Assert that every label for value has a matching id.
     */
    private function assertEveryLabelMatchesAnId($html)
    {
        preg_match_all('/\bid="([^"]*)"/i', $html, $ids);
        $ids = array_flip($ids[1]);
        $targets = $this->labelTargets($html);

        $this->assertNotEmpty($targets);

        foreach ($targets as $target) {
            $this->assertArrayHasKey($target, $ids, 'Missing id for label ' . $target);
        }
    }

    /**
     * Return the for values of all labels.
     */
    private function labelTargets($html)
    {
        preg_match_all('/<label\b[^>]*\bfor="([^"]*)"/i', $html, $labels);

        return $labels[1];
    }

    /**
     * Return the field names configured for the language selector.
     */
    private function fieldNames()
    {
        $names = ['name', 'short_description', 'long_description'];

        foreach (config('redminportal::translation') as $translation) {
            if ($translation['lang'] === 'en') {
                continue;
            }

            $names[] = $translation['lang'] . '_name';
            $names[] = $translation['lang'] . '_short_description';
            $names[] = $translation['lang'] . '_long_description';
        }

        return $names;
    }
}
