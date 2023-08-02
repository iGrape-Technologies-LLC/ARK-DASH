<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LanguageController extends Controller
{

    public function changeLanguage($locale) {
        session(['locale' => $locale]);
        return redirect()->back();
    }

}
