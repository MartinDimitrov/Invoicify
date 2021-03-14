<?php

namespace Tests\Unit;

use App\Helpers\CSVHelper;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\UploadedFile;

class CSVHelperTest extends TestCase
{
    public function testConvert()
    {
        $path = __DIR__ . '/invoices_partial.csv';
        $file = new UploadedFile($path, "invoices.csv", "csv", null, true);
        $array = CSVHelper::convertToArray($file);
        $this->assertEquals($this->getExpectedArray(), $array);
    }

    private function getExpectedArray()
    {
        return [
            0 => [
                'Customer'        => 'Vendor 1',
                'Vat number'      => '123456789',
                'Document number' => '1000000257',
                'Type'            => '1',
                'Parent document' => '',
                'Currency'        => 'USD',
                'Total'           => '400',  
            ],
        ];
    }
}
