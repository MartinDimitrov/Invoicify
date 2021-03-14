<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

/**
 * Helper to Handle currencies string parsing
 */
class CurrencyHelper
{
    public static function parse(string $data)
    {
        $data = explode(",", $data);
        $currencies = [];
        foreach ($data as $currency) {
            $temp = explode(":", $currency);
            self::validate($temp);
            $currencies[$temp[0]] = $temp[1];
        }

        return $currencies;
    }

    private static function validate(array $data)
    {
        // If there is no 2nd element or there is a third element the data is not correct for sure
        if (isset($data[2]) || !isset($data[1])) {
            throw new \Exception("Invalid currency list");
        }

        // if Exchange rate is not numeric or currency name is not of 3 symbols the data is not correct
        if (strlen($data[0]) !== 3 || !is_numeric($data[1])) {
            throw new \Exception("Invalid currency list");
        }
    }
}