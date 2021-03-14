<?php

namespace App\Custom\Classes;

use App\Models\Currency;
use App\Helpers\CSVHelper;
use Illuminate\Http\UploadedFile;

/**
 * Model to handle Invoices and its handling and calculations
 */
class InvoicesCalculator
{
    const TYPE_INVOICE = 1;
    const TYPE_CREDIT  = 2;
    const TYPE_DEBIT   = 3;

    private $file;
    private $defaultCurrency;
    private $currencies;
    private $outputCurrency;


    public static function parse(UploadedFile $file, array $currencies, string $outputCurrency)
    {
        $invoice = new self();
        $invoice->setFile($file);
        $invoice->setCurrencies($currencies);
        $invoice->setOutputCurrency($outputCurrency);
        return $invoice;
    }

    public function setFile(UploadedFile $file)
    {
        $this->file = $file;
    }

    public function setCurrencies(array $currencies)
    {
        $this->currencies = $currencies;
        foreach ($currencies as $name => $value) {
            if ($value == 1) {
                $this->defaultCurrency = $name;
            }
        }
    }

    public function getTotal($vat = '')
    {
        if (!isset($this->file)) {
            throw new \Exception("Calling getTotal before setting the file");
        }

        $data = CSVHelper::convertToArray($this->file);
        $invoices = $this->parseData($data);
        $customers = $this->combineInvoicesPerCustomers($invoices);
        if ($vat) {
            return $customers[$vat] ? [$vat => $customers[$vat]] : false;
        }

        return $customers;
    }

    public function getOutputCurrencyValue()
    {
        return $this->currencies[$this->outputCurrency];
    }

    public function setOutputCurrency(string $outputCurrency)
    {
        $this->outputCurrency = $outputCurrency;
    }

    public function convertCurrencyForOutput(string $currencyName, float $value)
    {
        if ($currencyName === $this->outputCurrency) {
            return $value;
        }

        $valueInDefaultCurrency = $this->convertValueToDefaultCurrency($currencyName, $value);
        // Convert value in default currency to desired output currency
        $valueInOutputCurrency = $valueInDefaultCurrency * $this->getOutputCurrencyValue();

        return round($valueInOutputCurrency, 2);
    }

    public function convertValueToDefaultCurrency(string $currencyName, float $value)
    {
        if ($currencyName === $this->defaultCurrency) {
            return $value;
        }
        
        return $value / $this->currencies[$currencyName];
    }

    /**
     * Function to parse the invoices and combine Debit/Credit Notes into the main document data, 
     * while also validate each document
     * @param $data   array  Array of all invoices
     *
     * @return array Returns all main document with included Debit/Credit Note totals
     */
    private function parseData(array $data)
    {
        $invoices = [];
        // Sort the invoices by document number - Credit or Debit notes cannot have lower number than main invoice
        // Will help out with figuring if it is a valid parent document.
        usort($data,  function ($a, $b) { return $a['Document number'] - $b['Document number']; });
        foreach ($data as $key => $value) {
            if ($value['Type'] == self::TYPE_INVOICE) {
                if ($value['Parent document']) {
                    throw new \Exception("Invoice document cannot have parent");
                }
                if (!isset($this->currencies[$value['Currency']])) {
                    throw new \Exception("Invalid Currency");
                }

                $invoices[$value['Document number']] = [
                    'Customer'   => $value['Customer'],
                    'Vat number' => $value['Vat number'],
                    'Currency'   => $this->outputCurrency,
                    'Total'      => $this->convertCurrencyForOutput($value['Currency'], $value['Total']),
                ];
            } else if ($value['Type'] == self::TYPE_CREDIT || $value['Type'] == self::TYPE_DEBIT) {
                if (!$value['Parent document']) {
                    throw new \Exception("Credit and Debit Note documents must have parent");
                } 

                if (!isset($invoices[$value['Parent document']])) {
                    throw new \Exception("Invalid Parent document");
                }

                $total = $this->convertCurrencyForOutput($value['Currency'], $value['Total']);
                // Combine invoices with the Debit/Credit Notes
                $invoices[$value['Parent document']]['Total'] += ($value['Type'] == self::TYPE_DEBIT ? $total : -$total);
            } else {
                throw new \Exception("Invalid Document type");
            }
        }

        return $invoices;
    }

    /**
     * Function to return the totals for each customer
     * @param $invoices   array   Parsed array of all invoices
     *
     * @return array Returns combined total of all invoices per customer
     */
    private function combineInvoicesPerCustomers(array $invoices)
    {
        $customers = [];
        foreach ($invoices as $invoice) {
            if (isset($customers[$invoice['Vat number']])) {
                $customers[$invoice['Vat number']]['Total'] += $invoice['Total'];
            } else {
                $customers[$invoice['Vat number']]['Customer'] = $invoice['Customer'];
                $customers[$invoice['Vat number']]['Total'] = $invoice['Total'];
            }
        }

        return $customers;
    }
}