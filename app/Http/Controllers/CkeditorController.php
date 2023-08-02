<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Hash;



class CkeditorController  extends Controller
{
    public function __construct()
    {
        
        $this->middleware('permission:notice.list|notice.create|notice.edit|notice.delete', ['only' => ['index']]);
        $this->middleware('permission:notice.view', ['only' => ['show']]);   
        $this->middleware('permission:notice.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:notice.edit', ['only' => ['update', 'edit']]);
        $this->middleware('permission:notice.delete', ['only' => ['destroy']]);   
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('ckeditor');
    }
  
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        if($request->hasFile('upload')) {
            $originName = $request->file('upload')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('upload')->getClientOriginalExtension();
            $fileName = $fileName.'_'.time().'.'.$extension;
        
            $request->file('upload')->move(public_path('storage/servicios'), $fileName);
   
            $CKEditorFuncNum = $request->input('CKEditorFuncNum');
            $url = asset('storage/servicios/'.$fileName); 
            $msg = 'Image uploaded successfully'; 
            $response = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url')</script>";
               
            @header('Content-type: text/html; charset=utf-8'); 
            echo $response;
        }
    }
   
}