<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ItemControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_getSalesOrderStockAjax_reduces_stock_correctly()
    {
        // Mock user login
        $user = User::factory()->create([
            'status' => 'karyawan',
        ]);
        $this->actingAs($user);

        // Mock HTTP responses for sales order list and details
        Http::fake([
            'https://public.accurate.id/accurate/api/sales-order/list.do*' => Http::response([
                'd' => [
                    ['id' => 'SO1', 'statusName' => 'WAITING', 'number' => 'SO.001'],
                ],
                'sp' => ['rowCount' => 1],
            ], 200),
            'https://public.accurate.id/accurate/api/sales-order/detail.do?id=SO1' => Http::response([
                'd' => [
                    'statusName' => 'Menunggu diproses',
                    'number' => 'SO.001',
                    'detailItem' => [
                        [
                            'itemId' => 1,
                            'warehouseId' => 100,
                            'availableQuantity' => 5,
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Prepare initial stokNew with warehouse 100 having balance 10
        $stokNew = [
            100 => ['name' => 'Gudang 100', 'balance' => 10],
        ];

        // Call protected method fetchSalesOrderDetailsBatch via reflection
        $controller = new \App\Http\Controllers\ItemController();
        $method = new \ReflectionMethod($controller, 'fetchSalesOrderDetailsBatch');
        $method->setAccessible(true);

        $salesOrderList = [
            ['id' => 'SO1', 'statusName' => 'WAITING', 'number' => 'SO.001'],
        ];

        $method->invokeArgs($controller, [$salesOrderList, [], 1, &$stokNew]);

        // Assert stokNew balance is reduced by 5
        $this->assertEquals(5, $stokNew[100]['balance']);
    }

    public function test_getMatchingInvoices_reduces_stock_correctly()
    {
        // Mock user login
        $user = User::factory()->create([
            'status' => 'karyawan',
        ]);
        $this->actingAs($user);

        // Mock HTTP responses for sales invoice list and details
        Http::fake([
            'https://public.accurate.id/accurate/api/sales-invoice/list.do*' => Http::response([
                'd' => [
                    ['id' => 'SI1', 'number' => 'SI.001', 'reverseInvoice' => true],
                ],
                'sp' => ['rowCount' => 1],
            ], 200),
            'https://public.accurate.id/accurate/api/sales-invoice/detail.do?id=SI1' => Http::response([
                'd' => [
                    'number' => 'SI.001',
                    'reverseInvoice' => true,
                    'detailItem' => [
                        [
                            'item' => ['id' => 1],
                            'warehouseId' => 200,
                            'quantity' => 3,
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Prepare initial stokNew with warehouse 200 having balance 10
        $stokNew = [
            200 => ['name' => 'Gudang 200', 'balance' => 10],
        ];

        // Call protected method fetchMatchingInvoices via reflection
        $controller = new \App\Http\Controllers\ItemController();
        $method = new \ReflectionMethod($controller, 'fetchMatchingInvoices');
        $method->setAccessible(true);

        $salesInvoiceList = [
            ['id' => 'SI1', 'number' => 'SI.001', 'reverseInvoice' => true],
        ];

        $method->invokeArgs($controller, [$salesInvoiceList, [], 1, &$stokNew]);

        // Assert stokNew balance is reduced by 3
        $this->assertEquals(7, $stokNew[200]['balance']);
    }
}
