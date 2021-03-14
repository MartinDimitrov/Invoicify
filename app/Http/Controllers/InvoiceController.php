<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use App\Helpers\CurrencyHelper;
use App\Custom\Classes\InvoicesCalculator;

class InvoiceController extends Controller
{
    public function result(Request $request)
    {
        try {
            $currencies = CurrencyHelper::parse($request->input("currecies"));
        } catch (\Exception $e) {
            $request->session()->flash("error", $e->getMessage());
            return redirect("/");
        }
        
        $outputCurrency = $request->input('outputCurrency');
        if (!$currencies[$outputCurrency]) {
            $request->session->flash("error", "Invalid output currency");
            redirect("/");
        }
        $calculator = InvoicesCalculator::parse($request->file('invoice'), $currencies, $outputCurrency);
        $results = $calculator->getTotal($request->input('vat'));

        return view('invoice', [
            'results'  => $results,
            'currency' => $outputCurrency,
        ]);
    }
}
