<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Helpers\RHelper;
use Redooor\Redminportal\App\Models\Mailinglist;

class RHelperExportCellTest extends RedminTestCase
{
    public function testFormulaPrefixesAreNeutralised()
    {
        $this->assertSame("'=cmd|' /C calc'!A0", RHelper::exportCell("=cmd|' /C calc'!A0"));
        $this->assertSame("'+1+1", RHelper::exportCell('+1+1'));
        $this->assertSame("'-2+3", RHelper::exportCell('-2+3'));
        $this->assertSame("'@SUM(1)", RHelper::exportCell('@SUM(1)'));
        $this->assertSame("'\t=1", RHelper::exportCell("\t=1"));
        $this->assertSame("'\r=1", RHelper::exportCell("\r=1"));
    }

    public function testCommaIsReplacedWithDot()
    {
        $this->assertSame('normal. text', RHelper::exportCell('normal, text'));
    }

    public function testPlainValuesAreUnchanged()
    {
        $this->assertSame('Peter Lim', RHelper::exportCell('Peter Lim'));
        $this->assertSame('admin@example.com', RHelper::exportCell('admin@example.com'));
        $this->assertSame('123.45', RHelper::exportCell(123.45));
    }

    public function testNullOrEmptyReturnsEmptyString()
    {
        $this->assertSame('', RHelper::exportCell(null));
        $this->assertSame('', RHelper::exportCell(''));
    }

    public function testMailinglistReportViewEscapesFormulaCell()
    {
        $item = new Mailinglist;
        $item->email = 'formula@example.com';
        $item->first_name = '=1+1';
        $item->last_name = 'Tester';
        $item->active = true;

        $html = view('redminportal::reports/mailinglist', ['data' => collect([$item])])->render();

        $this->assertStringContainsString('&#039;=1+1', $html);
        $this->assertStringContainsString("'=1+1", html_entity_decode($html, ENT_QUOTES, 'UTF-8'));
    }
}
