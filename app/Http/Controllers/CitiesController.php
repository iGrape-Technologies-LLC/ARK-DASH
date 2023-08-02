<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CitiesController extends Controller
{
	private $citiesRepository;

	public function __construct(City $repository) {
		$this->citiesRepository = $repository;

		$this->middleware('permission:city.list|city.create|city.edit|city.delete', ['only' => ['index']]);
        $this->middleware('permission:city.view', ['only' => ['show']]);   
        $this->middleware('permission:city.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:city.edit', ['only' => ['update', 'edit']]);
        $this->middleware('permission:city.delete', ['only' => ['destroy']]);
	}

    public function index() {
    	if(request()->ajax()) {
    		$cities = $this->citiesRepository->orderBy('name')->get();

    		return DataTables::of($cities)
    			->addColumn('action',function ($row)  {
                 	$html ="";     

                    if (auth()->user()->can("city.edit")) {
                        $html .= '<a class="btn btn-sm btn-primary" href="' . route('admin.editcity', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                    }

                     if (auth()->user()->can("city.delete")) {
                        $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . route('admin.deletecity', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                    }
                    
                    return $html;
                 })
    			->editColumn('state', function($row) {
					return $row->state->name;
    			})
    			->rawColumns(['name', 'state', 'action'])
    			->make(true);
    	}

    	return view('admin.cities.index');
    }

    public function create() {
    	$states = State::orderBy('name')->get();

    	return view('admin.cities.create', compact('states'));
    }

    public function store(Request $request) {
    	if($request->ajax()) {
    		$request->validate([
    			'name' => ['required', 'string', 'max:255'],
    			'state' => ['required', 'numeric', 'exists:states,id']
    		]);

    		$created = $this->citiesRepository->create([
    			'name' => $request->input('name'),
    			'state_id' => $request->input('state')
    		]);

    		if($created != null) {
    			$request->session()->flash('success', __('cities.store_msg'));

				return ['success' => true];
    		} else {
				return ['success' => false];
    		}
    	} else {
    		abort(404);
    	}
    }

    public function edit($id) {
    	$city = $this->citiesRepository->find($id);

    	if($city != null) {
    		$states = State::orderBy('name')->get();

    		return view('admin.cities.edit', compact('city', 'states'));
    	} else {
    		abort(404);
    	}
    }

    public function update(Request $request, $id) {
    	if($request->ajax()) {
    		$city = $this->citiesRepository->find($id);

    		if($city == null) {
    			abort(404);
    		}

    		$request->validate([
    			'name' => ['required', 'string', 'max:255'],
    			'state' => ['required', 'numeric', 'exists:states,id']
    		]);

    		$updated = $city->update([
    			'name' => $request->input('name'),
    			'state_id' => $request->input('state')
    		]);

    		if($updated) {
    			$request->session()->flash('success', __('cities.update_msg'));

				return ['success' => true];
    		} else {
    			return ['success' => false];
    		}
    	} else {
    		abort(404);
    	}
    }

    public function destroy($id) {
    	$city = City::find($id);

    	if($city != null) {
    		$users = User::where('city_id', $id)->get();

    		if(count($users) > 0) {
    			return ['success' => false, 'msg' => __('cities.city_selected')];
    		} else {
    			$city->delete();

				return ['success' => true, 'msg'=> __('cities.destroy_msg')];
    		}
    	} else {
    		abort(404);
    	}
    }
}
