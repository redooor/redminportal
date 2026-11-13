<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Helpers\RHelper;

class RHelperCleanHtmlTest extends RedminTestCase
{
    public function testScriptTagRemoved()
    {
        $output = RHelper::cleanHtml('<p>before</p><script>alert(1)</script><p>after</p>');

        $this->assertStringNotContainsString('<script', $output);
        $this->assertStringNotContainsString('</script>', $output);
        $this->assertStringContainsString('<p>before</p>', $output);
        $this->assertStringContainsString('<p>after</p>', $output);
    }

    public function testEventAttributesRemoved()
    {
        $output = RHelper::cleanHtml(
            '<img src="https://x.test/a.png" onerror="alert(1)" onclick="alert(1)" alt="a">'
        );

        $this->assertStringNotContainsString('onerror', $output);
        $this->assertStringNotContainsString('onclick', $output);
        $this->assertStringContainsString('src="https://x.test/a.png"', $output);
    }

    public function testJavascriptHrefRemoved()
    {
        $output = RHelper::cleanHtml('<a href="javascript:alert(1)">click</a>');

        $this->assertStringNotContainsString('javascript:', $output);
        $this->assertStringContainsString('click', $output);
    }

    public function testDataImageSourceRemoved()
    {
        $output = RHelper::cleanHtml('<img src="data:image/png;base64,PHN2Zz4=">');

        $this->assertStringNotContainsString('data:', $output);
        $this->assertStringNotContainsString('<img', $output);
    }

    public function testAllowedMarkupPreserved()
    {
        $input = '<p><strong>ok</strong></p>';

        $this->assertSame($input, RHelper::cleanHtml($input));
    }

    public function testTargetLinkKeepsHrefAndGetsNoopener()
    {
        $output = RHelper::cleanHtml('<a href="https://x.test" target="_blank">x</a>');

        $this->assertStringContainsString('href="https://x.test"', $output);
        $this->assertStringContainsString('target="_blank"', $output);
        $this->assertMatchesRegularExpression('/rel="[^"]*noopener[^"]*"/', $output);
        $this->assertMatchesRegularExpression('/rel="[^"]*noreferrer[^"]*"/', $output);
    }

    public function testStyleAttributesRemoved()
    {
        $output = RHelper::cleanHtml('<p style="color:red">text</p>');

        $this->assertStringNotContainsString('style=', $output);
        $this->assertStringContainsString('<p>text</p>', $output);
    }

    public function testNullOrEmptyReturnsEmptyString()
    {
        $this->assertSame('', RHelper::cleanHtml(null));
        $this->assertSame('', RHelper::cleanHtml(''));
    }
}
