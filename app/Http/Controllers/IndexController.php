<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $error = $request->session()->get("error");
        return view('index', ["error" => $error]);
    }
}
