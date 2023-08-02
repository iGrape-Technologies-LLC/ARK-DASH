<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Whatsapp;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;

class WhatsappsController extends Controller
{
	private $whatsappsRepository;

	public function __construct(Whatsapp $whatsapp) {
		$this->whatsappsRepository = $whatsapp;
		$this->middleware('permission:whatsapp.list|whatsapp.create|whatsapp.edit|whatsapp.delete', ['only' => ['index']]);
	    $this->middleware('permission:whatsapp.create', ['only' => ['create', 'store']]);
	    $this->middleware('permission:whatsapp.edit', ['only' => ['edit', 'update']]);
	    $this->middleware('permission:whatsapp.delete', ['only' => ['destroy']]);
	}

	public function index() {
		if(request()->ajax()) {
      	$datas = $this->whatsappsRepository					
					->orderBy('name')
					->get();

        return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                         $html ="";     

                        if (auth()->user()->can("whatsapp.edit")) {
                                $html .= '<a class="btn btn-sm btn-primary" href="' . route('admin.updatewhatsapp', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                        }

                         if (auth()->user()->can("whatsapp.delete")) {
                                $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . route('admin.deletewhatsapp', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                        }
                        
                        return $html;
                     }                   
                )
                ->addColumn('mass_delete', function ($row) {
                    return  '<input type="checkbox" class="row-select" value="' . $row->id .'">' ;
                })       
								->addColumn('business_hour', function($row){
									return $row->hour_from . ' a ' . $row->hour_to;
								})        
                ->rawColumns(['name', 'phone', 'action', 'mass_delete', 'business_hour'])
                ->make(true);                   

    }

		return view('admin.whatsapps.list');
	}

	public function create(Request $request) {
		$whatsapps = Whatsapp::orderBy('name')->get();

		return view('admin.whatsapps.form', compact('whatsapps'));
	}

	public function store(Request $request) {
		if($request->ajax()) {
			$request->validate([
				'name' => ['required', "max:255"],
				'phone' => ['required', 'max:255'],
				'hour_from' => ['required', 'date_format:H:i'],
				'hour_to' => ['required', 'date_format:H:i'],
			]);

			$whatsapp = $this->whatsappsRepository->create([
				'name' => $request->input('name'),
				'phone' => $request->input('phone'),
				'hour_from' => $request->input('hour_from'),
				'hour_to' => $request->input('hour_to')
			]);
			$whatsapp->save();

			$request->session()->flash('success', __('whatsapps.store_msg'));

			return ['success' => true];
		} else {
			abort(404);
		}
	}

	public function edit(Request $request, $id) {
		$whatsapp = $this->whatsappsRepository->find($id);
		$whatsapps = Whatsapp::orderBy('name')->get();

		if($whatsapp != null) {
			return view('admin.whatsapps.form', [
				'whatsapp' => $whatsapp,
				'whatsapps' => $whatsapps
			]);
		} else {
			abort(404);
		}
	}

	public function update(Request $request, $id) {
		if($request->ajax()) {
			$whatsapp = $this->whatsappsRepository->find($id);

			$request->validate([
				'name' => ['required', "max:255"],
				'phone' => ['required', 'max:255'],
				'hour_from' => ['required', 'date_format:H:i'],
				'hour_to' => ['required', 'date_format:H:i'],
			]);

			$whatsapp->update([
				'name' => $request->input('name'),
				'phone' => $request->input('phone'),
				'hour_from' => $request->input('hour_from'),
				'hour_to' => $request->input('hour_to')
			]);

			$request->session()->flash('success', __('whatsapps.update_msg'));

			return ['success' => true];
		} else {
			abort(404);
		}
	}

	public function destroy($id) {
        if (request()->ajax()) {
            try {     
                $whatsapp = $this->whatsappsRepository->find($id);
				$whatsapp->delete();

				$output = ['success' => true, 'msg' => __("general.delete_ok")];                                           
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
            }
            return $output;            
        }

    }


	/**
     * Mass deletes products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function massDestroy(Request $request)
    {
        if (!auth()->user()->can('whatsapp.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {            

            if (!empty($request->input('selected_rows'))) {                

                $selected_rows = explode(',', $request->input('selected_rows'));

                $whatsapps = Whatsapp::whereIn('id', $selected_rows)                                    
                                    ->get();
                                    
                $deletable_products = [];

                DB::beginTransaction();

                foreach ($whatsapps as $whatsapp) {
                    $whatsapp->delete();                    
                }

                DB::commit();
            }
            
            return $output = ['success' => 1,
                        'msg' => __('general.deleted_success')
                    ];
         
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            return $output = ['success' => 0,
                            'msg' => __("general.something_went_wrong")
                        ];
        }

        
    }
}
