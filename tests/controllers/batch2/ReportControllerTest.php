<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Redooor\Redminportal\App\Models\Mailinglist;
use Redooor\Redminportal\App\Models\Order;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportControllerTest extends RedminBrowserTestCase
{
    private $exportedPaths = array();
    private $lastExportedPath;

    /**
     * Initialize Setup with seed
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->seed('RedminSeeder');
        
        $this->loginRedminUser(1);
    }

    public function tearDown(): void
    {
        foreach ($this->exportedPaths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        $this->exportedPaths = array();
        $this->lastExportedPath = null;

        parent::tearDown();
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
     * Test (Pass): the mailing list report downloads an escaped csv file
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
     * Test (Pass): the orders report downloads an escaped xlsx file
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

        $reader = new Xlsx();
        $sheet = $reader->load($this->lastExportedPath)->getSheet(0);

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

    private function exportedFile($expectedName)
    {
        $this->assertResponseOk();
        $this->assertInstanceOf(BinaryFileResponse::class, $this->response->baseResponse);
        $this->assertStringContainsString('attachment', $this->response->headers->get('content-disposition'));
        $this->assertStringContainsString($expectedName, $this->response->headers->get('content-disposition'));

        $path = $this->response->baseResponse->getFile()->getPathname();

        // The test client never sends the response, so the temporary export file is not deleted for us
        $this->exportedPaths[] = $path;
        $this->lastExportedPath = $path;

        return file_get_contents($path);
    }
}
