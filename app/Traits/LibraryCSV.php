<?php

namespace App\Traits;

use PDF;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

trait LibraryCSV
{    
        
    /**
     * make_csv
     *
     * @param  mixed $columnHeader
     * @param  mixed $filename
     * @param  mixed $datas
     * @param  mixed $storage_path
     * @return void
     */

     /**
      *  example header
      *
      * $header = [
      *    "nama_column 1",
      *    "nama_column 2",
      *    "nama_column 3",
      *  ];
      */

     /**
      *  example datas
      *
      * $datas = [
      *    "nama_column 1"=> "value1",
      *    "nama_column 2"=> "value2",
      *    "nama_column 3"=> "value3",
      *  ];
      */

      /**
       * Example
       * 
       * $filename = "testing.csv";
       * 
       * **/

      /**
       * Example
       * storage_path variable is the location of the csv file that is stored in the storage folder
       * $storage_path = "csv/";
       * 
       * **/
    public function download_csv($full_path = null,$datas = [],$filename = "File .csv",$storage_path = "csv/",$columnHeader = [])
    {
        // Get the full path to the CSV file
        if ($full_path) {
            $fullPath = $full_path;
        }else{
            // if datas null ,and column header null
            if (!(count($datas)) && !(count($columnHeader))) {
                $columnHeader = ["Data Tidak Ditemukan"];
            }
            $fullPath =$this->make_csv($datas,$filename,$storage_path,$columnHeader);
        }

        // Check if the file exists
        if (file_exists($fullPath)) {
            // Set the CSV response headers
            $response = Response::make(file_get_contents($fullPath), 200);
            $response->header('Content-Type', 'text/csv');
            $response->header('Content-Disposition', 'attachment; filename="' . basename($fullPath) . '"');
            unlink($fullPath);
            return $response;
        } else {
            // If the file does not exist, return a 404 response
            abort(404);
        }
    }

