<?php

namespace App\Traits;

use PDF;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use ZipStream\ZipStream;

trait LibraryZIP
{    

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
    public function make_zip($filename = 'Archive.zip', $path = null, $path_file_zip = [],$download = false){
        $zip = new ZipArchive;
        $file_saved = storage_path($path.$filename);
        if ($zip->open(storage_path($path.$filename), ZipArchive::CREATE) === TRUE) {
            $filesToZip = $path_file_zip;

            foreach ($filesToZip as $file) {
                $zip->addFile($file, basename($file));
            }

            if ($download) {
                return response()->download($path)->deleteFileAfterSend(true);
            }
            return $file_saved;
        }
        return false;
    }
    public function read_zip($path_zip = null){
        $zip = new ZipArchive;
       if( $zip->open($path_zip, ZipArchive::RDONLY) !== TRUE ){
           for ($i = 0; $i < $zip->numFiles; $i++) {
               // Get the name of the file in the zip archive
               $filename = $zip->getNameIndex($i);
       
               // Read the contents of the file
               $fileContents[$filename] = $zip->getFromIndex($i);
           }
       
           // Close the zip file
           $zip->close();
       
           return $fileContents;
       }
       return false;
    }
    // public function extract_zip(){
        
    // }
    public function encrypt_zip($path_zip = null){
        // $zip = new ZipArchive;
        // if($zip->open($path_zip)){
        //     $encrypt = $zip->getStream('Detail');
        //     $zip->close();
        //     return $encrypt;
        // }// Path to the ZIP file
        $zipFilePath = $path_zip;
        // dd($path_zip);
        // Create a new ZipArchive instance
        $zip = new ZipArchive();

        // Open the ZIP file for reading
        if ($zip->open($zipFilePath) === true) {
            // Name of the file within the ZIP archive that you want to access
            $fileNameInZip = '2009021701_GSM.CSV';

            // Get a stream of the specified file within the ZIP archive
            $fileStream = $zip->getStream($fileNameInZip);

            if ($fileStream !== false) {
                // Read the contents of the file stream
                while (!feof($fileStream)) {
                    echo fread($fileStream, 1024); // Read and output data from the file stream
                }
                
                // Close the file stream
                fclose($fileStream);
            } else {
                echo "Failed to open the file inside the ZIP archive.";
            }

            // Close the ZIP archive
            $zip->close();
        } else {
            echo "Failed to open the ZIP archive.";
        }
        

    }
}