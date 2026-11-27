<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Redooor\Redminportal\App\Models\Mailinglist;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\UserPricelist;

class ReportControllerTest extends RedminBrowserTestCase
{
    /**
     * Initialize Setup with seed
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->seed('RedminSeeder');
        
        $this->loginRedminUser(1);
    }
    
    /**
     * Test (Pass): access getIndex
     */
    public function testIndex()
    {
        $this->call('GET', '/admin/reports');

        $this->assertResponseOk();
    }
    
    /**
     * Test (Fail): access postMailinglist with input but no data found
     */
    public function testReportMailinglists()
    {
        $input = array(
            'start_date' => '29/02/2016',
            'end_date' => '29/02/2016'
        );

        $this->call('POST', 'admin/reports/mailinglist', $input);

        $this->assertRedirectedTo('admin/mailinglists');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access postPurchases with input but no data found
     */
    public function testReportPurchases()
    {
        $input = array(
            'start_date' => '29/02/2016',
            'end_date' => '29/02/2016'
        );

        $this->call('POST', 'admin/reports/purchases', $input);

        $this->assertRedirectedTo('admin/purchases');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access postOrders with input but no data found
     */
    public function testReportOrders()
    {
        $input = array(
            'start_date' => '29/02/2016',
            'end_date' => '29/02/2016'
        );

        $this->call('POST', 'admin/reports/orders', $input);

        $this->assertRedirectedTo('admin/orders');
        $this->assertSessionHasErrors();
    }

    /**
     * Download the response file for an export and return its contents
     */
    private function exportedFile($expectedName)
    {
        $this->assertResponseOk();
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class, $this->response);
        $this->assertStringContainsString('attachment', $this->response->headers->get('content-disposition'));
        $this->assertStringContainsString($expectedName, $this->response->headers->get('content-disposition'));

        $path = $this->response->getFile()->getPathname();

        // The test client never sends the response, so the temporary export file is not deleted for us
        $this->exportedPaths[] = $path;

        return file_get_contents($path);
    }

    private $exportedPaths = [];

    public function tearDown(): void
    {
        foreach ($this->exportedPaths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $this->exportedPaths = [];

        parent::tearDown();
    }

    /**
     * Test (Pass): the mailing list export downloads a csv file and escapes cells that start like a formula
     */
    public function testReportMailinglistsDownloadsCsv()
    {
        $item = new Mailinglist;
        $item->email = 'report-one@test.com';
        $item->first_name = '=SUM(1+1)';
        $item->last_name = 'Reporter';
        $item->active = true;
        $item->save();

        $this->call('POST', 'admin/reports/mailinglist', []);

        $content = $this->exportedFile('Redmin_Mailinglist_Report.csv');

        $this->assertStringContainsString('report-one@test.com', $content);
        $this->assertStringContainsString("'=SUM(1+1)", $content);
        $this->assertStringNotContainsString(',=SUM', $content);
        $this->assertStringNotContainsString('"=SUM', $content);
    }

    /**
     * Test (Pass): the orders export downloads an xlsx file
     */
    public function testReportOrdersDownloadsXlsx()
    {
        $order = new Order;
        $order->user_id = 1;
        $order->paid = 10.99;
        $order->transaction_id = '+TX-1';
        $order->payment_status = 'Completed';
        $order->save();

        $this->call('POST', 'admin/reports/orders', []);

        $content = $this->exportedFile('Redmin_Orders_Report.xlsx');

        $this->assertSame('PK', substr($content, 0, 2), 'The orders report is not an xlsx (zip) file');

        // Reopen the workbook: the sheet has the report title and the transaction id is a text cell, prefixed so it is not a formula
        $path = $this->exportedPaths[count($this->exportedPaths) - 1];
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $sheet = $reader->load($path)->getSheet(0);

        $this->assertSame('Orders Report', $sheet->getTitle());

        $transactionCell = null;
        foreach ($sheet->toArray(null, false, false, false) as $row) {
            foreach ($row as $value) {
                if (is_string($value) && strpos($value, 'TX-1') !== false) {
                    $transactionCell = $value;
                }
            }
        }

        $this->assertSame("'+TX-1", $transactionCell, 'The order row is missing or the transaction id is not escaped');
    }

    /**
     * Test (Pass): the purchases export downloads a csv file
     */
    public function testReportPurchasesDownloadsCsv()
    {
        $membership = new Membership;
        $membership->name = 'Gold';
        $membership->rank = 5;
        $membership->save();

        $this->call('POST', '/admin/modules/store', [
            'name' => 'Report module',
            'short_description' => 'Body',
            'category_id' => 1,
            'sku' => 'REPORTSKU1',
            'price_1' => 9.99,
            'price_active_1' => true
        ]);

        $purchase = new UserPricelist;
        $purchase->user_id = 1;
        $purchase->pricelist_id = 1;
        $purchase->paid = 9.99;
        $purchase->transaction_id = 'TX-PURCHASE-1';
        $purchase->payment_status = 'Completed';
        $purchase->save();

        $this->call('POST', 'admin/reports/purchases', []);

        $content = $this->exportedFile('Redmin_Purchases_Report.csv');

        $this->assertStringContainsString('TX-PURCHASE-1', $content);
        $this->assertStringContainsString('Report module', $content);
    }
}
