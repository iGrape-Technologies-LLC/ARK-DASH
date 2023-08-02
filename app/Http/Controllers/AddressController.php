<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Address;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Hash;



class AddressController extends Controller
{
    public function __construct()
    {  
    }

    private function validateInput(Request $request) {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'street_number' => ['required', 'integer'],
            'floor' => ['nullable','string', 'max:50'],
            'apartment' => ['nullable','string', 'max:50'],
            'city_id' => ['nullable', 'numeric', 'min:1'],
            'postal_code' => ['nullable', 'string', 'max:100'],
        ]);
    }

    public function list(){
        //$addresses = Address::where('user_id', auth()->user()->id);

        return view('front.transversal.partials.profile-addresses');
    }

    public function listCheckout(){
        //$addresses = Address::where('user_id', auth()->user()->id);

        return view('checkout::partials.checkout-addresses');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       if($request->ajax()) {
            $this->validateInput($request);     
            
            $user_id = auth()->user()->id;

            $address = Address::create([
                'name' => $request->input('name'),
                'street' => $request->input('street'),
                'street_number' => $request->input('street_number'),
                'floor' => $request->input('floor'),
                'apartment' => $request->input('apartment'),
                'city_id' => $request->input('city_id'),
                'postal_code' => $request->input('postal_code'),
                
                'user_id' => $user_id
            ]);        
            
            //$request->session()->flash('success', __('users.update_msg'));

            return ['success' => true, 'id' => $address->id];
        } else {
            abort(404);
        }
    }

    
   
}