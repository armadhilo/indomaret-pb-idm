<?php

namespace App\Traits;

use PDF;
use DB;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

trait mdPublic
{   
    public function simpanDSPB($namafile = null, $kodetoko = null, $nopb = null, $nopick = null, $nosj = null, $nodspb = null, $jenispb= null)
    {
        $ret = false;
        $jenispb = strtoupper($jenispb);
        $KodeIGR =  session()->get('KODECABANG'); 
        $UserMODUL = session()->get('userid'); 

        $sql = "KODEIGR = '$KodeIGR' AND NAMAFILE = '$namafile' AND KODETOKO = '$kodetoko' AND NOPB = '$nopb' AND NOPICK = '$nopick' AND NOSJ = '$nosj' AND NODSPB = '$nodspb' AND JENISPB = '$jenispb'";
        $bindings = [$KodeIGR, $namafile, $kodetoko, $nopb, $nopick, $nosj, $nodspb, $jenispb];

        DB::beginTransaction();
        try {
            $recordExists = DB::table('TBHISTORY_DSPB')->whereRaw($sql)->exists();

            if (!$recordExists) {
                $query = "INSERT INTO TBHISTORY_DSPB (KODEIGR, NAMAFILE, KODETOKO, NOPB, NOPICK, NOSJ, NODSPB, JENISPB, CREATEBY, CREATEDT)
                        VALUES ('$KodeIGR', '$namafile', '$kodetoko', '$nopb', '$nopick', '$nosj', '$nodspb', '$jenispb', '$UserMODUL', current_date)";

                DB::insert($query);
            }

            $ret = true;
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            $ret = false;
        }

        return $ret;
    }

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

    //OMI

    public function HelpTokoOMI(){
        $data = DB::select("
            select TKO_KODEOMI,TKO_NAMAOMI
            from  tbmaster_tokoigr 
            where TKO_KODESBU = 'O'
            limit 2000
        ");
        return $data;
    }
    //IDM

    public function HelpTokoIDM(){
        $data = DB::select("
            select TKO_KODEOMI,TKO_NAMAOMI
            from  tbmaster_tokoigr
            WHERE TKO_KODESBU = 'I'
            limit 2000
        ");
        return $data;
    }



    public function caesarEncrypt($plainText, $date) {
        // Define the character arrangement used for encryption
        $stringArrangement = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'; // Adjust according to your arrangement
        $cipherText = '';
        $date = strtotime($date); // Convert date string to timestamp
    
        // Calculate the password value based on the month and day of the given date
        $pass = date('n', $date) + date('j', $date); // 'n' is month without leading zeros, 'j' is day without leading zeros
    
        if ($pass == 36) {
            $pass = 37;
        }
    
        // Encrypt each character in the plain text
        for ($i = 0; $i < strlen($plainText); $i++) {
            $currentCharPlain = $plainText[$i];
            $indexCharPlain = strpos($stringArrangement, $currentCharPlain);
            $indexCharCipher = -1;
            $numberOfStep = $pass % strlen($stringArrangement);
    
            if ($indexCharPlain + $numberOfStep > strlen($stringArrangement) - 1) {
                $indexCharCipher = $numberOfStep - (strlen($stringArrangement) - $indexCharPlain);
            } else {
                $indexCharCipher = $indexCharPlain + $numberOfStep;
            }
    
            $currentCharCipher = $stringArrangement[$indexCharCipher];
            $cipherText .= $currentCharCipher;
        }
    
        return $cipherText;
    }
}