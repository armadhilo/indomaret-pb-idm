<?php

namespace App\Http\Controllers;

use App\Traits\LibraryCSV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use DB;
use ZipArchive;

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

    public function create_qr_code($filename=null, $kodetoko=null, $nodspb=null, $tgldspb=null, $jmlrecord=null, $sourceDetail=[], $reprint = false, $rpt = null) {
        $pathSave = 'QRCODE_CSV/'; // csv file in storage folder
        
        if (!File::isDirectory(storage_path($pathSave))) {
            File::makeDirectory(storage_path($pathSave), 0755, true); 
        }

        $dtHeader = collect();
        $dtDetail = collect();

        $fileHeader = "HEADER_" . $filename . ".CSV";
        $fileDetail = "DETAIL_" . $filename . ".CSV";
        $encryptedHeader = '';
        $encryptedDetail = '';
        $partDetail = [];
        

        // DETAIL
        // $path_detail_csv = $this->make_csv($sourceDetail,$fileDetail,$pathSave,["DOCNO", "PICNO", "PICTGL", "PRDCD", "SJ_QTY", "PRICE", "PPNRP", "HPP", "KETER", "TANGGAL1", "TANGGAL2", "DOCNO2", "DUS_NO", "PPN_RATE"]);
        $zip = new ZipArchive;
        $filename_zip_detail='Testing zip';
        // if ($zip->open(storage_path($pathSave.$filename_zip_detail), ZipArchive::CREATE) === TRUE) {
        //     $filesToZip = [
        //         storage_path($pathSave.$fileDetail),
        //     ];

        //     foreach ($filesToZip as $file) {
        //         $zip->addFile($file, basename($file));
        //     }

        // }
        // for ($i = 0; $i < $zip->numFiles; $i++) {
        //     // Get the name of the file in the zip archive
        //     $filename = $zip->getNameIndex($i);
    
        //     // Read the contents of the file
        //     $fileContents[$filename] = $zip->getFromIndex($i);
        // }
    
        // // Close the zip file
        // $zip->close();
    
        $fileContents = $this->encrypt_zip(storage_path($pathSave.'2009021701_GSM.CSV'));
        dd($fileContents);
        $encryptedDetail = $this->createZipByte(Storage::path($pathSave . "/" . $fileDetail), "Detail");
        if ($encryptedDetail == "ERR") {
            return;
        }
        $partDetail = $this->devideText($encryptedDetail);
        if (!File::isDirectory(storage_path($pathSave.$fileDetail))) {   
            unlink(storage_path($pathSave.$fileDetail));
        }

        // HEADER

        $path_header_csv = $this->make_csv([['TOKO' => $kodetoko,'KIRIM' => 'GI' . session('KODECABANG'),'GEMBOK' => '','NOSJ' => $nodspb,'NORANG' => '','JMLPART' => $this->getLowerBound($encryptedDetail),'JMLRECORD' => $jmlrecord ]],$fileHeader,$pathSave,['KODETOKO','KIRIM','GEMBOK','NOSJ','NORANG','JMLPART','JMLRECORD']);
        $encryptedHeader = $this->createZipByte(Storage::path($pathSave . "/" . $fileHeader), "Header");
        if ($encryptedHeader == "ERR") {
            return;
        }
        if (!File::isDirectory(storage_path($pathSave.$fileHeader))) {   
            unlink(storage_path($pathSave.$fileHeader));
        }

        // -- QR_CODE
        $dtQr = new DataSetNPB\QRCodesDataTable();
        $qrRow = $dtQr->newQRCodesRow();

        // HEADER
        $qrRow["QRbyte_L"] = $this->convertQRCode($encryptedHeader . LASTCHAR_HEADER);
        $qrRow["Keterangan_L"] = "HEADER";

        // DETAIL
        foreach ($partDetail as $i => $part) {
            $urutan = $i + 1;
            $strUrutan = str_pad($urutan, 2, "0", STR_PAD_LEFT);
            $tmpQRDetail = $strUrutan . $nodspb . $part . LASTCHAR_DETAIL;

            if ($i % 2 == 0) {
                $qrRow["QRbyte_R"] = $this->convertQRCode($tmpQRDetail);
                $qrRow["Keterangan_R"] = $strUrutan . " / " . str_pad(count($partDetail), 2, "0", STR_PAD_LEFT);
                $dtQr->addQRCodesRow($qrRow);
            } else {
                $qrRow = $dtQr->newQRCodesRow();
                $qrRow["QRbyte_L"] = $this->convertQRCode($tmpQRDetail);
                $qrRow["Keterangan_L"] = $strUrutan . " / " . str_pad(count($partDetail), 2, "0", STR_PAD_LEFT);

                if ($i == count($partDetail) - 1) {
                    $qrRow["QRbyte_R"] = $this->convertQRCode("");
                    $dtQr->addQRCodesRow($qrRow);
                }
            }
        }

        // $ds->QRCodes->clear();
        // $ds->QRCodes->rows->clear();
        // $ds->QRCodes->merge($dtQr);

        $rptQR->setDataSource($ds);
        $rptQR->refresh();
        $rptQR->setParameterValue("nama_perusahaan", NamaIGR);
        $rptQR->setParameterValue("kode_kodetoko", $kodetoko);
        $rptQR->setParameterValue("no_npb", $nodspb);
        $rptQR->setParameterValue("tgl_npb", $tgldspb);
        $rptQR->setParameterValue("reprint", $reprint ? "REPRINT" : "");

        if ($reprint) {
            $rpt = $rptQR;
            return;
        }

        $r = new Report();
        $r->crv->reportSource = $rptQR;
        $r->show();
    } 

    public static function convertQRCode($sTx) {
        $cTex = $sTx;
        $qrCode = new QrCode($cTex);
        $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::L));

        // Set writer options
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Save QR code to storage
        $path = 'your_path_here'; // Set your save path here
        $filename = 'qr_code_' . time() . '.png'; // You can define your filename logic
        Storage::put($path . '/' . $filename, $result->getString());

        // If you want to return the QR code as byte array, you can use the following
        // return $result->getString();

        // If you want to return the path of the saved QR code file
        return $path . '/' . $filename;
    }


    public static function createDtHeader($dt, $kodetoko, $nosj, $jmlrecord, $jmlpart) {
        $dt = collect();
    
        $dt->push([
            'kodetoko',
            'KIRIM',
            'GEMBOK',
            'NOSJ',
            'NORANG',
            'JMLPART',
            'JMLRECORD',
        ]);
    }

    public function createDtDetail($dt, $sourceDetail) {
        // Clone the structure of sourceDetail DataTable
        $dt = $sourceDetail->clone();
    
        // List of columns to keep
        $columnDetail = ['COLUMN1', 'COLUMN2']; // Replace 'COLUMN1', 'COLUMN2' with actual column names
    
        // Remove columns not in columnDetail
        foreach ($dt->columns() as $column) {
            $columnName = strtoupper($column);
            if (!in_array($columnName, $columnDetail)) {
                $dt->removeColumn($column);
            }
        }
    
        // Merge sourceDetail into dt, ignoring any missing schema actions
        $dt->merge($sourceDetail, false, 'ignore');
    }
    

    public static function createZipAndGetContentsAsString($files, $zipFileName) {
        $zip = new ZipArchive();

        // Path where the zip file will be temporarily stored
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Create a new zip file
        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            foreach ($files as $file) {
                // Add each file to the zip archive
                $zip->addFile($file->getPathname(), $file->getBasename());
            }
            $zip->close();
        } else {
            return 'Unable to create zip file';
        }

        // Get the contents of the zip file as a string
        $zipContents = file_get_contents($zipPath);

        // Remove the temporary zip file
        unlink($zipPath);

        return $zipContents;
    }

    public function getLowerBound($text) {
        $dataTextLength = strlen($text);
        $lowerBound = intval(ceil($dataTextLength * 1.0 / $MAX_CHARACTER));
        return $lowerBound;
    }


   
}
