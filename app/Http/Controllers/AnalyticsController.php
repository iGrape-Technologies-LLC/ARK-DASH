<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use DB;

use Analytics;
use Spatie\Analytics\Period;




class AnalyticsController extends Controller
{
    public function __construct()
    {
    }

    public function index(){


        //RESUMEN
        $dataResume =  Analytics::performQuery(
            Period::days(31),
            'ga:sessions',
            [
                'metrics' => 'ga:users, ga:newUsers, ga:sessions, ga:percentNewSessions, ga:sessionsPerUser, ga:sessionDuration, ga:avgSessionDuration, ga:pageviews, ga:pageviewsPerSession, ga:bounceRate'/*,
                'dimensions' => 'ga:yearMonth'*/
                //,'samplingLevel' => 'LARGE'
            ]
        );
        if(empty($dataResume['rows'][0])){
            $dataResume = 0;
        } else{
            $dataResume = $dataResume['rows'][0];
        }
        if(!empty($dataResume[6])){
            $dataResume[6] = $this->format_time($dataResume[6]);
        }

        //USUARIOS POR PAIS
        $usersPerCountry =  Analytics::performQuery(
            Period::days(31),
            'ga:sessions',
            [
                'metrics' => 'ga:users',
                'dimensions' => 'ga:country'
            ]
        );
        $usersPerCountryData = array();
        $usersPerCountry = $usersPerCountry['rows'];
        if(!empty($usersPerCountry) && count($usersPerCountry)){
            foreach ($usersPerCountry as $upc) {
                array_push($usersPerCountryData, ['label'=>$upc[0], 'value'=>$upc[1]] );
            }
            array_multisort(array_column($usersPerCountryData, 'value'), SORT_DESC, $usersPerCountryData);
            $usersPerCountryData = array_slice($usersPerCountryData, 0, 8);
        }

        //USUARIOS POR CIUDAD
        $usersPerCity =  Analytics::performQuery(
            Period::days(31),
            'ga:sessions',
            [
                'metrics' => 'ga:users',
                'dimensions' => 'ga:city'
            ]
        );
        $usersPerCityData = array();
        $usersPerCity = $usersPerCity['rows'];
        if(!empty($usersPerCity) && count($usersPerCity)){
            foreach ($usersPerCity as $upc) {
                array_push($usersPerCityData, ['label'=>$upc[0], 'value'=>$upc[1]] );
            }
            array_multisort(array_column($usersPerCityData, 'value'), SORT_DESC, $usersPerCityData);
            $usersPerCityData = array_slice($usersPerCityData, 0, 8);
        }


        //USUARIOS POR FECHA
        //SESIONES POR FECHA
        $daysUsers =  Analytics::performQuery(
            Period::days(31),
            'ga:sessions',
            [
                'metrics' => 'ga:users, ga:sessions',
                'dimensions' => 'ga:date'
            ]
        );
        $daysUsersData = array();
        $daysSessionsData = array();
        $daysUsers = $daysUsers['rows'];
        if(!empty($daysUsers) && count($daysUsers)){
            foreach ($daysUsers as $upc) {
                array_push($daysUsersData, ['dia'=>substr($upc[0], 6,8), 'value'=>$upc[1]] );
                array_push($daysSessionsData, ['dia'=>substr($upc[0], 6,8), 'value'=>$upc[2]] );
            }
        }


        //USUARIOS POR DEVICE
        $devicesUsers =  Analytics::performQuery(
            Period::days(31),
            'ga:sessions',
            [
                'metrics' => 'ga:users',
                'dimensions' => 'ga:deviceCategory'
            ]
        );
        $devicesUsersData = array();
        $devicesUsers = $devicesUsers['rows'];
        if(!empty($devicesUsers) && count($devicesUsers)){
            foreach ($devicesUsers as $upc) {
                array_push($devicesUsersData, ['label'=>$upc[0], 'value'=>$upc[1]] );
            }
            array_multisort(array_column($devicesUsersData, 'value'), SORT_DESC, $devicesUsersData);
        }

        //USUARIOS POR BROWSER
        $browsersUsers =  Analytics::performQuery(
            Period::days(31),
            'ga:sessions',
            [
                'metrics' => 'ga:users',
                'dimensions' => 'ga:browser'
            ]
        );
        $browsersUsersData = array();
        $browsersUsers = $browsersUsers['rows'];
        if(!empty($browsersUsers) && count($browsersUsers)){
            foreach ($browsersUsers as $upc) {
                array_push($browsersUsersData, ['label'=>$upc[0], 'value'=>$upc[1]] );
            }
            array_multisort(array_column($browsersUsersData, 'value'), SORT_DESC, $browsersUsersData);
        }

        //USUARIOS POR SISTEMA OPERATIVO
        $operatingSystemsUsers =  Analytics::performQuery(
            Period::days(31),
            'ga:sessions',
            [
                'metrics' => 'ga:users',
                'dimensions' => 'ga:operatingSystem'
            ]
        );
        $operatingSystemsUsersData = array();
        $operatingSystemsUsers = $operatingSystemsUsers['rows'];
        if(!empty($operatingSystemsUsers) && count($operatingSystemsUsers)){
            foreach ($operatingSystemsUsers as $upc) {
                array_push($operatingSystemsUsersData, ['label'=>$upc[0], 'value'=>$upc[1]] );
            }
            array_multisort(array_column($operatingSystemsUsersData, 'value'), SORT_DESC, $operatingSystemsUsersData);
        }



        //USUARIOS POR SISTEMA OPERATIVO
        $adquisitionsUsers =  Analytics::performQuery(
            Period::days(31),
            'ga:sessions',
            [
                'metrics' => 'ga:users',
                'dimensions' => 'ga:source'
            ]
        );
        $adquisitionsUsersData = array();
        $adquisitionsUsers = $adquisitionsUsers['rows'];
        if(!empty($adquisitionsUsers) && count($adquisitionsUsers)){
            foreach ($adquisitionsUsers as $upc) {
                array_push($adquisitionsUsersData, ['label'=>$upc[0], 'value'=>$upc[1]] );
            }
            array_multisort(array_column($adquisitionsUsersData, 'value'), SORT_DESC, $adquisitionsUsersData);
            $adquisitionsUsersData = array_slice($adquisitionsUsersData, 0, 10);
        }


        //USUARIOS POR HORA
        $horasUsuariosData =  Analytics::performQuery(
            Period::days(31),
            'ga:sessions',
            [
                'metrics' => 'ga:users',
                'dimensions' => 'ga:date, ga:hour'
            ]
        );


        return view('admin.analytics.index')->with(['dataResume'=> $dataResume, 'usersPerCountry'=>$usersPerCountryData, 'usersPerCity'=>$usersPerCityData, 'daysUsers'=> $daysUsersData, 'sessionsUsers'=>$daysSessionsData, 'devicesUsers'=> $devicesUsersData, 'browsersUsers'=> $browsersUsersData, 'operatingSystemsUsers' => $operatingSystemsUsersData, 'adquisitionsUsers' => $adquisitionsUsersData, 'horasUsuarios'=> $horasUsuariosData]);
    }


    public function format_time($t,$f=':') // t = seconds, f = separator
    {
      return sprintf("%02d%s%02d%s%02d", floor($t/3600), $f, ($t/60)%60, $f, $t%60);
    }






}
