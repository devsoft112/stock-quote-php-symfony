<?php

namespace App\Tests\Util;

use App\Util\CsvGenerator;
use PHPUnit\Framework\TestCase;

class CsvGeneratorTest extends TestCase
{
    public function testGenerateCsv()
    {
        $data = [
            ['date' => strtotime('2023-01-01'), 'open' => 150, 'high' => 155, 'low' => 145, 'close' => 152, 'volume' => 1000],
            ['date' => strtotime('2023-01-02'), 'open' => 153, 'high' => 158, 'low' => 149, 'close' => 157, 'volume' => 1100],
        ];

        $csvContent = CsvGenerator::generateCsv($data);
        $expectedCsv = "Date,Open,High,Low,Close,Volume\n2023-01-01,150,155,145,152,1000\n2023-01-02,153,158,149,157,1100\n";
        $this->assertEquals($expectedCsv, $csvContent);
    }
}
