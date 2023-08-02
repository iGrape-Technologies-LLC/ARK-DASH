<?php


namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\NoticePhoto;
use Illuminate\Http\Request;

use App\Models\NoticeCategory;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\User;
use App\Utils\UtilGeneral;
use App\Utils\UtilFile;
use Carbon\Carbon;

class NoticeController extends Controller
{
    protected $UtilGeneral;
    protected $UtilFile;

    public function __construct(UtilGeneral $UtilGeneral, UtilFile $UtilFile)
    {
        $this->UtilGeneral = $UtilGeneral;
        $this->UtilFile = $UtilFile;

        $this->middleware('permission:notice.list|notice.create|notice.edit|notice.delete', ['only' => ['index']]);
        $this->middleware('permission:notice.view', ['only' => ['show']]);
        $this->middleware('permission:notice.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:notice.edit', ['only' => ['update', 'edit']]);
        $this->middleware('permission:notice.delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index(Request $request)
    {
        if(request()->ajax()) {

            $datas = Notice::all();

            return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                        $html ="";


                        if (auth()->user()->can("notice.view")) {
                                $html .= '<a class="btn btn-sm btn-info" target="_blank" href="' . route('front.noticedetail',  [$row->slug]) . '"><i class="fa fa-eye"></i></a>';
                        }

                        if (auth()->user()->can("notice.edit")) {
                                $html .= '<a class="btn btn-sm btn-primary" href="' . action('NoticeController@edit', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                        }

                         if (auth()->user()->can("notice.delete")) {
                                $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . action('NoticeController@destroy', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                        }

                        return $html;
                     }
                 )
                ->editColumn(
                    'active', function($row) {
                         return $this->UtilGeneral->changeStatus($row->id, $row->active, 'NoticeController');
                    }
                )
                ->editColumn(
                    'publish_date', function($row) {
                         return $this->UtilGeneral->format_date($row->publish_date) ;
                    }
                )
                ->rawColumns(['id', 'title', 'publish_date', 'active', 'action'])
                ->make(true);
        }


        return view('admin.notices.index');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $default_datetime = $this->UtilGeneral->format_date('now');

        $categories = NoticeCategory::forDropdown(false);

        return view('admin.notices.create')->with(compact('default_datetime','categories'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        request()->validate([
            'title' => 'required',
            'short_description' => 'required',
            'publish_date'=> 'required',
            'article-photos' => 'required|string|min:2'
            //'description' => 'required',
        ]);

        $request->merge(['created_by' => auth()->user()->id]);

        //$request->replace(['publish_date' => $publish_date]);

        $input = $request->only(['title', 'description', 'short_description', 'author', 'publish_date', 'categories', 'created_by']);

        $publish_date = \DateTime::createFromFormat('d/m/Y H:i', $input['publish_date']);
        $input['publish_date'] = $publish_date ? $publish_date->format("Y-m-d H:i") : now();

        DB::beginTransaction();

        $categories = NoticeCategory::find( $request->input('categories'));

        $notice = Notice::create($input);
        $notice->categories()->attach($categories);
        $notice->save();

        $principalPhoto = $request->input('principal-photo');
        $error = $this->UtilFile->updatePhotos($request->input('article-photos'), $notice, $principalPhoto, false);
        if($error) {
            throw ValidationException::withMessages([
                'file' => __('notices.photo_required')
            ]);
        }

        DB::commit();


        return redirect()->route('admin.notices.index')
                        ->with('success',__('notices.create_succesfull'));
    }



    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $notice = Notice::find($id);

        if(empty($notice)){
            abort(404, 'Not found');
        }

        return view('admin.notices.show',compact('notice'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $notice = Notice::find($id);

        if(empty($notice)){
            abort(404, 'Not found');
        }

        $default_datetime = $this->UtilGeneral->format_date($notice->publish_date);

        $categories = NoticeCategory::forDropdown(false);

        return view('admin.notices.edit',compact('notice', 'default_datetime', 'categories'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        request()->validate([
            'title' => 'required',
            'short_description' => 'required',
            'publish_date'=> 'required',
            //'description' => 'required'
        ]);

        $input = $request->only(['title', 'description', 'short_description', 'author', 'publish_date', 'categories']);

        $publish_date = \DateTime::createFromFormat('d/m/Y H:i', $input['publish_date']);
        $input['publish_date'] = $publish_date ? $publish_date->format("Y-m-d H:i") : now();

        $notice = Notice::find($id);

        if(empty($notice)){
            abort(404, 'Not found');
        }

        DB::beginTransaction();

        $notice->categories()->detach();

        $categories = NoticeCategory::find( $request->input('categories'));

        $notice->categories()->attach($categories);

        $notice->update($input);

        $principalPhoto = $request->input('principal-photo');
        $error = $this->UtilFile->updatePhotos($request->input('article-photos'), $notice, $principalPhoto, false);
        if($error) {
            throw ValidationException::withMessages([
                'file' => __('notices.photo_required')
            ]);
        }

        DB::commit();

        return redirect()->route('admin.notices.index')
                        ->with('success',__('notices.edit_succesfull'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (request()->ajax()) {
            try {
                DB::table("notices")->where('id',$id)->delete();
                $output = ['success' => true, 'msg' => __("general.delete_ok")];

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
                $notice = Notice::find($id);

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

    public function detail(Request $request, $id){

        $new = Notice::where('slug', $id)->where('active', '1')->first();

        if(empty($new)){
            abort(404);
        }

        //$notice->publish_date = $this->UtilGeneral->format_date_long($notice->publish_date);

        $new_categories = NoticeCategory::all();

        $relateds = Notice::where('active', '1')->where('publish_date', '<', Carbon::now()->setTimezone('UTC'))->where('id', '!=', $id)->orderBy('publish_date', 'desc')->limit(5)->get();

        /*foreach ($relateds as $related) {
            $related->publish_date =  $this->UtilGeneral->format_date_long($related->publish_date, false);
        }*/

        return view('front.pages.news.detail',compact('new', 'new_categories', 'relateds'));
    }


    //Cuando no hay categorias
    public function list(Request $request){


        $notices = Notice::where('active', '1')->where('publish_date', '<', Carbon::now()->setTimezone('UTC'))->orderBy('publish_date', 'desc');

        if($request->query('s') != null) {
                $cleanedSearch =
                str_replace('+', ' ',
                    urlencode(
                        str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'],
                            $request->query('s'))));

                $notices = $notices->where('title', 'like', '%' . $cleanedSearch . '%');
            }

        if($request->query('category_id') != null) {
            $category_id = $request->query('category_id');
            $notices->whereHas('categories', function($query) use($category_id) {
                $query->where('notice_categories.id', $category_id);
            });
        }

        $notices = $notices->paginate(4);

        /*foreach ($notices as $notice) {
            $notice->publish_date = $this->UtilGeneral->format_date_short($notice->publish_date, false);
        }*/

        $notice_categories = NoticeCategory::all();

        return view('front.pages.news.index',compact('notices', 'notice_categories'));
    }


}
