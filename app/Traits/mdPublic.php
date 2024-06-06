<?php

namespace App\Traits;

use PDF;
use DB;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

trait mdPublic
{    
    public function checkPPN($flagbkp)
    {
        $query = "
            SELECT DISTINCT 
                kfp_statuspajak AS STATUS,
                kfp_kodefp AS KODEFP,
                CONCAT(kfp_kodefp, kfp_kodereferensi_ef) AS REF,
                kfp_kodereferensi_ef AS REF2
            FROM tbmaster_kodefp 
            WHERE CONCAT(kfp_flagbkp1, kfp_flagbkp2) = '$flagbkp]'
        ";

        $dtPPN = DB::select($query);

        if (count($dtPPN) == 0) {
            $resp = "Data PPN tidak ditemukan (TBMASTER_KODEFP)";
        } elseif (count($dtPPN) > 1) {
            $resp = "Data PPN lebih dari 1 (TBMASTER_KODEFP)";
        } else {
            $resp = "OK";
        }

        return (object)[
            'status' => $resp,
            'data' => $dtPPN
        ];
    }
}