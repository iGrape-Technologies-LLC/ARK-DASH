<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\RegisterController;
use App\User;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use App\Utils\UtilGeneral;

class StaffController extends Controller {
	protected $UtilGeneral;

	public function __construct(UtilGeneral $UtilGeneral) {
		$this->middleware('permission:staff.list|staff.create|staff.edit|staff.delete', ['only' => ['index']]);
	    $this->middleware('permission:staff.create', ['only' => ['create', 'store']]); 
	    $this->middleware('permission:staff.edit', ['only' => ['edit', 'update']]); 
	    $this->middleware('permission:staff.delete', ['only' => ['destroy']]);

			$this->UtilGeneral = $UtilGeneral;
	}

	private function validateInput(Request $request) {
		$request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'numeric', 'min:1'],
            'postal_code' => ['nullable', 'string', 'max:100']
        ]);
	}

	public function profile(Request $request) {
		if($request->isMethod('post') && $request->ajax()) {
			$this->validateInput($request);

			$user = Auth::user();

	      if($request->input('password') != null && $request->input('password_confirmation') != null) {
	      	$request->validate([
	            'password' => ['string', 'min:5', 'confirmed'],
	        ]);

	        $user->password = Hash::make($request->input('password'));
	      }

	      if($request->input('photo') != null) {
	      	$request->validate([
	      		'photo' => ['string', 'max:1000']
	      	]);

	      	$user->photo = $request->input('photo');
	      }

			$user->name = $request->input('name');
			$user->lastname = $request->input('lastname');
			$user->email = $request->input('email');
			$user->phone = $request->input('phone');
			$user->address = $request->input('address');
			$user->city_id = $request->input('city');
			$user->postal_code = $request->input('postal_code');
			$user->save();

			$request->session()->flash('success', __('users.profile_updated'));

			return ['success' => true];
		} else {
			$cities = City::orderBy('name')->get();

			return view('admin.staff.profile', compact('cities'));
		}
	}

	public function index() {

		if(request()->ajax()) {
      	$datas = User::where('id', '!=' , auth()->user()->id)->where('is_staff', 1)->whereHas('roles', function($q){$q->where('name', '!=', 'SuperAdmin');})->orderBy('name','ASC')->get();


      return DataTables::of($datas)
          ->addColumn(
              'action',
               function ($row)  {
                   $html ="";     

                  if (auth()->user()->can("staff.edit")) {
                          $html .= '<a class="btn btn-sm btn-primary" href="' . route('admin.updateuser', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                  }

                  if (auth()->user()->can("staff.delete") && auth()->user()->id != $row->id) {
                          $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . route('admin.deleteuser', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                  }
                  
                  return $html;
               }                   
          )         
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
          ->editColumn('email', function($row){
          	return '<a href="mailto:'.$row->email.'">'.$row->email.'</a>';
          })
          ->rawColumns(['name','email', 'roles', 'action'])
          ->make(true);
    }

		return view('admin.staff.list');
	}

	public function create(Request $request) {
		$cities = City::all();
		$roles = Role::where('is_default', '!=' , 1)->where('is_staff', 1)->pluck('name','name');

		return view('admin.staff.form', compact('cities', 'roles'));
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

		        $usr->is_staff = 1;

				$usr->email_verified_at = date('Y-m-d');
				$usr->approved_at = date('Y-m-d');
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

	public function edit(Request $request, $id) {
		$user = User::find($id);

		if($user != null) {
			$cities = City::all();

			$roles = Role::where('is_default', '!=' , 1)->where('is_staff', 1)->pluck('name','name');        
        	$userRole = $user->roles->pluck('name','name')->all();

			return view('admin.staff.form', compact('user', 'cities','roles','userRole'));
		} else {
			abort(404);
		}
	}

	public function update(Request $request, $id) {
		if($request->ajax()) {
			$user = User::find($id);

			if($user == null) {
				abort(404);
			}

			$this->validateInput($request);

			$user->update([
				'name' => $request->input('name'),
				'lastname' => $request->input('lastname'),
				'email' => $request->input('email'),
				'phone' => $request->input('phone'),
				'postal_code' => $request->input('postal_code'),
				'city_id' => $request->input('city'),
				'address' => $request->input('address')
			]);

			if(!empty($request->input('password'))){
				$user->update([
				'password' => Hash::make($request->input('password'))
				]);
			}

			DB::table('model_has_roles')->where('model_id',$id)->delete();
        	$user->assignRole($request->input('roles'));

			$request->session()->flash('success', __('users.update_msg'));

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

	public function apiIndex()
	{
		if (!auth()->user()->can('api.list')) {
			abort(403, 'Unauthorized action.');
		}

		if(request()->ajax()) {
			$datas = User::where('is_staff', 1)->whereHas('roles', function($q){$q->where('name', '!=', 'SuperAdmin');})->orderBy('name','ASC')->get();


		return DataTables::of($datas)
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
				->editColumn(
					'active', function($row) {
							 return $this->UtilGeneral->changeStatus($row->id, $row->api, 'StaffController');
					}
				)
				->editColumn('email', function($row){
					return '<a href="mailto:'.$row->email.'">'.$row->email.'</a>';
				})
				->rawColumns(['name','email', 'roles', 'active'])
				->make(true);
	}

	return view('admin.staff.api.index');

	}

	public function changeStatus($id)
	{
		if (request()->ajax()) {
			try {
					$user = User::find($id);

					if(empty($user)){
							abort(404, 'Not found');
					}

					$user->api = !$user->api;
					$user->api_token = null;
					$user->update();
					$output = ['success' => true, 'msg' => __("general.change_status_ok")];

			} catch (\Exception $e) {
					\Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

					$output = ['success' => false, 'msg' => __("general.something_went_wrong")];
			}

			return $output;
		}
	}
}
