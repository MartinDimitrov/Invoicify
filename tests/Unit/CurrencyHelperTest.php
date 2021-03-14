<?php

namespace Tests\Unit;

use App\Helpers\CurrencyHelper;
use PHPUnit\Framework\TestCase;

class CurrencyHelperTest extends TestCase
{
    public function testParse()
    {
        $result = CurrencyHelper::parse("EUR:1,BGN:1.22");

        $this->assertEquals(["EUR" => 1, "BGN" => 1.22], $result);
    }

    public function testFailsOnWrongData()
    {
        $this->expectException(\Exception::class);
        $result = CurrencyHelper::parse("EUR:,BGN:1.22");
    }

    public function testFailsOnDifferentWrongData()
    {
        $this->expectException(\Exception::class);
        $result = CurrencyHelper::parse("EURO:1,BGN:1.22");
    }
}
