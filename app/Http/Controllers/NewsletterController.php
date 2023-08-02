<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Newsletter;
use Validator;
use Yajra\DataTables\Facades\DataTables;

use App\Utils\UtilGeneral;

class NewsletterController extends Controller
{ 
    protected $UtilGeneral;

    public function __construct(UtilGeneral $UtilGeneral) {

        $this->UtilGeneral = $UtilGeneral;  

        $this->middleware('permission:newsletter.list', ['only' => ['index']]);
    }

    public function changeStatus($id){
        if (request()->ajax()) {
            try {                
                $news = Newsletter::find($id);

                if(empty($news)){ 
                    abort(404, 'Not found');
                }

                $news->suscribed = !$news->suscribed;
                $news->update();
                $output = ['success' => true, 'msg' => __("general.change_status_ok")];

            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
            }

            return $output;
        }
    }

    public function suscribe(Request $request) {
    	if($request->ajax()) {
    		$request->validate([
    			'email' => ['required', 'max:500', 'email']
    		]);

    		$news = Newsletter::where('email', $request->input('email'))->get();
    		
			if(count($news) == 0) {
	    		$news = new Newsletter([
	    			'email' => $request->input('email')
	    		]);
	    		$news->save();
	    	}

            $output = ['success' => true, 'msg' => __("general.newsletter_success")];

            return $output;
    	}

        $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
    }

    public function unsuscribe(Request $request, $email) {
    	$validator = Validator::make(['email' => $email], [
    		'email' => ['required', 'max:500', 'email']
    	]);

    	if(!$validator->fails()) {
    		$news = Newsletter::where('email', $email)->get();

    		if(count($news) > 0) {
    			$news[0]->suscribed = false;
    			$news[0]->save();
    		}
    	}

    	return redirect()->route('front.pages.home');
    }

    public function index() {

        if(request()->ajax()) {

            $datas= Newsletter::all();

            return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                         $html ="";     
                       

                         if (auth()->user()->can("newsletter.list")) {
                                $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . route('admin.deletenewsletter', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                        }
                        
                        return $html;
                     }                   
                )
                ->editColumn('suscribed', function($row) {                                            
                    return $this->UtilGeneral->changeStatus($row->id, $row->suscribed, 'NewsletterController');                         
                })
                ->rawColumns(['email', 'suscribed', 'action'])
                ->make(true);                   

        }


        $newsletters = Newsletter::all();

        return view('admin.newsletter.list', compact('newsletters'));
    }

    public function destroy($id) {
        if (request()->ajax()) {
            try {     
                $article = Newsletter::find($id);
                
                $article->delete();
                $output = ['success' => true, 'msg' => __("general.delete_ok")];

                
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
            }
            return $output;            
        }

    }
}
