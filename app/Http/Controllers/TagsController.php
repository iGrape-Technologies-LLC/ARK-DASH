<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;

class TagsController extends Controller
{
	private $repository;

	public function __construct(Tag $repository) {
		$this->repository = $repository;
		$this->middleware('permission:tag.list|tag.create|tag.edit|tag.delete', ['only' => ['index']]);
	    $this->middleware('permission:tag.create', ['only' => ['create', 'store']]);
	    $this->middleware('permission:tag.edit', ['only' => ['edit', 'update']]);
	    $this->middleware('permission:tag.delete', ['only' => ['destroy']]);
	}

	public function index() {
		if(request()->ajax()) {
      	$datas = $this->repository					
					->orderBy('name')
					->get();

        return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                         $html ="";     

                        if (auth()->user()->can("tag.edit")) {
                                $html .= '<a class="btn btn-sm btn-primary" href="' . route('admin.updatetag', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                        }

                         if (auth()->user()->can("tag.delete")) {
                                $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . route('admin.deletetag', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
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
                ->rawColumns(['name', 'color', 'action', 'mass_delete'])
                ->make(true);                   

    }

		return view('admin.tags.list');
	}

	public function create(Request $request) {
		
		return view('admin.tags.form');
	}

	public function store(Request $request) {
		if($request->ajax()) {
			$request->validate([
				'name' => ['required', "max:255"]
			]);

			$tag = $this->repository->create([
				'name' => $request->input('name'),
				'color' => $request->input('color')
			]);
			$tag->save();

			$request->session()->flash('success', __('tags.store_msg'));

			return ['success' => true];
		} else {
			abort(404);
		}
	}

	public function edit(Request $request, $id) {
		$tag = $this->repository->find($id);
		

		if($tag != null) {
			return view('admin.tags.form', [
				'tag' => $tag
			]);
		} else {
			abort(404);
		}
	}

	public function update(Request $request, $id) {
		if($request->ajax()) {
			$tag = $this->repository->find($id);

			$request->validate([
				'name' => ['required', "max:255"]
			]);

			$tag->update([
				'name' => $request->input('name'),
				'color' => $request->input('color')
			]);

			$request->session()->flash('success', __('tags.update_msg'));

			return ['success' => true];
		} else {
			abort(404);
		}
	}

	public function destroy($id) {
        if (request()->ajax()) {
            try {     
                $tag = $this->repository->find($id);
				$tag->delete();

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
        if (!auth()->user()->can('tag.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {            

            if (!empty($request->input('selected_rows'))) {                

                $selected_rows = explode(',', $request->input('selected_rows'));

                $tags = Tag::whereIn('id', $selected_rows)                                    
                                    ->get();
                $deletable_products = [];

                DB::beginTransaction();

                foreach ($tags as $tag) {
                    $tag->delete();                    
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
