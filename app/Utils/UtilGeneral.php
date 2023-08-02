<?php

namespace App\Utils;

use Carbon\Carbon;
use App\Models\Photo;
use Jenssegers\Date\Date;

class UtilGeneral 
{
    public function changeStatus($id, $status_row, $controller){
        $status = 'success';
        $status_text =__('general.active');
        if($status_row == 0) {
            $status = 'danger'; 
            $status_text =__('general.no_active');
        }        
        return '<button type="button" class="btn btn-sm btn-outline-'.$status.' change_status" data-href="' . action($controller.'@changeStatus', [$id]) . '">'.$status_text.'</button>';
    }


    /**
     * Converts mysql to business date
     *
     * @param string $time
     * @return strin
     */
    public static function format_date($date, $show_time = true, $timezone = 'America/Buenos_Aires')
    {           
        $format = 'd/m/Y';
        if (($show_time)) {
                $format .= ' H:i';          
        }
        return Carbon::createFromTimestamp(strtotime($date), $timezone)->format($format);
    }

    public static function format_date_long($date, $show_time = true, $timezone = 'America/Buenos_Aires')
    {           
        $format = 'l j \de F \d\e\l Y';
        if (($show_time)) {
                $format .= ' \- H:i \hs.';          
        }
        return Date::createFromTimestamp(strtotime($date), $timezone)->format($format);
    }

    public static function format_date_short($date, $show_time = true, $timezone = 'America/Buenos_Aires')
    {           
        $format = 'j \de F';
        if (($show_time)) {
                $format .= ' \- H:i \h\s\.';          
        }
        return  Date::createFromTimestamp(strtotime($date), $timezone)->format($format);
    }

    /**
     * Converts mysql to business date
     *
     * @param string $time
     * @return strin
     */
    public function format_date_to_mysql($date, $timezone = 'America/Buenos_Aires')
    {           
        $format = 'd/m/Y H:i';
        return Carbon::createFromFormat($format, $date, $timezone)->setTimezone('UTC')->toDateTimeString();
    }

    /**
     * Converts mysql to business date
     *
     * @param string $time
     * @return strin
     */
    public function format_time($date, $timezone = 'America/Buenos_Aires', $time_format = 24)
    {           
        $time_format = 'H:i';
        if ($time_format == 12) {
            $time_format = 'h:i A';
        }
        return Carbon::createFromTimestamp(strtotime($date), $timezone)->format($time_format);
    }

    /**
     * Converts time to mysql format
     *
     * @param string $time
     * @return strin
     */
    public function format_time_to_mysql($time, $time_format = 24)
    {
        $time_format = 'H:i';
        if ($time_format == 12) {
            $time_format = 'h:i A';
        }
        return \Carbon::createFromFormat('H:i:s', $time)->format($time_format);
    }

    /**
     * Checks whether mail is configured or not
     *
     * @return boolean
     */
    public function IsMailConfigured()
    {
        $is_mail_configured = false;

        if (!empty(env('MAIL_DRIVER')) &&
            !empty(env('MAIL_HOST')) &&
            !empty(env('MAIL_PORT')) &&
            !empty(env('MAIL_USERNAME')) &&
            !empty(env('MAIL_PASSWORD')) &&
            !empty(env('MAIL_FROM_ADDRESS'))
            ) {
            $is_mail_configured = true;
        }

        return $is_mail_configured;
    }

    public static function number_format($number, $currency = '$') {
        return $currency . number_format($number, 2, ",", ".");
    }
}
