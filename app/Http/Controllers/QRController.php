<?php

namespace App\Http\Controllers;

use App\Traits\LibraryCSV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use DB;
use ZipArchive;
use Milon\Barcode\DNS2D;

class QRController extends Controller
{ 
    use LibraryCSV;
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

    public function load_qr($kodetoko,$nopb,$tglpb){
        $filename = date('Ymd');
        $nodspb = $this->DB_PGSQL->select("SELECT NEXTVAL('SEQ_NPB')");
        $nodspb = $nodspb[0]->nextval;
        $query = "
            SELECT 
                '*' AS recid, 
                NULL AS rtype, 
                '$nodspb' AS docno, 
                ROW_NUMBER() OVER() AS seqno, 
                pbo_nopb AS picno, 
                NULL AS picnot, 
                TO_CHAR(pbo_tglpb, 'dd-MM-YYYY') AS pictgl, 
                pbo_pluomi AS prdcd, 
                (SELECT prd_deskripsipendek 
                FROM tbmaster_prodmast 
                WHERE prd_prdcd = pbo_pluigr 
                LIMIT 1) AS nama, 
                pbo_kodedivisi AS div, 
                pbo_qtyorder AS qty, 
                pbo_qtyrealisasi AS sj_qty, 
                pbo_hrgsatuan AS price, 
                pbo_ttlnilai AS gross, 
                pbo_ttlppn AS ppnrp, 
                pbo_hrgsatuan AS hpp, 
                pbo_kodeomi AS toko, 
                'V-' AS keter, 
                TO_CHAR(CURRENT_DATE, 'dd-MM-YYYY') AS tanggal1, 
                TO_CHAR(pbo_tglpb, 'dd-MM-YYYY') AS tanggal2, 
                pbo_nopb AS docno2, 
                NULL AS lt, 
                NULL AS rak, 
                NULL AS bar, 
                (SELECT 'GI' || prs_kodeigr 
                FROM tbmaster_perusahaan 
                LIMIT 1) AS kirim, 
                LPAD(pbo_NOKOLI, 12, '0') AS dus_no, 
                NULL AS TGLEXP, 
                COALESCE(prd_ppn, 0) AS ppn_rate, 
                COALESCE(prd_flagbkp1, 'N') AS BKP, 
                COALESCE(prd_flagbkp2, 'N') AS SUB_BKP 
            FROM 
                tbmaster_pbomi 
            JOIN 
                TBMASTER_PRODMAST 
            ON 
                pbo_pluigr = prd_prdcd 
            -- WHERE -- command for debug
                -- PBO_TGLPB::date = '$tglpb'::date -- command for debug
                -- AND pbo_nopb = '$nopb' -- command for debug
                -- AND pbo_kodeomi = '$kodetoko' -- command for debug
                -- AND pbo_qtyrealisasi > 0 -- command for debug
                -- AND pbo_recordid = '4' 
                -- AND pbo_nokoli LIKE '04%'
                limit 10 -- debug
        ";

    $sourceDetail = $this->DB_PGSQL->select($query);
    $LASTCHAR_HEADER = "-";
    $LASTCHAR_DETAIL = "*";
    $fileHeader = "HEADER_" . $filename . ".CSV";
    $fileDetail = "DETAIL_" . $filename . ".CSV";
    $encryptedHeader = '';
    $encryptedDetail = '';
    $partDetail = null; 
    $dtQr = [];
    $pathqrcsv = 'QRCODE_CSV/';
    $columnDetail = [
        'DOCNO', 'PICNO', 'PICTGL', 'PRDCD', 'SJ_QTY', 'PRICE', 'PPNRP', 
        'HPP', 'KETER', 'TANGGAL1', 'TANGGAL2', 'DOCNO2', 'DUS_NO', 'PPN_RATE'
    ];

    //DETAIL
    $dataDetail = $this->create_dataDetail($sourceDetail);
    $csvDetail = $this->make_csv($dataDetail,$fileDetail,$pathqrcsv,$columnDetail);
    $encryptedDetail = $this->create_zipbyte($csvDetail, 'Detail');
    $partDetail = $this->divideText($encryptedDetail);
    //HEADER
    $dataHeader = $this->createDtHeader($dataDetail,$kodetoko,$nodspb,count($dataDetail),$this->get_lower_bound($encryptedDetail));
    $csvHeader = $this->make_csv($dataHeader['data'],$fileHeader,$pathqrcsv,$dataHeader['columns']) ;
    $encryptedHeader = $this->create_zipbyte($csvHeader, 'Header');
    
    
    if ($encryptedHeader === 'ERR') {
        return (object)['errors'=>true,'messages' => 'Failed to encrypt header'];
    }
    
    if (file_exists($csvHeader)) {
        unlink($csvHeader); 
    }

    //HEADER
    $qrRow = [
        // 'QRbyte_L' => $this->convertQRCode($encryptedHeader . $LASTCHAR_HEADER),
        'QRbyte_L' => $encryptedHeader . $LASTCHAR_HEADER,
        'Keterangan_L' => 'HEADER',
    ];
    
    //DETAIL
    for ($i = 0; $i < count($partDetail); $i++) {
        $urutan = $i + 1;
        $strUrutan = sprintf('%02d', $urutan);
        $tmpQRDetail = sprintf('%s%s%s%s', $strUrutan, $nodspb, $partDetail[$i], $LASTCHAR_DETAIL);
        if ($i % 2 == 0) {
            // $qrRow['QRbyte_R'] = $this->convertQRCode($tmpQRDetail);
            $qrRow['QRbyte_R'] = $tmpQRDetail;
            $qrRow['Keterangan_R'] = $strUrutan . ' / ' . sprintf('%02d', $this->get_lower_bound($encryptedDetail));
            $dtQr[] =(object)$qrRow;
        } else {
            $qrRow = [
                // 'QRbyte_L' => $this->convertQRCode($tmpQRDetail),
                'QRbyte_L' => $tmpQRDetail,
                'Keterangan_L' => $strUrutan . ' / ' . sprintf('%02d', $this->get_lower_bound($encryptedDetail)),
            ];

            if ($i == count($partDetail) - 1) {
                $qrRow['QRbyte_R'] = $this->convertQRCode('');
                $dtQr[] =(object)$qrRow;
            }
        }
    }
    return $dtQr;

    }
    public function createDtHeader($dt,$toko, $nosj, $jmlrecord, $jmlpart)
    {
        $dtHeader = [];

        // Define the columns
        $columns = ["TOKO", "KIRIM", "GEMBOK", "NOSJ", "NORANG", "JMLPART", "JMLRECORD"];
        
        // Create a new row
        $row = [
            "TOKO" => (string)$toko,
            "KIRIM" => "GI" .session()->get('KODECABANG'),
            "GEMBOK" => "",
            "NOSJ" => $nosj,
            "NORANG" => "",
            "JMLPART" => $jmlpart,
            "JMLRECORD" => $jmlrecord
        ];

        // Add the row to the data array
        $dtHeader[] = $row;

        return ['columns' => $columns, 'data' => $dtHeader];
    }

