<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\NoticeCategory;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Hash;



class NoticeCategoryController extends Controller
{
    public function __construct()
    {
        
        $this->middleware('permission:notice_category.list|notice_category.create|notice_category.edit|notice_category.delete', ['only' => ['index']]);
        $this->middleware('permission:notice_category.view', ['only' => ['show']]);   
        $this->middleware('permission:notice_category.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:notice_category.edit', ['only' => ['update', 'edit']]);
        $this->middleware('permission:notice_category.delete', ['only' => ['destroy']]);   
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if(request()->ajax()) {

            $datas = NoticeCategory::orderBy('name','ASC')->get();

            return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                        $html ="";                        
                       
                        /*if (auth()->user()->can("notice_category.view")) {
                                $html .= '<a class="btn btn-sm btn-info" href="' . action('NoticeCategoryController@show', [$row->id]) . '"><i class="fa fa-search"></i></a>';
                        }*/

                        if (auth()->user()->can("notice_category.edit")) {
                                $html .= '<a class="btn btn-sm btn-primary" href="' . action('NoticeCategoryController@edit', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                        }

                        if (auth()->user()->can("notice_category.delete")) {
                                $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . action('NoticeCategoryController@destroy', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
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

        return view('admin.notice_categories.index');            
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        return view('admin.notice_categories.create');
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

        $user = NoticeCategory::create($input);        

        return redirect()->route('admin.notice_categories.index')
                        ->with('success',__('categories.store_msg'));
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      

        $notice_category = NoticeCategory::find($id);

        if(empty($notice_category)){ 
            abort(404, 'Not found');
        }

        return view('admin.notice_categories.show',compact('notice_category'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
     

        $notice_category = NoticeCategory::find($id);
        
        if(empty($notice_category)){ 
            abort(404, 'Not found');
        }

        return view('admin.notice_categories.edit',compact('notice_category'));
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


        $notice_category = NoticeCategory::find($id);

        if(empty($notice_category)){ 
            abort(404, 'Not found');
        }

        $notice_category->update($input);
                

        return redirect()->route('admin.notice_categories.index')
                        ->with('success',__('categories.update_msg'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('notice_category.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {            
            try {                
                NoticeCategory::find($id)->delete();
                
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
        if (!auth()->user()->can('notice_category.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {            

            if (!empty($request->input('selected_rows'))) {                

                $selected_rows = explode(',', $request->input('selected_rows'));

                $categories = NoticeCategory::whereIn('id', $selected_rows)                                    
                                    ->get();
                $deletable_products = [];

                DB::beginTransaction();

                foreach ($categories as $category) {
                    $category->delete();                    
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