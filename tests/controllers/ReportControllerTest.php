<?php namespace Redooor\Redminportal\Test;

class ReportControllerTest extends \RedminTestCase
{
    /**
     * Test (Pass): access getIndex
     */
    public function testIndex()
    {
        $crawler = $this->client->request('GET', '/admin/reports');

        $this->assertResponseOk();
        $this->assertCount(1, $crawler->filter('h1:contains("Oops, 404!")'));
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
}
