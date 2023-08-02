<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\Article;
use App\Models\Category;
use App\Models\Newsletter;
use App\Models\Advertisement;
use App\Models\Notification;
use App\Models\Whatsapp;
use App\User;
use Illuminate\Support\Facades\View;
use Jenssegers\Date\Date;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Carbon::setLocale(config('app.locale'));
        setlocale(LC_TIME, config('app.locale'));
        Date::setLocale(config('app.locale'));

        $asset_v = config('constants.asset_version', 1);
        View::share('asset_v', $asset_v);

        view()->composer(['front.*'], function ($view) {
            $whatsapps = Whatsapp::where('hour_from', '<=', date('H:i'))->where('hour_to', '>=', date('H:i'))->get();
            $categories = Category::all();            
            $view->with('categories',$categories);
            $view->with('whatsapps',$whatsapps);
        });        

    

        view()->composer(['admin.*'], function ($view) {
            $components = [];
            if(!empty(auth()->user())){
                $notifications = Notification::where('read', false)
                ->where('user_id', auth()->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();        

                $numbers = array();
                $numbers['articles'] = Article::count();
                $numbers['users'] = User::where('id', '!=' , auth()->user()->id)->whereHas('roles', function($q){$q->where('name', '!=', 'SuperAdmin');})->orderBy('name','ASC')->count();
                $numbers['newsletters'] = Newsletter::count();
                $numbers['advertisements'] = Advertisement::count();

                $components['numbers'] = $numbers;

                $components['notifications'] = $notifications;

                $view->with($components);
            } else{
                $view->with($components);
            }   
        });
    }
}
