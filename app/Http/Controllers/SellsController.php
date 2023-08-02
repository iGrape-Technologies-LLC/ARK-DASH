<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Modules\Shipping\Entities\ShippingMethod;

use App\Models\Status;
use Yajra\DataTables\Facades\DataTables;
use DB;

use App\Utils\UtilGeneral;
use App\Utils\TransactionUtil;

use App\Mail\ConfirmOfflineSellToCustomer;
use App\Mail\CancelOfflineSellToCustomer;
use Illuminate\Support\Facades\Mail;

class SellsController extends Controller
{

    protected $UtilGeneral;
    protected $transactionUtil;

    public function __construct(UtilGeneral $UtilGeneral, TransactionUtil $transactionUtil) {
        $this->UtilGeneral = $UtilGeneral; 
        $this->transactionUtil = $transactionUtil; 

        $this->middleware('permission:sell.list|sell.create|sell.edit|sell.delete', ['only' => ['index']]); 
        $this->middleware('permission:sell.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sell.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:sell.delete', ['only' => ['destroy']]);
    }

    public function index() {

        if(request()->ajax()) {
            $datas = Transaction::where('type', 'sell')->where('status', 'approved')->with('user')->with(
                ["payments" => function($q){
                    $q->where('payments.status', '=', 'paid');
                }]
            )->with('internal_status')->with('shippings')->orderBy('created_at', 'desc');

            $filters = (request()->get('filters', null));  

            if (!empty($filters['shippings'])) {                
                $variable = $filters['shippings'];
                $datas->whereHas('shippings', function($q) use($variable) { $q->where('id', $variable); });
            } 

            if (!empty($filters['internal_status'])) {                
                $variable = $filters['internal_status'];
                $datas->whereHas('internal_status', function($q) use($variable) { $q->where('id', $variable); });
            }             

            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $start = $filters['start_date'];
                $end =  $filters['end_date'];
                $datas->whereDate('transactions.created_at', '>=', $start)
                            ->whereDate('transactions.created_at', '<=', $end);
            }

            $datas = $datas->get();

            return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                         $html ="";  

                         if (auth()->user()->can("sell.view")) {
                                $html .= '<a class="btn btn-sm btn-info" href="' . route('admin.viewSell',  [$row->id]) . '"><i class="fa fa-search"></i></a>';
                            }   

                        /*if (auth()->user()->can("sell.edit")) {
                                $html .= '<a class="btn btn-sm btn-primary" href="' . route('admin.updatediscount', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                        }

                         if (auth()->user()->can("sell.delete")) {
                                $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . route('admin.deletediscount', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                        }*/
                        
                        return $html;
                     }                   
                )   
                ->addColumn(
                    'username', function($row){
                            return $row->user->name . ' ' . $row->user->lastname;
                })   
                ->addColumn(
                    'final_total', function($row){
                        return  $this->UtilGeneral->number_format($row->final_total());
                    }
                )
                ->editColumn(
                    'created_at', function($row){
                        return  $this->UtilGeneral->format_date($row->created_at);
                    }
                )
                ->addColumn(
                    'internal_status', function($row){
                        $style ='';
                        $status = 'none';
                        $statusText = __('sells.'.$status);
                        if(!empty($row->internal_status)) {
                            $status = $row->internal_status->name;    
                            $statusText = $row->internal_status->name;
                            $style = 'border-color: ' . $row->internal_status->color . '; color: ' . $row->internal_status->color . ';';
                        }  

                        return '<a href="#" data-container=".view_modal" class="btn btn-sm btn-modal no-print btn-outline-default change_internal_status" data-href="' . action('SellsController@editStatus', [$row->id]) . '"><span class="badge"  style="'.$style.'">'.$statusText.'</span></a>'; 
                     }
                )
                ->addColumn('shipping', function($row){
                    return '<span class="badge badge-success">'.$row->shipping_name().'</span>';
                    /*if(count($row->shippings)){                        
                        return '<span class="badge badge-success">'.$row->shipping_name().'</span>';
                    } else{
                        if($row->shipping_type == 'pick_up_store'){
                            return '<span class="badge badge-principal-color">'.__('sells.pick_up_store').'</span>';
                        } else{
                            return '<span class="badge badge-principal-color">'.__('sells.without_shipping').'</span>';
                        }
                        
                    }*/

                })
                ->addColumn(
                    'payment_status', function($row){
                        $status = $row->paymentStatus();
                        
                        return '<span class="badge payment-'. $status . '">' .  __('sells.' . $status) . '</span>';
                    }
                )
                ->rawColumns(['action', 'total_articles', 'final_total', 'username', 'payment_status', 'internal_status', 'shipping'])
                ->make(true);                   

        }

        $payment_statuses = ['paid'=> __('sells.paid'), 'due'=> __('sells.due')];

        $shipping_statuses = ShippingMethod::forDropdown();

        $internal_statuses = Status::forDropdown();

        $sells_witing_count = count(Transaction::where('type', 'sell')->where('status', 'waiting_for_admin')->get());

    	return view('admin.sells.list')->with(compact('payment_statuses', 'shipping_statuses', 'internal_statuses', 'sells_witing_count'));
    }

    public function listWaitingSells() {

        if(request()->ajax()) {
            $datas = Transaction::where('type', 'sell')->where('status', 'waiting_for_admin')->with('user')->with('shippings')->orderBy('created_at', 'desc');           

            $datas = $datas->get();             

            return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                         $html ="";  
                         
                         if (auth()->user()->can("sell.view")) {
                                $html .= '<a class="btn btn-sm btn-info" href="' . route('admin.viewSell',  [$row->id]) . '"><i class="fa fa-search"></i></a>';
                            }   
                        
                        return $html;
                     }                   
                )   
                ->addColumn(
                    'confirm',
                     function ($row)  {
                         $html ="";  
                         
                         if (auth()->user()->can("sell.edit")) {
                                $html .= '<a class="btn btn-sm btn-success confirmSell" href="#!" data-href="' . 
                                    route('admin.confirmOfflineSell', [$row->id]) . '"><i class="fa fa-check"></i> '.__('sells.confirm').'</a>';
                                $html .= '<a class="btn btn-sm btn-danger" href="' . route('admin.deleteOfflineSell',  [$row->id]) . '"><i class="fa fa-trash"></i></a>';
                            }   
                        
                        return $html;
                     }                   
                )   
                ->addColumn(
                    'username', function($row){
                            return $row->user->name . ' ' . $row->user->lastname;
                })   
                ->addColumn(
                    'final_total', function($row){
                        return  $this->UtilGeneral->number_format($row->final_total());
                    }
                )
                ->editColumn(
                    'created_at', function($row){
                        return  $this->UtilGeneral->format_date($row->created_at);
                    }
                )               
                ->addColumn('shipping', function($row){
                    return '<span class="badge badge-success">'.$row->shipping_name().'</span>';
                    /*if(count($row->shippings)){                        
                        return '<span class="badge badge-success">'.$row->shipping_name().'</span>';
                    } else{
                        if($row->shipping_type == 'pick_up_store'){
                            return '<span class="badge badge-principal-color">'.__('sells.pick_up_store').'</span>';
                        } else{
                            return '<span class="badge badge-principal-color">'.__('sells.without_shipping').'</span>';
                        }
                        
                    }*/

                })               
                ->rawColumns(['action', 'final_total', 'username', 'shipping', 'confirm'])
                ->make(true);                   
        }        
    }

    public function listCancelledSells() {

        if(request()->ajax()) {
            $datas = Transaction::where('type', 'sell')->where('status', 'cancelled')->orWhere('status', 'pending')->with('user')->with('shippings')->orderBy('created_at', 'desc');           

            $datas = $datas->get();             

            return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                         $html ="";  
                         
                         if (auth()->user()->can("sell.view")) {
                                $html .= '<a class="btn btn-sm btn-info" href="' . route('admin.viewSell',  [$row->id]) . '"><i class="fa fa-search"></i></a>';
                            }   
                        
                        return $html;
                     }                   
                )   
                ->addColumn(
                    'confirm',
                     function ($row)  {
                         $html ="";  
                         
                         if (auth()->user()->can("sell.edit") && $row->status == 'pending') {
                                //$html .= '<a class="btn btn-sm btn-success" href="' . route('admin.confirmOfflineSell',  [$row->id]) . '"><i class="fa fa-check"></i> '.__('sells.confirm').'</a>';
                                $html .= '<a class="btn btn-sm btn-danger" href="' . route('admin.deleteOfflineSell',  [$row->id]) . '"><i class="fa fa-trash"></i></a>';
                         } else{
                            $html .= '-';
                         }
                        
                        return $html;
                     }                   
                )  
                ->editColumn(
                    'status', function($row){
                        $stat = 'danger';
                        if($row->status == 'pending'){                        
                            $stat = 'warning';
                        } 
                        return '<span class="badge badge-'.$stat.'">'.__('sells.'.$row->status).'</span>';
                    }
                ) 
                ->addColumn(
                    'username', function($row){
                            return $row->user->name . ' ' . $row->user->lastname;
                })   
                ->addColumn(
                    'final_total', function($row){
                        return  $this->UtilGeneral->number_format($row->final_total());
                    }
                )
                ->editColumn(
                    'created_at', function($row){
                        return  $this->UtilGeneral->format_date($row->created_at);
                    }
                )               
                ->addColumn('shipping', function($row){
                    return '<span class="badge badge-success">'.$row->shipping_name().'</span>';
                    /*if(count($row->shippings)){                        
                        return '<span class="badge badge-success">'.$row->shipping_name().'</span>';
                    } else{
                        if($row->shipping_type == 'pick_up_store'){
                            return '<span class="badge badge-principal-color">'.__('sells.pick_up_store').'</span>';
                        } else{
                            return '<span class="badge badge-principal-color">'.__('sells.without_shipping').'</span>';
                        }
                        
                    }*/

                })               
                ->rawColumns(['action', 'final_total', 'username', 'shipping', 'confirm', 'status'])
                ->make(true);                   
        }        
    }

    public function deleteOfflineSell($id){
        $transaction = Transaction::findorfail($id);
        $transaction->status = 'cancelled';
        $transaction->update();

        if(!env('APP_DEBUG')){
            $mail = Mail::to($transaction->user->email);

            try {
                $mail->send(new CancelOfflineSellToCustomer($transaction));
            } catch(\Exception $e) {
                \Log::error($e);
            }
        }  

        return redirect()->route('admin.adminsellslist');
    }

    public function confirmOfflineSell($id){
        $transaction = Transaction::findorfail($id);

        $error = $transaction->syncArticlesByStockAndAvailability();
        if($error) {
            return ['success' => false, 'msg' => __('sells.articles_changed')];
        }

        $transaction->status = 'approved';
        $transaction->update();

        $this->transactionUtil->updateStock($transaction->id);

        $this->transactionUtil->updateOfflinePayment($transaction->id);    

        if(!env('APP_DEBUG')){
            $mail = Mail::to($transaction->user->email);

            try {
                $mail->send(new ConfirmOfflineSellToCustomer($transaction));
            } catch(\Exception $e) {
                \Log::error($e);
            }
        }    

        return ['success' => true, 'url' => route('admin.adminsellslist')];
    }

    public function editStatus($id){        

        $transaction = Transaction::findorfail($id);
        $statuses = Status::orderBy('priority')->pluck('name','id');

        return view('admin.sells.partials.statuses')
               ->with(compact('transaction', 'statuses'));
    }

    /**
     * Update shipping.
     *
     * @param  Request $request, int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
      

        try {
            $input = $request->only([
                    'status'
                ]);

            $transaction = Transaction::where('id', $id)
                                ->update([
                                    'status_id' => $input['status']
                                ]);

            $output = ['success' => 1,
                            'msg' => trans("general.updated_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => trans("general.something_went_wrong")
                        ];
        }

        return $output;
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

                $transaction = Transaction::findorfail($id);

                $this->transactionUtil->deleteTransaction($transaction->id);


                $output = ['success' => true, 'msg' => __("general.delete_ok")];                


            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
            }
            return $output;
        }        
    }

    public function view($id){

        $transaction = Transaction::findorfail($id);
        $statuses = Status::orderBy('priority')->pluck('name','id');

        return view('admin.sells.view')
               ->with(compact('transaction', 'statuses'));
    }
  
}
