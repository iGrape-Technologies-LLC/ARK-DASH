<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Status;
use App\Utils\TransactionUtil;
use Yajra\DataTables\Facades\DataTables;
use DB;

class StatisticsController extends Controller
{

    protected $transactionUtil;

    public function __construct(TransactionUtil $transactionUtil) {
        $this->transactionUtil = $transactionUtil;
    }
    
    public function chartSells() {

        if(request()->ajax()) {
            /*$datas = Transaction::where('type', 'sell')->where('status', 'approved')->with('user')->with(
                ["payments" => function($q){
                    $q->where('payments.status', '=', 'paid');
                }]
            )->with('internal_status')->with('shippings')->orderBy('created_at', 'desc')->get();*/


            $sells_this_fy = $this->transactionUtil->getSellsCurrentFy(date('Y-m-01'), date('Y-m-t'));

        
            return $output = ['success' => true, 'msg' => __("general.ok"), 'data' => $sells_this_fy];                            

        }
    }

  
}