    public function create_dataDetail($sourceDetail){
        $columnDetail = [
            'DOCNO', 'PICNO', 'PICTGL', 'PRDCD', 'SJ_QTY', 'PRICE', 'PPNRP', 
            'HPP', 'KETER', 'TANGGAL1', 'TANGGAL2', 'DOCNO2', 'DUS_NO', 'PPN_RATE'
        ];
        $dt = array_map(function($row) use ($columnDetail) {
            $newRow = [];
            foreach ($row as $key => $value) {
                $upperKey = strtoupper($key);
                if (in_array($upperKey, $columnDetail)) {
                    $newRow[$upperKey] = $value;
                }
            }
            return $newRow;
        }, $sourceDetail);
        return $dt;
    }

    public function create_zipbyte($pathCSV, $jenis)
    {
        $passwordZip = $jenis; // Set your password
        $encrypt = '';

        try {
            if (file_exists($pathCSV)) {
                $zip = new ZipArchive();
                $zipPath = sys_get_temp_dir() . '/temp.zip';
                if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                    $zip->setPassword($passwordZip);
                    $zip->addFile($pathCSV, basename($pathCSV));
                    $zip->setEncryptionName(basename($pathCSV), ZipArchive::EM_AES_256);
                    $zip->close();
    
                    $bytes = file_get_contents($zipPath);
                    $encrypt = base64_encode($bytes);
    
                    unlink($zipPath); // Clean up the temporary file
                } else {
                    return 'ERR';
                }
                return $encrypt;
            } else {
                echo "File {$jenis} NPB QR Code Tidak Ditemukan";
                return 'ERR';
            }
        } catch (\Exception $ex) {
            echo "File {$jenis} NPB QR Gagal Encrypt Zip ".$ex->getMessage();
            return 'ERR';
        }

        return $encrypt;
    }

    public function divideText($text) {
        $MAX_CHARACTER = 3000; 
        $newText = [];
        $lowerBound = $this->get_lower_bound(strlen($text));
    
        for ($i = 0; $i < $lowerBound; $i++) {
            $startingIndex = $i * $MAX_CHARACTER;
            
            if ($i == $lowerBound - 1) {
                $newText[] = substr($text, $startingIndex);
            } else {
                $newText[] = substr($text, $startingIndex, $MAX_CHARACTER);
            }
        }
    
        return $newText;
    }

    public function get_lower_bound($text)
    {
        $MAX_CHARACTER = 3000; 
        $dataTextLength = strlen($text);
        $lowerBound = (int) ceil($dataTextLength * 1.0 / $MAX_CHARACTER);
        return $lowerBound;
    }    
    public static function convertQRCode($sTx) {
        $d = new DNS2D();
        $d->setStorPath(storage_path('framework/barcode/'));

        // Generate QR code and get the base64-encoded PNG image
        $base64QR = $d->getBarcodePNG($sTx, 'QRCODE', 4, 4);

        // Convert the base64-encoded PNG image to a binary byte array
        $qrCodeByteArray = base64_decode($base64QR);

        return $qrCodeByteArray;
    }
   
}
