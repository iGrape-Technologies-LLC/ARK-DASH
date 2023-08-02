<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\Notification;

class ContactController extends Controller
{
	public function contact(Request $request) {
		//Busca solo los usuarios de un rol
        $users = User::whereHas('roles', function($query) {
                    $query->where('name', env('ROLE_TO_SEND_EMAIL'));
                })->get();

       	if(request()->ajax()) {

            $request->validate([
                    'name' => ['required', 'string', 'max:200'],
                    'email' => ['required', 'email', 'max:500'],
                    'subject' => ['required', 'string', 'max:100'],
                    'message' => ['required', 'string', 'max:1000']
            ]);

            $output = ['success' => true, 'msg' => __("custom.all_went_right")];
            // enviar emails solo si la configuracion de emails esta realizada
            if(env('MAIL_USERNAME') != null) {
                try{
                    foreach($users as $user) {
                        if(!env('APP_DEBUG')){   
                            try {                         
                                $user->sendContactFormNotification($request->all());
                            } catch(\Exception $e) {
                                \Log::error($e);
                            }
                        }

                        $notification = $request->all();
                        $notification['user_id'] = $user->id;
                        Notification::create($notification);
                    }                    
                }catch (\Exception $e) {
                    $output = ['success' => false, 'msg' => __("custom.something_went_wrong")];
                }
            }
            return $output;
        } else if($request->isMethod('post')) {  
                $request->validate([
                        'name' => ['required', 'string', 'max:200'],
                        'email' => ['required', 'email', 'max:500'],
                        'subject' => ['required', 'string', 'max:100'],
                        'message' => ['required', 'string', 'max:1000']
                ]);             
                // enviar emails solo si la configuracion de emails esta realizada
                if(env('MAIL_USERNAME') != null) {
                    foreach($users as $user) {

                        $user->sendContactFormNotification($request->all());

                        $notification = $request->all();
                        $notification['user_id'] = $user->id;
                		Notification::create($notification);
                    }
                }
                return redirect()->route('front.contact')->with('success-msg', 'Mensaje enviado con éxito');
        } else{
            return view('front.contact');
        }

		/*if($request->isMethod('post')) {
			$request->validate([
				'name' => ['required', 'max:100'],
				'email' => ['required', 'max:500', 'email'],
				'subject' => ['required', 'max:100'],
				'message' => ['required', 'max: 1000']
			]);

			if($users != null && count($users) > 0) {
				foreach($users as $user) {
					$user->sendPageContactNotification($request->input("name"),
						$request->input('email'), $request->input('subject'), 
						$request->input('message'));
				}
			}
		}

		return redirect()->route('front.contact');*/
	}
}
