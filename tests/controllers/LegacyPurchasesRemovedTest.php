<?php namespace Redooor\Redminportal\Test;

use Database\Seeders\RedminSeeder;

class LegacyPurchasesRemovedTest extends RedminBrowserTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RedminSeeder::class);
        $this->loginRedminUser(1);
    }

    public function testPurchasesPagesAreNoLongerRouted()
    {
        $this->call('GET', '/admin/purchases');
        $this->assertResponseStatus(404);

        $this->call('POST', '/admin/reports/purchases', array());
        $this->assertResponseStatus(404);
    }

    public function testPermissionListNoLongerContainsPurchases()
    {
        $permissions = array_column(config('redminportal::permissions.routes'), 'value');

        $this->assertNotContains('admin.purchases', $permissions);
        $this->assertContains('admin.orders', $permissions);
    }
}
