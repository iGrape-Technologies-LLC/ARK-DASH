<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Hash;



class BrandsController extends Controller
{
    public function __construct()
    {
        
        $this->middleware('permission:brand.list|brand.create|brand.edit|brand.delete', ['only' => ['index']]);
        $this->middleware('permission:brand.view', ['only' => ['show']]);   
        $this->middleware('permission:brand.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:brand.edit', ['only' => ['update', 'edit']]);
        $this->middleware('permission:brand.delete', ['only' => ['destroy']]);   
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if(request()->ajax()) {

            $datas = Brand::orderBy('name','ASC')->get();

            return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                        $html ="";                        
                       
                        /*if (auth()->user()->can("brand.view")) {
                                $html .= '<a class="btn btn-info" href="' . action('BrandsController@show', [$row->id]) . '"><i class="fa fa-search"></i></a>';
                        }*/

                        if (auth()->user()->can("brand.edit")) {
                                $html .= '<a class="btn btn-sm btn-primary" href="' . action('BrandsController@edit', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                        }

                        if (auth()->user()->can("brand.delete")) {
                                $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . action('BrandsController@destroy', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                        }
                        
                        return $html;
                     }                   
                )
                ->addColumn('mass_delete', function ($row) {
                    return  '<input type="checkbox" class="row-select" value="' . $row->id .'">' ;
                })
                ->rawColumns(['id', 'name', 'action', 'mass_delete'])
                ->make(true);                        
        }

        return view('admin.brands.index');            
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        return view('admin.brands.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        $this->validate($request, [
            'name' => 'required'
        ]);

        $input = $request->all();        

        $user = Brand::create($input);        

        return redirect()->route('admin.brands.index')
                        ->with('success',__('brands.store_msg'));
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      

        $brand = Brand::find($id);

        if(empty($brand)){ 
            abort(404, 'Not found');
        }

        return view('admin.brands.show',compact('brand'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
     

        $brand = Brand::find($id);
        
        if(empty($brand)){ 
            abort(404, 'Not found');
        }

        return view('admin.brands.edit',compact('brand'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    
        $this->validate($request, [
            'name' => 'required'
        ]);

        $input = $request->all();       


        $brand = Brand::find($id);

        if(empty($brand)){ 
            abort(404, 'Not found');
        }

        $brand->update($input);
                

        return redirect()->route('admin.brands.index')
                        ->with('success',__('brands.update_msg'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('brand.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {            
            try {                
                Brand::find($id)->delete();
                
                $output = ['success' => true, 'msg' => __("general.delete_ok")];

            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false, 'msg' => __("custom.something_went_wrong")];
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
        if (!auth()->user()->can('brand.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {            

            if (!empty($request->input('selected_rows'))) {                

                $selected_rows = explode(',', $request->input('selected_rows'));

                $brands = Brand::whereIn('id', $selected_rows)                                    
                                    ->get();
                $deletable_products = [];

                DB::beginTransaction();

                foreach ($brands as $brand) {
                    $brand->delete();                    
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