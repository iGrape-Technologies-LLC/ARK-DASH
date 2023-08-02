<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;

class StatusesController extends Controller
{
	private $repository;

	public function __construct(Status $repository) {
		$this->repository = $repository;
		$this->middleware('permission:status.list|status.create|status.edit|status.delete', ['only' => ['index']]);
	    $this->middleware('permission:status.create', ['only' => ['create', 'store']]);
	    $this->middleware('permission:status.edit', ['only' => ['edit', 'update']]);
	    $this->middleware('permission:status.delete', ['only' => ['destroy']]);
	}

	public function index() {
		if(request()->ajax()) {
      	$datas = $this->repository
					->orderBy('priority')
					->orderBy('name')
					->get();

        return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                         $html ="";     

                        if (auth()->user()->can("status.edit")) {
                                $html .= '<a class="btn btn-sm btn-primary" href="' . route('admin.updatestatus', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                        }

                         if (auth()->user()->can("status.delete")) {
                                $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . route('admin.deletestatus', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                        }
                        
                        return $html;
                     }                   
                )
                ->addColumn('mass_delete', function ($row) {
                    return  '<input type="checkbox" class="row-select" value="' . $row->id .'">' ;
                })             
                ->editColumn('color', function($row){
                	return '<span class="badge" style="border-color: '.$row->color.'; color: '.$row->color.';">'.$row->color.'</span>';
                }) 
                ->rawColumns(['name', 'color', 'priority', 'action', 'mass_delete'])
                ->make(true);                   

    }

		return view('admin.statuses.list');
	}

	public function create(Request $request) {
		
		return view('admin.statuses.form');
	}

	public function store(Request $request) {
		if($request->ajax()) {
			$request->validate([
				'name' => ['required', "max:255"]
			]);

			$status = $this->repository->create([
				'name' => $request->input('name'),
				'color' => $request->input('color'),
				'priority' => $request->input('priority')
			]);
			$status->save();

			$request->session()->flash('success', __('statuses.store_msg'));

			return ['success' => true];
		} else {
			abort(404);
		}
	}

	public function edit(Request $request, $id) {
		$status = $this->repository->find($id);
		

		if($status != null) {
			return view('admin.statuses.form', [
				'status' => $status
			]);
		} else {
			abort(404);
		}
	}

	public function update(Request $request, $id) {
		if($request->ajax()) {
			$status = $this->repository->find($id);

			$request->validate([
				'name' => ['required', "max:255"]
			]);

			$status->update([
				'name' => $request->input('name'),
				'color' => $request->input('color'),
				'priority' => $request->input('priority')
			]);

			$request->session()->flash('success', __('statuses.update_msg'));

			return ['success' => true];
		} else {
			abort(404);
		}
	}

	public function destroy($id) {
        if (request()->ajax()) {
            try {     
                $status = $this->repository->find($id);
				$status->delete();

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
        if (!auth()->user()->can('status.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {            

            if (!empty($request->input('selected_rows'))) {                

                $selected_rows = explode(',', $request->input('selected_rows'));

                $statuses = Status::whereIn('id', $selected_rows)                                    
                                    ->get();
                $deletable_products = [];

                DB::beginTransaction();

                foreach ($statuses as $status) {
                    $status->delete();                    
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
