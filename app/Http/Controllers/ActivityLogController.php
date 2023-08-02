<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\NoticeCategory;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Hash;
use Spatie\Activitylog\Models\Activity;
use App\Utils\UtilGeneral;


class ActivityLogController extends Controller
{
    protected $UtilGeneral;

    public function __construct(UtilGeneral $UtilGeneral)
    {
        $this->UtilGeneral = $UtilGeneral;        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('activity_log.list')) {
            abort(403, 'Unauthorized action.');
        }

        if(request()->ajax()) {

            $datas = Activity::orderBy('created_at', 'desc')->with('subject', 'causer')->get();

            return DataTables::of($datas)       
                ->addColumn('causer', function($row){                    
                    if(!empty($row->causer)){
                        return ( $row->causer->name . ' ' . $row->causer->lastname  );
                    } else{
                        return "-";
                    }
                })    
                ->editColumn('created_at', function($row){
                    return $this->UtilGeneral->format_date($row->created_at);
                })     
                ->rawColumns(['id', 'causer', 'description', 'created_at', 'subject_type'])
                ->make(true);                        
        }

       

        return view('admin.activity_log.index');            
    }

}