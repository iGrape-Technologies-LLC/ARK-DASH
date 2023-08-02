<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Checkout\Entities\Cart;
use App\Mail\NewUserRegisterToAdmin;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/admin/home';

    private $is_backoffice_registration = false;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct($is_backoffice_registration = false)
    {
        if(!$is_backoffice_registration) {
            $this->middleware('guest');
        }

        $this->is_backoffice_registration = $is_backoffice_registration;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:5', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    public function create(array $data)
    {
        $role = Role::where('name', 'Cliente')->first();

        $cartRepository = new Cart();
        $cart = $cartRepository->userCurrentCart(null);

        DB::beginTransaction();

        $user = User::create([
            'name' => $data['name'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->roles()->attach($role->id, ['model_type' => 'App\User']);

        DB::commit();

        if(!env('APP_DEBUG')){
            $admins = User::whereHas('roles', function($query) {
                $query->where('name', env('ROLE_TO_SEND_EMAIL'));
            })->get();

            foreach ($admins as $recipient) {            
                $mail = Mail::to($recipient->email);

                try {
                    $mail->send(new NewUserRegisterToAdmin($user));
                } catch(\Exception $e) {
                    \Log::error($e);
                }
            }            
        }        

        if(!$this->is_backoffice_registration) {
            Auth::login($user);
        }
        
        if($cart != null) {
            $cart->update([
                'user_id' => auth()->user()->id,
                'session_id' => null
            ]);
        }

        return $user;
    }

    public function redirectTo() {
        if(auth()->user()->is_staff == 0) {
            $this->redirectTo = route('front.profile');
        }

        return $this->redirectTo;
    }
}
