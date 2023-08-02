<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\RegisterController;
use App\User;
use App\Models\City;
use App\Models\AfipType;
use App\Models\UserFavorites;
use App\Models\Transaction;
use App\Models\Subsidiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Modules\Checkout\Entities\PaymentMethod;
use Modules\Shipping\Entities\ShippingMethod;
use Modules\Checkout\Entities\Cart;

class CustomerController extends Controller 
{
	private $cartRepository;

	public function __construct(Cart $cartRepo) {
		$this->middleware('permission:customer.list|customer.create|customer.edit|customer.delete', ['only' => ['index']]);
    $this->middleware('permission:customer.create', ['only' => ['create', 'store']]); 
    $this->middleware('permission:customer.edit', ['only' => ['edit', 'update']]); 
    $this->middleware('permission:customer.delete', ['only' => ['destroy']]);

    $this->cartRepository = $cartRepo;
	}
	

	private function validateInput(Request $request) {
		$request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'alternative_email' => ['nullable', 'string', 'emial', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'numeric', 'min:1'],
            'postal_code' => ['nullable', 'string', 'max:100'],
            'doc_number'=>['nullable', 'string', 'max:255']
        ]);
	}


	public function index() {

		if(request()->ajax()) {
      	$datas = User::where('id', '!=' , auth()->user()->id)->where('is_staff', 0)->whereHas('roles', function($q){$q->where('name', '!=', 'SuperAdmin');})->orderBy('name','ASC');


      return DataTables::of($datas)
          ->addColumn(
              'action',
               function ($row)  {
                   $html ="";     

                  if (auth()->user()->can("customer.edit")) {
                          $html .= '<a class="btn btn-sm btn-primary" href="' . route('admin.updatecustomer', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                  }

                  if (auth()->user()->can("customer.delete") && auth()->user()->id != $row->id) {
                          $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . route('admin.deletecustomer', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                  }
                  
                  return $html;
               }                   
          )
          ->filterColumn('roles', function($query, $value) {
          	$query->whereHas('roles', function($role) use($value) {
          		$role->where('name', 'like', '%' . $value . '%');
          	});
          })   
          ->addColumn(
                    'roles',
                    function ($row)  {
                        $html ="";
                        if(!empty($row->getRoleNames())){
                            foreach($row->getRoleNames() as $v){
                                $html .= '<label class="badge badge-success">'.$v.'</label>';
                            }
                        }
                        return $html;
                    }
                )
         	->filterColumn('email', function($query, $value) {
         		$query->where('email', 'like', '%' . $value . '%')
         					->orWhere('alternative_email', 'like', '%' . $value . '%');
         	})
          ->editColumn('email', function($row){
          	$html = '';

          	if($row->email != null) {
          		$html = '<a href="mailto:'.$row->email.'">'.$row->email.'</a>';
          	} elseif($row->alternative_email != null) {
          		$html = '<a href="mailto:'.$row->alternative_email.'">'.$row->alternative_email.'</a>';
          	}

          	return $html;
          })
          ->addColumn('wp', function($row){
          		$html = "";
	          	if($row->whatsapp() != null) {	          		
	          		$html = '<a href="'.$row->whatsapp().'" target="_blank">Enviar mensaje</a>';
	          	} else{	          		
	          		$html = "-";
	          	}
	          	return $html;	          	
          })	
          ->rawColumns(['name','email', 'roles', 'action', 'wp'])
          ->make(true);
    }

		return view('admin.customers.list');
	}

	public function create(Request $request) {
		$cities = City::all();
		$roles = Role::where('is_default', '!=' , 1)->where('is_staff', 0)->pluck('name','name');

		return view('admin.customers.form', compact('cities', 'roles'));
	}

	public function store(Request $request) {
		if($request->ajax()) {
			
			$validator = Validator::make($request->all(), [
	            'name' => ['required', 'string', 'max:255'],
	            'lastname' => ['required', 'string', 'max:255'],
	            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
	            'password' => ['required', 'string', 'min:5', 'confirmed'],
	        ]);			

			if(!$validator->fails()) {
				DB::beginTransaction();
				
				$usr = User::create([
		            'name' =>$request->input('name'),
		            'lastname' =>$request->input('lastname'),
		            'email' => $request->input('email'),
		            'password' => Hash::make($request->input('password')),
		        ]);

		        $usr->is_staff = 0;

				$usr->email_verified_at = date('Y-m-d');
				$usr->phone = $request->input('phone');
				$usr->postal_code = $request->input('postal_code');
				$usr->city_id = $request->input('city');
				$usr->address = $request->input('address');
				$usr->assignRole($request->input('roles'));
				$usr->save();
				DB::commit();

				
				$request->session()->flash('success', __('users.store_msg'));

				return ['success' => true];
			} else {
				return ['success' => false, 'errors' => $validator->errors()];
			}
		} else {
			abort(404);
		}
	}

	public function ingresar(Request $request) {
		if(auth()->user() != null) {
			if(auth()->user()->is_staff) {
				return redirect()->route('admin.staff.profile');
			} else {
				return redirect()->route('front.profile');
			}
		}

		return view('front.pages.auth.login');
	}

	public function profile(Request $request){
		$user = auth()->user();
		$cities = City::orderBy('name')->get();
		$favorites = UserFavorites::where('user_id', $user->id)->whereHas('article', function($articles) {
			$articles->where('active', true);
		})->get();

		$orders = Transaction::where('type', 'sell')->where('user_id', $user->id)->with('payments')->with('shippings')->orderBy('created_at', 'desc')->get();		

		return view('front.pages.profile', compact('user', 'cities', 'favorites', 'orders'));
	}

	public function checkout(Request $request){
		$user = auth()->user();
		$cities = City::orderBy('name')->get();
		$afip_types = AfipType::get();
		
		$payment_methods = PaymentMethod::where('active', true)->orderBy('name')->get();
		$shipping_methods = ShippingMethod::where('active', true)->orderBy('name')->get();
		$subsidiaries = Subsidiary::orderBy('name')->get();

		$cart = $this->cartRepository->userCurrentCart($user);
    request()->session()->put('cart', $cart);

    if($cart == null) {
    	$request->session()->flash('notificationmsg', __('checkout::process.no_articles'));
    	return redirect()->route('front.articleslist');
    }

    if(count($cart->article_properties) == 0) {
    	$request->session()->flash('notificationmsg', __('checkout::process.no_articles'));
    	return redirect()->route('front.articleslist');
    }

		return view('front.pages.checkout', compact('user', 'payment_methods', 'shipping_methods', 'cities', 'afip_types', 'subsidiaries'));
	}

	public function cart(Request $request){
		$user = auth()->user();
		return view('front.pages.cart', compact('user'));
	}

	

	public function edit(Request $request, $id) {
		$user = User::find($id);

		if($user != null) {
			$cities = City::all();

			$roles = Role::where('is_default', '!=' , 1)->where('is_staff', 0)->pluck('name','name');        
        	$userRole = $user->roles->pluck('name','name')->all();

			return view('admin.customers.form', compact('user', 'cities','roles','userRole'));
		} else {
			abort(404);
		}
	}

	public function updatePassword(Request $request) {
		if($request->ajax()) {
			$user = User::find(auth()->user()->id);

			if($user == null) {
				abort(404);
			}

			$validator = Validator::make($request->all(), [
	            'password' => ['required', 'string', 'min:5', 'confirmed']
	        ]);		

			$user->update([
				'password' => Hash::make($request->input('password')),			
			]);

			Auth::setUser($user);

			//$request->session()->flash('success', __('users.update_msg'));

			return ['success' => true];
		} else {
			abort(404);
		}
	}

	public function updateProfileCheckout(Request $request) {
		if($request->ajax()) {
			$user = auth()->user();

			// si no esta habilitado el express checkout y no esta logueado no deberia poder entrar a esta URL
			if($user == null && !config('config.EXPRESS_CHECKOUT')) {
				abort(404);
			}

			$request->validate([
          'name' => ['required', 'string', 'max:255'],
          'lastname' => ['required', 'string', 'max:255'],	           
          'phone' => ['required', 'string', 'max:255'],
          'address' => ['nullable', 'string', 'max:255'],
          'city' => ['nullable', 'numeric', 'min:1'],
          'postal_code' => ['nullable', 'string', 'max:100'],
          'doc_number'=>['required', 'string', 'max:255'],
          'alternative_email' => ['nullable', 'string', 'email', 'max:255']
    	]);

			// crea el usuario en el checkout en caso de que este habilitado el express checkout
			if($user == null && config('config.EXPRESS_CHECKOUT')) {
				DB::beginTransaction();

				$role = Role::where('name', 'Cliente')->first();

				$user = User::create([
					'name' => $request->input('name'),
					'lastname' => $request->input('lastname'),
					'phone' => $request->input('phone'),
					'doc_number' => $request->input('doc_number'),
					'alternative_email' => $request->input('alternative_email')
				]);

				$user->roles()->attach($role->id, ['model_type' => 'App\User']);

				DB::commit();

				// obtengo el carro guardado en sesion antes de hacer login (despues cambia el session_id)
				$cart = $this->cartRepository->userCurrentCart(null);

				Auth::login($user);

				if($cart != null) {
					$cart->update([
						'user_id' => auth()->user()->id,
						'session_id' => null
					]);
				}

				return ['success' => true];
			}

			$user->update([
				'name' => $request->input('name'),
				'lastname' => $request->input('lastname'),				
				'phone' => $request->input('phone'),		
				'doc_number' => $request->input('doc_number'),				
			]);

			Auth::setUser($user);			

			return ['success' => true];
		} else {
			abort(404);
		}
	}

	public function updateProfile(Request $request) {
		if($request->ajax()) {
			$user = User::find(auth()->user()->id);

			if($user == null) {
				abort(404);
			}

			$this->validateInput($request);

			$user->update([
				'name' => $request->input('name'),
				'lastname' => $request->input('lastname'),
				'email' => $request->input('email'),
				'phone' => $request->input('phone'),		
				'doc_number' => $request->input('doc_number'),				
			]);			

			Auth::setUser($user);
			

			return ['success' => true];
		} else {
			abort(404);
		}
	}

	public function destroy($id) {
        if (request()->ajax()) {
            try {     
                $user = User::find($id);
                
               if($user != null) {
                    $user->delete();
                    $output = ['success' => true, 'msg' => __("general.delete_ok")];
                } else {
                    $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
                }                        
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
            }
            return $output;            
        }
	}

	public function validateEmail() {
		$foundedUsers = User::where('email', request()->input('email'))->get();

		if(count($foundedUsers) > 0) {
			return ['success' => true, 'user_exists' => true];
		} else {
			return ['success' => true, 'user_exists' => false];
		}
	}

	
}
