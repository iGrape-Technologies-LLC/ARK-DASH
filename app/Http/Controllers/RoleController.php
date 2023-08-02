<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;
use DB;
use App\Notification;


class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('role.list')) {
            abort(403, 'Unauthorized action.');
        }

        if(request()->ajax()) {
            $datas = Role::where('is_default', '!=' , 1)->get();

            return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                        $html ="";   
                                    
                        
                        /*if (auth()->user()->can("role.view")) {
                            $html .= '<a class="btn btn-sm btn-info" href="' . action('RoleController@show', [$row->id]) . '"><i class="fa fa-search"></i></a>';
                        }*/
                        if($row->is_staff){
                        if(!in_array($row->id, auth()->user()->roles()->pluck('id')->toArray())){
                            if (auth()->user()->can("role.edit")) {
                                $html .= '<a class="btn btn-sm btn-primary" href="' . action('RoleController@edit', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                            }

                            if (auth()->user()->can("role.delete")) {
                                $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . action('RoleController@destroy', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                            }
                        }
                        }

                        
                        return $html;
                     }                   
                )
                ->editColumn(
                    'is_staff', function($row) {
                         return $row->is_staff ? __('users.is_staff') : __('users.isnt_staff');
                    }
                )
                ->rawColumns(['id', 'name', 'action', 'is_staff'])
                ->make(true);                        
        }

        return view('admin.roles.index');       
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        if (!auth()->user()->can('role.create')) {
            abort(403, 'Unauthorized action.');
        }

        $permission = Permission::get();
        return view('admin.roles.create',compact('permission'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('role.create')) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'permission' => 'required',
            'is_staff' => 'required'
        ]);

        $role_name = $request->input('name');

        $count = Role::where('name', $role_name)                       
                        ->count();

        if($request->input('is_staff') == 'on') {
            $is_staff = true;
        } else {
            $is_staff = false;
        }
                        
        if ($count == 0) {
            $role = Role::create(['name' => $request->input('name'), 'is_staff'=> $is_staff ]);
            $role->syncPermissions($request->input('permission'));
        } else{
            $output = ['success' => 0,
                            'msg' => __("users.role_already_exists")
            ];
        }

        return redirect()->route('admin.roles.index')
                        ->with('success',__('users.create_succesful_role'));
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('role.list')) {
            abort(403, 'Unauthorized action.');
        }

        $role = Role::find($id);

        if(empty($role)){ 
            abort(404, 'Not found');
        }

        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("role_has_permissions.role_id",$id)
            ->get();


        return view('admin.roles.show',compact('role','rolePermissions'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('role.edit')) {
            abort(403, 'Unauthorized action.');
        }

        if(in_array($id, auth()->user()->roles()->pluck('id')->toArray())){
            abort(403, 'Unauthorized action.');
        }

        $role = Role::find($id);

        if(empty($role)){ 
            abort(404, 'Not found');
        }

        $permission = Permission::get();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();


        return view('admin.roles.edit', compact('role','permission','rolePermissions'));
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
        if (!auth()->user()->can('role.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);

        if(in_array($id, auth()->user()->roles()->pluck('id')->toArray())){
            abort(403, 'Unauthorized action.');
        }

        $role_name = $request->input('name');

        $count = Role::where('name', $role_name)
                        ->where('id', '!=', $id)                        
                        ->count();

        if ($count == 0) {
            $role = Role::find($id);

            if(empty($role)){ 
                abort(404, 'Not found');
            }
            
            $role->name = $request->input('name');
            $role->save();
        } else {
            $output = ['success' => 0,
                            'msg' => __("users.role_already_exists")
            ];
        }


        $role->syncPermissions($request->input('permission'));


        return redirect()->route('admin.roles.index')
                        ->with('success',__('users.edit_succesful_role'));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('role.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if(in_array($id, auth()->user()->roles()->pluck('id')->toArray())){
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {                
                DB::table("roles")->where('id',$id)->delete();
                $output = ['success' => true, 'msg' => __("general.delete_ok")];

            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
            }

            return $output;
        }
    }
}