<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Advertisement;
use Yajra\DataTables\Facades\DataTables;

class AdvertisementsController extends Controller
{

    public function __construct() {
        $this->middleware('permission:advertisement.list|advertisement.create|advertisement.edit|advertisement.delete', ['only' => ['index']]); 
        $this->middleware('permission:advertisement.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:advertisement.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:advertisement.delete', ['only' => ['destroy']]);
    }

    public function index() {

        if(request()->ajax()) {
            $datas = Advertisement::all();

            return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                         $html ="";     

                        if (auth()->user()->can("advertisement.edit")) {
                                $html .= '<a class="btn btn-sm btn-primary" href="' . route('admin.updateadvertisement', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                        }

                         if (auth()->user()->can("advertisement.delete")) {
                                $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . route('admin.deleteadvertisement', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                        }
                        
                        return $html;
                     }                   
                )
                ->editColumn('aside', function($row) {
                    if($row->aside) {
                        return '<button type="button" class="btn btn-sm btn-outline-success">Si</button>';
                    } else {
                        return '<button type="button" class="btn btn-sm btn-outline-danger">No</button>';
                    }
                })
                ->editColumn('inter_product', function($row) {
                    if($row->inter_product) {
                        return '<button type="button" class="btn btn-sm btn-outline-success">Si</button>';
                    } else {
                        return '<button type="button" class="btn btn-sm btn-outline-danger">No</button>';
                    }
                })
                ->editColumn(
                    'link_to', function($row) {
                         return '<a href="'. $row->link_to .'" target="_blank">'.$row->link_to. '</a>' ;
                    }
                )
                ->rawColumns(['link_to', 'aside', 'inter_product', 'action'])
                ->make(true);                   

        }

    	return view('admin.advertisements.list');
    }

    public function create(Request $request) {
		return view('admin.advertisements.form');
    }

    public function store(Request $request) {
        if($request->ajax()) {
            $request->validate([
                'path' => 'required|string|max:1000',
                'link_to' => 'required|url|min:1|max:500',
            ]);

            $ad = new Advertisement([
                'path' => $request->input('path'),
                'link_to' => $request->input('link_to')
            ]);

            /*if($request->input('home') == 'on') {
                $ad->home = true;
            }*/

            if($request->input('aside') == 'on') {
                $ad->aside = true;
            }

            if($request->input('inter_product') == 'on') {
                $ad->inter_product = true;
            }

            $ad->save();

            $request->session()->flash('success', __('advertisements.store_msg'));

            return ['success' => true];
        } else {
            abort(404);
        }
    }

    public function edit(Request $request, $id) {
        $ad = Advertisement::find($id);

        if($ad != null) {
            return view('admin.advertisements.form', compact('ad'));
        } else {
            abort(404);
        }
    }

    public function update(Request $request, $id) {
    	if($request->ajax()) {
            $ad = Advertisement::find($id);

            if($ad == null) {
                abort(404);
            }

    		if($request->isMethod('post')) {
    			$request->validate([
	    			'path' => 'required|string|max:1000',
	    			'link_to' => 'required|url|min:1|max:500',
	    		]);

                $ad->path = $request->input('path');
	    		$ad->link_to = $request->input('link_to');

		        /*if($request->input('home') == 'on') {
		        	$ad->home = true;
		        } else {
		        	$ad->home = false;
		        }*/

		        if($request->input('aside') == 'on') {
		        	$ad->aside = true;
		        } else {
		        	$ad->aside = false;
		        }

		        if($request->input('inter_product') == 'on') {
		        	$ad->inter_product = true;
		        } else {
		        	$ad->inter_product = false;
		        }

		        $ad->save();

    			$request->session()->flash('success', __('advertisements.update_msg'));

                return ['success' => true];
	    	} else {
	    		return view('admin.advertisements.form', compact('ad'));
	    	}
    	} else {
			abort(404);
    	}
    }

    public function destroy($id) {
        if (request()->ajax()) {
            try {     
                $adverisement = Advertisement::find($id);
                
                if($adverisement != null) {
                    $adverisement->delete();
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

  
}
