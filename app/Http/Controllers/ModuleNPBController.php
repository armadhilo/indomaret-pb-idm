<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ModuleNPBController extends Controller
{
    public function insertToNPB($cabang, $namaFile, $dtH, $dtD) {
        $sukses = true;
    
        $npbLog = new NPBTokoLog();
        $this->insertToNPBLog($namaFile, $dtH, $npbLog);
    
        $sukses = $this->insertToNPBFile($dtD, $npbLog);
    
        if ($sukses) {
            $jamKirim = Carbon::now()->format('H:i:s');
            $sukses = $this->sendNPB($npbLog, $cabang);
    
            if ($sukses) {
                $tglConfirm = Carbon::now()->format('d/m/Y H:i:s');
            } else {
                $tglConfirm = '';
            }
    
            return $sukses;
        } else {
            return false;
        }
    }
    
    public function insertToNPBFile($tempDT, $npbLog) {
        try {
            foreach ($tempDT as $row) {
                $npbFile = new NPBTokoFile();
                $npbFile->RECID = $row['RECID'] ?? '';
                $npbFile->RTYPE = $row['RTYPE'] ?? '';
                $npbFile->DOCNO = $row['DOCNO'] ?? '';
                $npbFile->SEQNO = $row['SEQNO'] ?? 0;
                // Assign other properties in a similar manner
                
                $npbLog->DC_NPBTOKO_FILE()->save($npbFile);
            }
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function insertToNPBLog($namaFile, $tempDT, $npbLog) {
        $npbLog->KIRIM = $tempDT[0]['GUDANG'] ?? '';
        $npbLog->TOKO = $tempDT[0]['TOKO'] ?? '';
        $npbLog->DOCNO = $tempDT[0]['DOCNO'] ?? '';
        $npbLog->PICTGL = $tempDT[0]['DOC_DATE'] ?? '';
        $npbLog->NAMAFILE = $namaFile;
        $npbLog->ITEM = $tempDT[0]['ITEM'] ?? '';
        $jmlItem = $npbLog->ITEM;
    }

    public function sendNPB($npb, $cabang) {
        $sukses = true;
        $response = '';
    
        $listNPB = [];
        $listNPB[] = $npb;
    
        $jsonNPB = json_encode($listNPB);
        $notaKirim = $this->memStream($jsonNPB);
        $cabangKirim = $cabang;
    
        // Assuming npbIP is a defined constant or variable
        $npbServiceUrl = config('services.npb_service.url'); // You need to define this in your configuration files
    
        try {
            $response = Http::post($npbServiceUrl, [
                'NotaKirim' => $notaKirim,
                'CabangKirim' => $cabangKirim
            ])->body();
        } catch (\Exception $ex) {
            $response = "EX|Exception : " . $ex->getMessage();
        }
    
        $npbRes = $response;
    
        if (strpos($npbRes, "'") !== false) {
            $npbRes = str_replace("'", "''", $npbRes);
        }
    
        $splitResp = explode("|", $response);
        if ($splitResp[0] == "00") {
            // Success message
            $sukses = true;
        } else {
            // Error message handling
            $strError = $splitResp[1];
            // You should handle this error message based on your application's logic
            $sukses = false;
        }
    
        return $sukses;
    }
    
}
