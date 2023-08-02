<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;
use DB;
use App\Notification;
use App\Utils\UtilGeneral;


class SettingsController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $UtilGeneral;

    public function __construct(UtilGeneral $UtilGeneral)
    {
        $this->UtilGeneral = $UtilGeneral;        
    }
 
    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $default_values = [
            'APP_NAME' => config('config.APP_NAME'),
            'APP_TITLE' => config('config.APP_TITLE'),
            'APP_LOCALE' => env('APP_LOCALE'),            
            'MAIL_HOST' => env('MAIL_HOST'),
            'MAIL_PORT' => env('MAIL_PORT'),
            'MAIL_USERNAME' => env('MAIL_USERNAME'),
            'MAIL_PASSWORD' => env('MAIL_PASSWORD'),
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
            'ANALYTICS_VIEW_ID' => env('ANALYTICS_VIEW_ID'),
            'OAUTH2_CLIENT_ID' => env('OAUTH2_CLIENT_ID'),
            'ROLE_TO_SEND_EMAIL' => env('ROLE_TO_SEND_EMAIL'),                
            'SENTRY_LARAVEL_DSN' => env('SENTRY_LARAVEL_DSN'),            
            'EMAIL_URL' => env('EMAIL_URL'),
            'BUSINESS_WEB' => env('BUSINESS_WEB'),            
            'BUSINESS_EMAIL' => env('BUSINESS_EMAIL'),
            'BUSINESS_PHONE' => env('BUSINESS_PHONE'),
            'SHOW_PRICING' => config('config.SHOW_PRICING'),                
            'ADMIN_TERMS_ACCEPT'=> config('config.ADMIN_TERMS_ACCEPT'),
            'IS_ECOMMERCE'=> config('config.IS_ECOMMERCE'),
            'PHOTO_REQUIRED'=> config('config.PHOTO_REQUIRED'),
            'SHOW_ATRIBUTES_WITHOUT_ARTICLES'=> config('config.SHOW_ATRIBUTES_WITHOUT_ARTICLES'),
            //'MANAGE_STOCK'=> config('config.MANAGE_STOCK'),
            'ADD_CART_THEN_REDIRECT_CHECKOUT'=> config('config.ADD_CART_THEN_REDIRECT_CHECKOUT'),
            'SHIPPING_REQUIRED'=> config('config.SHIPPING_REQUIRED'),
            
        ];

        return view('admin.settings.edit')->with(compact('default_values'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        } 

        try {
            

            $env_settings =  $request->only(['APP_NAME', 'APP_TITLE'
                , 'MAIL_HOST', 'MAIL_PORT', 'MAIL_FROM_NAME',
                'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_ENCRYPTION',
                'MAIL_FROM_ADDRESS', 'ANALYTICS_VIEW_ID', 'OAUTH2_CLIENT_ID',
                'ROLE_TO_SEND_EMAIL',
                'SENTRY_LARAVEL_DSN', 'SHOW_PRICING', 'SHIPPING_REQUIRED',
                'EMAIL_URL', 'BUSINESS_WEB','BUSINESS_EMAIL','BUSINESS_PHONE',
                'ADMIN_TERMS_ACCEPT', 'IS_ECOMMERCE', 'PHOTO_REQUIRED', 'SHOW_ATRIBUTES_WITHOUT_ARTICLES', 'ADD_CART_THEN_REDIRECT_CHECKOUT'
            ]);            

            $env_settings['SHOW_PRICING'] = $env_settings['SHOW_PRICING'] ? "true" : "false"; 

            //$env_settings['MANAGE_STOCK'] = $env_settings['MANAGE_STOCK'] ? "true" : "false";   

            $env_settings['SHIPPING_REQUIRED'] = $env_settings['SHIPPING_REQUIRED'] ? "true" : "false";             
            

            $env_settings['ADMIN_TERMS_ACCEPT'] = $env_settings['ADMIN_TERMS_ACCEPT'] ? "true" : "false"; 

            $env_settings['IS_ECOMMERCE'] = $env_settings['IS_ECOMMERCE'] ? "true" : "false"; 

            $env_settings['PHOTO_REQUIRED'] = $env_settings['PHOTO_REQUIRED'] ? "true" : "false"; 

            $env_settings['SHOW_ATRIBUTES_WITHOUT_ARTICLES'] = $env_settings['SHOW_ATRIBUTES_WITHOUT_ARTICLES'] ? "true" : "false"; 

            $env_settings['ADD_CART_THEN_REDIRECT_CHECKOUT'] = $env_settings['ADD_CART_THEN_REDIRECT_CHECKOUT'] ? "true" : "false";       
                

            $found_envs = [];
            $env_path = base_path('.env');
            $env_lines = file($env_path);
            foreach ($env_settings as $index => $value) {
                foreach ($env_lines as $key => $line) {
                    //Check if present then replace it.
                    if (strpos($line, $index) !== false) {
                        $env_lines[$key] = $index . '="' . $value . '"' . PHP_EOL;

                        $found_envs[] = $index;
                    }
                }
            }

            //Add the missing env settings
            $missing_envs = array_diff(array_keys($env_settings), $found_envs);
            if (!empty($missing_envs)) {
                $missing_envs = array_values($missing_envs);
                foreach ($missing_envs as $k => $key) {
                    if ($k == 0) {
                        $env_lines[] = PHP_EOL . $key . '="' . $env_settings[$key] . '"' . PHP_EOL;
                    } else {
                        $env_lines[] = $key . '="' . $env_settings[$key] . '"' . PHP_EOL;
                    }
                }
            }

            $env_content = implode('', $env_lines);

            if (is_writable($env_path) && file_put_contents($env_path, $env_content)) {
                return redirect()->route('admin.settings.edit')->with('success', __('settings.success'));
            } else {
                return redirect()->route('admin.settings.edit')->with('error', __('settings.error'));
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            return redirect()->route('admin.settings.edit')->with('error', __('messages.something_went_wrong'));

        }
    }
}
