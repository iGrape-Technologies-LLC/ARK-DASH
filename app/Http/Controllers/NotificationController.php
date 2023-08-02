<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\Notification;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\UtilGeneral;
use App\Utils\UtilFile;

class NotificationController extends Controller
{
    protected $UtilGeneral;
    protected $UtilFile;

    public function __construct(UtilGeneral $UtilGeneral, UtilFile $UtilFile)
    {
        $this->UtilGeneral = $UtilGeneral;  
        $this->UtilFile = $UtilFile;        

       
    }

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

            $output = ['success' => true, 'msg' => __("general.all_went_right")];
            // enviar emails solo si la configuracion de emails esta realizada
            if(env('MAIL_USERNAME') != null) {
                try{
                    foreach($users as $user) {
                        $user->sendContactFormNotification($request->all());

                        $notification = $request->all();
                        $notification['user_id'] = $user->id;
                        Notification::create($notification);
                    }                    
                }catch (\Exception $e) {
                    $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
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
                return redirect()->route('front.contact')->with('success-msg', __('notifications.sent_ok'));
        } else{
            return view('front.contact');
        }
    }

    public function read_notifications() {
    	Notification::where('read', 0)
    		->where('user_id', auth()->user()->id)
    		->update(['read' => 1]);

    	return 'OK';
    }

    public function index(Request $request)
    {
        if (!auth()->user()->can('notification.list')) {
            abort(403, 'Unauthorized action.');
        }

        if(request()->ajax()) {

            $datas = Notification::orderBy('created_at', 'desc')
                ->get();        

            return DataTables::of($datas)        
                ->editColumn('created_at', function($row){
                    return $this->UtilGeneral->format_date($row->created_at);
                })     
                ->rawColumns(['name', 'email', 'subject', 'message', 'created_at'])
                ->make(true);                        
        }

    

        return view('admin.notifications.index');            
    }
}
