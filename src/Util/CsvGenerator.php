<?php

namespace App\Util;

class CsvGenerator
{
    public static function generateCsv(array $data): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Date', 'Open', 'High', 'Low', 'Close', 'Volume']);

        foreach ($data as $row) {
            fputcsv($handle, [
                date('Y-m-d', $row['date']),
                $row['open'],
                $row['high'],
                $row['low'],
                $row['close'],
                $row['volume'],
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);
        
        return $csvContent;
    }
}