    public function make_csv($datas = [],$filename = "File .csv",$storage_path = "csv/",$columnHeader = [])
    {
        // $testing_data = json_decode('[{"KODE TOKO":"F33E","PLU":"10000019","FLAG":"T","QTY TARGET":"69","QTY AVGSPDNORMAL":"69"},{"KODE TOKO":"F33E","PLU":"10000021","FLAG":"X","QTY TARGET":"25","QTY AVGSPDNORMAL":"25"},{"KODE TOKO":"F33E","PLU":"10000069","FLAG":"T","QTY TARGET":"10","QTY AVGSPDNORMAL":"10"},{"KODE TOKO":"F33E","PLU":"10000073","FLAG":"T","QTY TARGET":"19","QTY AVGSPDNORMAL":"19"},{"KODE TOKO":"F33E","PLU":"10000077","FLAG":"T","QTY TARGET":"0","QTY AVGSPDNORMAL":"0"},{"KODE TOKO":"F33E","PLU":"10000088","FLAG":"T","QTY TARGET":"15","QTY AVGSPDNORMAL":"15"},{"KODE TOKO":"F33E","PLU":"10000090","FLAG":"T","QTY TARGET":"19","QTY AVGSPDNORMAL":"19"},{"KODE TOKO":"F33E","PLU":"10000097","FLAG":"T","QTY TARGET":"3","QTY AVGSPDNORMAL":"3"},{"KODE TOKO":"F33E","PLU":"10000098","FLAG":"T","QTY TARGET":"6","QTY AVGSPDNORMAL":"6"},{"KODE TOKO":"F33E","PLU":"10000101","FLAG":"T","QTY TARGET":"2","QTY AVGSPDNORMAL":"2"},{"KODE TOKO":"F33E","PLU":"10000102","FLAG":"T","QTY TARGET":"0","QTY AVGSPDNORMAL":"0"},{"KODE TOKO":"F33E","PLU":"10000126","FLAG":"T","QTY TARGET":"2","QTY AVGSPDNORMAL":"2"},{"KODE TOKO":"TX6P","PLU":"10000019","FLAG":"T","QTY TARGET":"31","QTY AVGSPDNORMAL":"31"},{"KODE TOKO":"TX6P","PLU":"10000021","FLAG":"X","QTY TARGET":"25","QTY AVGSPDNORMAL":"25"},{"KODE TOKO":"TX6P","PLU":"10000069","FLAG":"T","QTY TARGET":"10","QTY AVGSPDNORMAL":"10"},{"KODE TOKO":"TX6P","PLU":"10000073","FLAG":"T","QTY TARGET":"19","QTY AVGSPDNORMAL":"19"},{"KODE TOKO":"TX6P","PLU":"10000077","FLAG":"T","QTY TARGET":"0","QTY AVGSPDNORMAL":"0"},{"KODE TOKO":"TX6P","PLU":"10000088","FLAG":"T","QTY TARGET":"15","QTY AVGSPDNORMAL":"15"},{"KODE TOKO":"TX6P","PLU":"10000090","FLAG":"T","QTY TARGET":"19","QTY AVGSPDNORMAL":"19"},{"KODE TOKO":"TX6P","PLU":"10000097","FLAG":"T","QTY TARGET":"3","QTY AVGSPDNORMAL":"3"},{"KODE TOKO":"TX6P","PLU":"10000098","FLAG":"T","QTY TARGET":"6","QTY AVGSPDNORMAL":"6"},{"KODE TOKO":"TX6P","PLU":"10000101","FLAG":"T","QTY TARGET":"2","QTY AVGSPDNORMAL":"2"},{"KODE TOKO":"TX6P","PLU":"10000102","FLAG":"T","QTY TARGET":"0","QTY AVGSPDNORMAL":"0"},{"KODE TOKO":"TX6P","PLU":"10000126","FLAG":"T","QTY TARGET":"2","QTY AVGSPDNORMAL":"2"}]');
        // $new_testing_data = [];

        // foreach ($testing_data as $key => $value) {
        //     $new_testing_data[] = (array)$value;
        // }
        // $datas =$new_testing_data;

        // ========================================================
        // ============== END TESTING          ====================
        // ========================================================

        // if datas null ,and column header null
        if(!count($datas) && !count($columnHeader)){
            $columnHeader = ["Data Tidak Ditemukan"];
        }
        // if column header null , column header defined by index data from variable datas
        if(!count($columnHeader)){
            foreach ($datas[0] as $key => $value) {
                $columnHeader[]= $key;
            }
        }
        $filename = $storage_path.$filename;
        $headers = [
            "Content-type" => "text/csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        if (!File::isDirectory(storage_path($storage_path))) {
            
            File::makeDirectory(storage_path($storage_path), 0755, true); 
        }
        if (file_exists(storage_path($filename))) {
            
            unlink(storage_path($filename));
        } 

        $file = fopen(storage_path($filename), 'w');
        fputcsv($file, $columnHeader, '|');
        foreach ($datas as $data) {
            fputcsv($file, $data, '|');
        }

        fclose($file);
        return storage_path($filename);
    }

    public function csv_to_array($file = null){
        // if (get_resource_type($file) == 'file' || get_resource_type($file) == 'stream') {

            if (($open = fopen($file, "r")) !== FALSE) {
                while (($data = fgetcsv($open, 0, "|")) !== FALSE) {
                    $datas[] = $data;
                }
                fclose($open);
            }
            $init_index = $datas[0];
            unset($datas[0]);
            $new_data = [];
            $temp_data = [];
    
            foreach ($datas as $key => $value) {
                $temp_data =[];
                // defind new match value and index
                for ($i=0; $i <count($init_index) ; $i++) { 
                    $temp_data [] = [$init_index[$i]=>$value[$i]];
                }
                
                // add to new array
                $new_data[]= call_user_func_array('array_merge', $temp_data);
            }
            return $new_data;
        // }
    }
}