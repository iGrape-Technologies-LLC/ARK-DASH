<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use \GuzzleHttp\Client;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Entities\Cart;
use Socialite;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        logout as performLogout;
    }

    private $cartRepository;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Cart $cartRepository)
    {
        $this->middleware('guest')->except('logout');
        $this->cartRepository = $cartRepository;
    }

    public function redirectTo() {
        $user = auth()->user();

        $cart = $this->cartRepository->userCurrentCart($user);
        if($cart != null) {
            session()->put('cart', $cart);
        }

        if(auth()->user()->is_staff == 0) {
            $this->redirectTo = route('front.articleslist');
        } else{
            $this->redirectTo = route('admin.home');
        }

        return $this->redirectTo;
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return Response
     */
    protected function login(Request $request)
    {
        try {
            $client = new Client();
    
            $response = $client->post(config('config.DNERO_ENDPOINT') . 'Account/login', [
                'json' => [
                    "email" => $request->email,
                    "countryCode" => "+1",
                    "phone" => config('config.DNERO_PHONE'),
                    "password" => $request->password
                ],
                'headers' =>
                [
                    'Content-Type' => "application/json",
                ]
            ]);
            
            $tokens = json_decode($response->getBody()->getContents(), true);
            $credentials = $request->only('email', 'password');
    
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                
                $user->token = $tokens['accessToken'];
                $user->refreshToken = $tokens['refreshToken'];

                $user->save();
                return ['success' => true, 'url' => $this->redirectTo()];
            } else{
                return redirect()->back()->withInput($request->only('email', 'remember'))->withErrors([
                    'password' => 'Email o contraseña incorrectos',
                ]);
            }
        } catch (\Throwable $th) {
            
            \Log::info($th);
            return redirect()->back()->withInput($request->only('email', 'remember'))->withErrors([
                'password' => 'Algo salio mal',
            ]);
        }
    }

    protected function logout(Request $request) {
        session()->forget('cart');

        if(auth()->user()->is_staff == 0) $this->redirectTo = '/ingresar';

        $this->performLogout($request);
        
        return redirect($this->redirectTo);
    }

    protected function authenticated(Request $request, $user)
    {
        activity()
           ->performedOn(app("App\\User"))
           ->causedBy($user)           
           ->log('Log in');
    }

    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from Facebook.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleFacebookCallback(Request $request)
    {
        if($request->query('error_code') != null) {
            return redirect()->route('login');
        }

        $user = Socialite::driver('facebook')->user();

        $loggedUser = User::where('email', $user->getEmail())->first();

        if($loggedUser == null) {
            DB::beginTransaction();

            $loggedUser = new User();
            $completeName = explode(' ', $user->getName());
            $loggedUser->name = $completeName[0];
            $loggedUser->lastname = $completeName[(count($completeName)-1)];
            $loggedUser->email = $user->getEmail();
            if(config('config.EMAIL_VERIFICATION')) {
                $loggedUser->email_verified_at = date('Y-m-d');
            }
            $loggedUser->password = '';
            $loggedUser->save();

            $role = Role::where('name', 'Cliente')->first();

            $loggedUser->roles()->attach($role->id);

            DB::commit();
        }

        Auth::login($loggedUser);

        // $user->token;
        return redirect($this->redirectTo());
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('Google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback(Request $request)
    {
        $user = Socialite::driver('Google')->user();

        $loggedUser = User::where('email', $user->getEmail())->first();

        if($loggedUser == null) {
            DB::beginTransaction();
            
            $loggedUser = new User();
            $completeName = explode(' ', $user->getName());
            $loggedUser->name = $completeName[0];
            $loggedUser->lastname = $completeName[(count($completeName)-1)];
            $loggedUser->email = $user->getEmail();
            if(config('config.EMAIL_VERIFICATION')) {
                $loggedUser->email_verified_at = date('Y-m-d');
            }
            $loggedUser->password = '';
            $loggedUser->save();

            $role = Role::where('name', 'Cliente')->first();

            $loggedUser->roles()->attach($role->id);

            DB::commit();
        }

        Auth::login($loggedUser);

        // $user->token;
        return redirect($this->redirectTo());
    }

    public function loginApi(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'email' => 'required|email',
			'password' => 'required|string|min:4'
		]);			

		if ($validator->fails()) {
			return response()->json(['error' =>  $validator->errors(), 'code' => 404], 404);
		}
		$user = User::where('email', $request->email)->where('is_staff', 1)->first();
		if ($user == null) {
			$error["email"] = "Usuario incorrecto.";
			return response()->json(['message' =>  $error, 'code' => 401], 401);
		}
		if (! Hash::check($request->password, $user->password)) {
			$error["password"] = "Contraseña incorrecta.";
			return response()->json(
                [
                    'message' => $error,
                    'code' => 401
                ], 401);
		}

        if (!$user->api) {
            $error["permissions"] = "El usuario no posee los permisos necesarios.";
			return response()->json(
                [
                    'message' => $error,
                    'code' => 401
                ], 401);
        }
		
		$user->api_token = Str::random(80);
		$user->update();
		
		return response()->json([
            'code' => 200, 
            'message' => 'Login correcto',
            'api_token' => $user->api_token
        ], 200);
	}
}
