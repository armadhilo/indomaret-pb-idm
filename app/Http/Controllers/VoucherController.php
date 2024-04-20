<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class VoucherController extends Controller
{
    public $DB_PGSQL;
    public function __construct()
    { 
        $this->DB_PGSQL = DB::connection('pgsql');

        // try {
        //     $this->DB_PGSQL->beginTransaction();

            
        //     $this->DB_PGSQL->commit();
        // } catch (\Throwable $th) {
            
        //     $this->DB_PGSQL->rollBack();
        //     dd($th);
        //     return response()->json(['errors'=>true,'messages'=>$th->getMessage()],500);
        // }
    }

    public function index(){
        return view("menu.voucher.index");
    }

    public function voucher_load(Request $request){
        // $this->create_table_log_npb();
        $data = $this->initial("");


        return response()->json(['errors'=>false,'messages'=>'berhasi','data'=>$data],200);
    }
    public function initial($param = "",$tanggal = null, $flag_detail = null, $mode_program = false){
        $picking = $siapDspb = $selesaiDspb = $jmlhPb = 0;
        

        if ($mode_program) {
           $selesaiDspb_condition =  " TKO_KODESBU = 'O'";
           $jmlhPb_condition =  " TKO_KODESBU = 'O'";
        } else {
           $selesaiDspb_condition =  " TKO_KODESBU = 'I'";
           $jmlhPb_condition =  " TKO_KODESBU = 'I'";
           
        }
        
        // ===========================
        // Query to get total jumlah PB
        // ===========================
        $jmlhPb =  $this->DB_PGSQL
                        ->table("tbtr_header_materai_voucher")
                        ->join("tbmaster_tokoigr",function($join){
                            $join->on("hmv_kodetoko","=","tko_kodeomi");
                        })
                        ->selectRaw(" COUNT(*) as count ");
        if (($tanggal)) {
            $jmlhPb = $jmlhPb
                       ->whereRaw("hmv_tgltransaksi::date = '$tanggal'::date");     
        }
        $jmlhPb = $jmlhPb
                  ->whereRaw($jmlhPb_condition)
                  ->get();
        $jmlhPb = $jmlhPb[0]->count;
                   
        // ===========================
        // Query to get total jumlah picking
        // ===========================
        $query_picking =  $this->DB_PGSQL
                        ->table("tbtr_header_materai_voucher")
                        ->join("tbmaster_pbomi",function($join){
                            $join->on("hmv_kodetoko" ,"=" ,"pbo_kodeomi") 
                                ->on("hmv_nopb" ,"=" ,"pbo_nopb") 
                                ->on("hmv_tglpb" ,"=" ,"pbo_tglpb");
                        })
                        ->join("plu_materai_voucher",function($join){
                            $join->on("pmv_pluidm", "=" ,"pbo_pluomi");
                        })
                        ->selectRaw(" HMV_KODETOKO KODETOKO, HMV_NOPB NOPB, HMV_TGLPB TGLPB, HMV_ITEMPB ITEMPB ")
                        ->distinct()
                        ->whereRaw("PMV_PLUIGR IS NOT NULL ")
                        ->whereRaw("HMV_FLAG = '1' ");
        if (($tanggal)) {
            $query_picking = $query_picking
                        ->whereRaw("hmv_tgltransaksi::date = '$tanggal'::date");     
        }
        $query_picking = $query_picking->toSql();
        $picking =  $this->DB_PGSQL
                        ->table($this->DB_PGSQL->raw("($query_picking) as query"))
                        ->selectRaw(" COUNT(*) as count ")
                        ->get();
      
        $picking = $picking[0]->count;


        // ===========================
        // Query to get total jumlah siapDspb
        // ===========================
        $query_siapDspb =  $this->DB_PGSQL
                        ->table("tbtr_header_materai_voucher")
                        ->join("tbmaster_pbomi",function($join){
                            $join->on("hmv_kodetoko" ,"=" ,"pbo_kodeomi") 
                                ->on("hmv_nopb" ,"=" ,"pbo_nopb") 
                                ->on("hmv_tglpb" ,"=" ,"pbo_tglpb");
                        })
                        ->join("plu_materai_voucher",function($join){
                            $join->on("pmv_pluidm", "=" ,"pbo_pluomi");
                        })
                        ->selectRaw(" HMV_KODETOKO KODETOKO, HMV_NOPB NOPB, HMV_TGLPB TGLPB, HMV_ITEMPB ITEMPB ")
                        ->distinct()
                        ->whereRaw("PMV_PLUIGR IS NOT NULL ")
                        ->whereRaw("HMV_FLAG = '4' ");
        if (($tanggal)) {
            $query_siapDspb = $query_siapDspb
                        ->whereRaw("hmv_tgltransaksi::date = '$tanggal'::date");     
        }
        $query_siapDspb = $query_siapDspb->toSql();
        $siapDspb =  $this->DB_PGSQL
                        ->table($this->DB_PGSQL->raw("($query_siapDspb) as query"))
                        ->selectRaw(" COUNT(*) as count ")
                        ->get();
      
        $siapDspb = $siapDspb[0]->count;

         // ===========================
        // Query to get total selesaiDspb
        // ===========================
        $selesaiDspb =  $this->DB_PGSQL
                        ->table("tbtr_header_materai_voucher")
                        ->join("tbmaster_tokoigr",function($join){
                            $join->on("hmv_kodetoko","=","tko_kodeomi");
                        })
                        ->selectRaw(" COUNT(*) as count ")
                        ->whereRaw("HMV_FLAG = '5'");
        if ($tanggal) {
            $selesaiDspb = $selesaiDspb
                       ->whereRaw("hmv_tgltransaksi::date = '$tanggal'::date");     
        }
        $selesaiDspb = $selesaiDspb
                  ->whereRaw($selesaiDspb_condition)
                  ->get();
        $selesaiDspb = $selesaiDspb[0]->count;

                           
        // Set labels
        $lblPB = $jmlhPb;
        $lblPicking = $picking;
        $lblDspb = $selesaiDspb;
        $lblSDspb = $siapDspb;

       
        $query_dt = $this->DB_PGSQL
                         ->table("tbtr_header_materai_voucher")
                         ->join("tbmaster_pbomi",function($join){
                             $join->on("hmv_kodetoko" ,"=" ,"pbo_kodeomi") 
                                 ->on("hmv_nopb" ,"=" ,"pbo_nopb") 
                                 ->on("hmv_tglpb" ,"=" ,"pbo_tglpb");
                         })
                         ->join("plu_materai_voucher",function($join){
                             $join->on("pmv_pluidm", "=" ,"pbo_pluomi");
                         })
                         ->selectRaw("
                         CASE WHEN coalesce(HMV_FLAG,'0') = '1' THEN 'Siap Picking' ELSE CASE WHEN coalesce(HMV_FLAG,'0') = '4' THEN 'Siap DSPB' ELSE 'Selesai DSPB' END END STAT,
                         HMV_KODETOKO KODETOKO, HMV_NOPB NOPB, HMV_TGLPB TGLPB, HMV_ITEMPB ITEMPB  
                         ")
                         ->distinct()
                         ->whereRaw("PMV_PLUIGR IS NOT NULL ");
        if ($tanggal) {
            $query_dt = $query_dt
                        ->whereRaw("hmv_tgltransaksi::date = '$tanggal'::date");     
        }
        $query_dt = $query_dt->toSql();

        $dt = $this->DB_PGSQL
                   ->table($this->DB_PGSQL->raw("($query_dt) a"))
                   ->selectRaw("ROW_NUMBER() OVER() NO, a.*")
                   ->get();
        
        $lblMonitoring = count($dt) . " PB Terupload";
        $data = [
                   "jmlh_Pb" => $jmlhPb,
                   "jmlh_picking" => $picking,
                   "jmlh_selesaiDspb" => $selesaiDspb,
                   "jmlh_siapDspb" => $siapDspb,
                   "list_data"=> $dt,
                   "label" =>$lblMonitoring
                   ];

        return $data;

    }
}
