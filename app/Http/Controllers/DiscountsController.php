<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Discount;
use App\Models\Article;
use Yajra\DataTables\Facades\DataTables;
use DB;

use App\Utils\UtilGeneral;

class DiscountsController extends Controller
{

    protected $UtilGeneral;

    public function __construct(UtilGeneral $UtilGeneral) {
        $this->UtilGeneral = $UtilGeneral; 

        $this->middleware('permission:discount.list|discount.create|discount.edit|discount.delete', ['only' => ['index']]); 
        $this->middleware('permission:discount.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:discount.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:discount.delete', ['only' => ['destroy']]);
    }

    public function index() {

        if(request()->ajax()) {
            $datas = Discount::all();

            return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                         $html ="";     

                        if (auth()->user()->can("discount.edit")) {
                                $html .= '<a class="btn btn-sm btn-primary" href="' . route('admin.updatediscount', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                        }

                         if (auth()->user()->can("discount.delete")) {
                                $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . route('admin.deletediscount', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                        }
                        
                        return $html;
                     }                   
                )
                ->editColumn('type', function($row){
                    return __('discounts.'.$row->type);
                })
                ->editColumn(
                    'active', function($row) {
                         return $this->UtilGeneral->changeStatus($row->id, $row->active, 'DiscountsController'); 
                    }
                )
                ->editColumn(
                    'amount', function($row) {
                         return '<span class="display_currency" data-currency_symbol="true">'. $row->amount . '</span>';
                    }
                )
                ->rawColumns(['action', 'name', 'type', 'amount', 'active'])
                ->make(true);                   

        }



    	return view('admin.discounts.list')->with(compact(
                'articles'
            ));
    }

    public function create(Request $request) {
        $articles = Article::all();
		return view('admin.discounts.form')->with(compact(
                'articles'
        ));
    }

    public function store(Request $request) {
        if($request->ajax()) {
            
            $request->validate([
                'name' => 'required|string',
                'type' => 'required',
                'amount' => 'required|numeric|min:0',
                'date_from' => 'required',
                'date_until' => 'required'
            ]);            

            $articles = Article::find( json_decode($request->input('articles'), true) );

            $disc = new Discount([
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'amount' => $request->input('amount'),
                'date_from' => $request->input('date_from'),
                'date_until' => $request->input('date_until')
            ]);
            
            $disc->save();
            $disc->articles()->attach($articles);

            $request->session()->flash('success', __('discounts.store_msg'));

            return ['success' => true];
        } else {
            abort(404);
        }
    }

    public function edit(Request $request, $id) {
        $discount = Discount::find($id);

        $articles = Article::all();

        foreach($articles as $article) {
            if($discount->articles->contains($article) ){
                $article->selected = true;
            } else{
                $article->selected = false;
            }
        }

        if($discount != null) {
            return view('admin.discounts.form', compact('discount', 'articles'));
        } else {
            abort(404);
        }
    }

    public function update(Request $request, $id) {
    	if($request->ajax()) {
            $disc = Discount::find($id);

            if($disc == null) {
                abort(404);
            }

    		if($request->isMethod('post')) {
    			$request->validate([
                    'name' => 'required|string',
                    'type' => 'required',
                    'amount' => 'required|numeric|min:0',
                    'date_from' => 'required',
                    'date_until' => 'required'
                ]);

                $input = $request->only(['name', 'type', 'amount', 'date_from', 'date_until']);                            

                DB::beginTransaction();
		        
                $disc->articles()->detach();                

                $articles = Article::find( json_decode($request->input('articles'), true) );

                $disc->articles()->attach($articles);

                $disc->update($input);

                DB::commit();

    			$request->session()->flash('success', __('discounts.update_msg'));

                return ['success' => true];
	    	} else {

	    		return view('admin.discounts.form', compact('ad'));
	    	}
    	} else {
			abort(404);
    	}
    }

    public function destroy($id) {
        if (request()->ajax()) {
            try {     
                $discount = Discount::find($id);
                
                if($discount != null) {
                    $discount->delete();
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

    public function changeStatus($id){
        if (request()->ajax()) {
            try {                
                $notice = Discount::find($id);

                if(empty($notice)){ 
                    abort(404, 'Not found');
                }

                $notice->active = !$notice->active;
                $notice->update();
                $output = ['success' => true, 'msg' => __("general.change_status_ok")];

            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
            }

            return $output;
        }
    }

  
}
