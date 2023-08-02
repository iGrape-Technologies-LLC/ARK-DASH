<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClimaController extends Controller
{

    public static function getClima() {

        $xml = simplexml_load_file('http://api.meteored.com.ar/index.php?api_lang=ar&localidad='.config('config.METEORED_CITY_ID').'&affiliate_id='.config('config.METEORED_AFFILIATE_ID').'&v=2');

        $json = json_encode($xml);
        $json = str_replace('@', '', $json);
        $array = json_decode($json,TRUE);
        
        return($array);

    }

    
}
