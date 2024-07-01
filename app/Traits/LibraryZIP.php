<?php

namespace App\Traits;

use PDF;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use ZipStream\ZipStream;

trait LibraryZIP
{    
    public function make_zip($filename = 'Archive.zip', $path = null, $path_file_zip = [],$pathSave = 'zip_saved/',$password = null){

        $zip = new ZipArchive();
        $zipPath = $pathSave == 'zip_saved/'?storage_path($pathSave . $filename):$pathSave; //
        if (!File::isDirectory(storage_path($zipPath))) {
            
            File::makeDirectory(storage_path($zipPath), 0755, true); 
        } 
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = glob(storage_path($request->storagePath) . '/' . session('userid') .  '/*');
            //add password
            if ($password) {
                $zip->setPassword($passwordZip);
            }
            if (count($path_file_zip)) {
                //add file in array with path
                foreach ($path_file_zip as $key => $data_path) {
                    if (file_exists($data_path)) {
                        $zip->addFile($data_path, basename($data_path)); // example app/data/file.csv
                        $zip->setEncryptionName(basename($data_path), ZipArchive::EM_AES_256);
                       
                    }
                    
                }
            } else {
                //add file  with single path
                if (file_exists($path)) {
                    $zip->addFile($path, basename($path)); // example app/data/file.csv
                    $zip->setEncryptionName(basename($path), ZipArchive::EM_AES_256);
                   
                }
            }
            $zip->close();
            

            return  $zipPath;
        } else{
            return false;
        }

    }

    public function download_zip($filename = 'Archive.zip', $path = null, $path_file_zip = [],$download = false,$pathSave = 'zip_saved/',$password = null){
        $zipPath = $this->make_zip($filename,$path,$pathSave,$password);

        if ($zipPath) {
            return response()->download($zipPath);
        } else {
            return false;
        }
        
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
    
    public function extract_zip($pathFile=null,$pathExtract = "zip_file/"){

        $zipFilePath = $pathFile;
        $extractPath = $pathExtract == "zip_file/"?storage_path($pathExtract):$pathExtract;

        // Create a new ZipArchive instance
        $zip = new ZipArchive;

        // Open the zip file
        if ($zip->open($zipFilePath) === TRUE)
        {
            // Extract the contents to the specified path
            $zip->extractTo($extractPath);

            // Close the zip archive
            $zip->close();

            return "Files extracted successfully to " . $extractPath;
        }else{
            return "Failed to open the zip file.";
        }
    }
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