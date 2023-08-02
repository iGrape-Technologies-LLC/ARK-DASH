<?php

namespace App\Utils;

use Carbon\Carbon;
use App\Models\Photo;
use App\Models\Transaction;
use DB;

class TransactionUtil 
{
    public function getSellsCurrentFy($start, $end)
    {
        $query = Transaction::where('type', 'sell')
                            ->where('status', 'approved')
                            ->whereBetween(DB::raw('date(created_at)'), [$start, $end])
                            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))
                            ->select(                                
                                DB::raw("DATE_FORMAT(created_at, '%d-%m-%Y') as date"),
                                DB::raw("SUM( total_articles + total_shipping - total_discounts ) as total")
                            );

        $sells = $query->get();
        
        $sells = $sells;        
        
        return $sells;
    }

    public function updateStock($id){
        if(env('MANAGE_STOCK', false)){
            $transaction = Transaction::findorfail($id);

            DB::beginTransaction();

            foreach ($transaction->lines as $line) {
                $line->article_property->stock = $line->article_property->stock - $line->quantity;
                $line->article_property->save();
            }

            DB::commit();
        }
    }

    public function updateOfflinePayment($id){
        $transaction = Transaction::findorfail($id);

        DB::beginTransaction();

            foreach ($transaction->payments as $payment) {
                $payment->amount = $transaction->total_paid;
                $payment->status = 'paid';
                $payment->update();
            }

        DB::commit();
    }

    public function deleteTransaction($id){
        $transaction = Transaction::findorfail($id);

        DB::beginTransaction();
            if($transaction->status == 'approved'){
                foreach ($transaction->lines as $line) {
                    $line->article_property->stock = $line->article_property->stock + $line->quantity;
                    $line->article_property->save();
                }
            }
            
            $transaction->status = 'cancelled';
            $transaction->update();
        DB::commit();
    }
}
