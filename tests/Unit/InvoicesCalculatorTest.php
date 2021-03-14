<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Http\UploadedFile;
use App\Custom\Classes\InvoicesCalculator;

class InvoicesCalculatorTest extends TestCase
{
    public function testBasicParse()
    {
        $path = __DIR__ . '/invoices.csv';
        $file = new UploadedFile($path, "invoices.csv", "csv", null, true);
        $calculator = InvoicesCalculator::parse($file, $this->getCurrencies(), 'EUR');
        $this->assertInstanceOf(InvoicesCalculator::class, $calculator);
    }

    public function testGetTotal()
    {
        $path = __DIR__ . '/invoices.csv';
        $file = new UploadedFile($path, "invoices.csv", "csv", null, true);
        $calculator = InvoicesCalculator::parse($file, $this->getCurrencies(), 'EUR');
        $total = $calculator->getTotal();

        $this->assertEquals($this->getDesiredTotalForAll(), $total);
    }

    public function testGetTotalWithSetVat()
    {
        $path = __DIR__ . '/invoices.csv';
        $file = new UploadedFile($path, "invoices.csv", "csv", null, true);
        $calculator = InvoicesCalculator::parse($file, $this->getCurrencies(), 'EUR');
        $total = $calculator->getTotal('123456789');

        $expected = [
            '123456789' => [
                'Customer' => 'Vendor 1',
                'Total'    => '2333.33',
            ]
        ];

        $this->assertEquals($expected, $total);
    }

    public function testFailsWhenMissingCurrency()
    {
        $this->expectException(\Exception::class);
        $path = __DIR__ . '/invoices.csv';
        $file = new UploadedFile($path, "invoices.csv", "csv", null, true);
        $calculator = InvoicesCalculator::parse($file, ["EUR" => 1], 'EUR');
        $total = $calculator->getTotal();
    }

    public function testFailsWhenWrongParrent()
    {
        $this->expectException(\Exception::class);
        $path = __DIR__ . '/invoices_error.csv';
        $file = new UploadedFile($path, "invoices.csv", "csv", null, true);
        $calculator = InvoicesCalculator::parse($file, $this->getCurrencies(), 'EUR');
        $total = $calculator->getTotal();
    }

    private function getDesiredTotalForAll()
    {
        return [
            123456789 => [
                "Customer" => "Vendor 1",
                "Total" => 2333.33,
            ],
            987654321 => [
                "Customer" => "Vendor 2",
                "Total" => 500,
            ],
            123465123 => [
                "Customer" => "Vendor 3",
                "Total" => 966.67,
            ],
        ];
    }

    private function getCurrencies()
    {
        return ['EUR' => 1, "USD" => 0.5, 'GBP' => 1.5];
    }
}
