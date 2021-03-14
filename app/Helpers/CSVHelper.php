<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

/**
 * Helper to Handle CSV parsing
 */
class CSVHelper
{
    public static function convertToArray(UploadedFile $file)
    {
        $header = null;
        $data = array();
        if (($handle = fopen($file->path(), 'r')) !== false){
            while (($row = fgetcsv($handle, 1000, ',')) !== false){
                if (!$header){
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }
}