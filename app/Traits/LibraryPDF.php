<?php

namespace App\Traits;

use PDF;

trait LibraryPDF
{    
    
    public function printPotraitPDF($data = null, $template = null,$nama_pdf = "pdf-testing.pdf", $download = false)
    {
        if ($template){
            $pdf = PDF::loadview($template, compact(['data']));
            $pdf->output();
            $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);

           
            $canvas = $dompdf ->get_canvas();
            $canvas->page_text(617, 62.75, "{PAGE_NUM} of {PAGE_COUNT}", null, 7, array(0, 0, 0));
            
        } else {
            $pdf = PDF::loadview('PDF.default', compact(['data']));
            $pdf->output();
            $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);

           
            $canvas = $dompdf ->get_canvas();
            $canvas->page_text(617, 30, "{PAGE_NUM} of {PAGE_COUNT}", null, 7, array(0, 0, 0));
            
        }
        if ($download) {
            return $pdf->download($nama_pdf);
        } else {
            return $pdf->stream($nama_pdf); 
        }
        
    }
    public function printLandscapePDF($data = null, $template = null,$nama_pdf = "pdf-testing.pdf", $download = false)
    {
        if ($template){
            $pdf = PDF::loadview($template, compact(['data']));
            $pdf->output();
            $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);

           
            $canvas = $dompdf ->get_canvas();
            $canvas->page_text(617, 62.75, "{PAGE_NUM} of {PAGE_COUNT}", null, 7, array(0, 0, 0));
            
        } else {
            $pdf = PDF::loadview('PDF.default', compact(['data']));
            $pdf->output();
            $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);

           
            $canvas = $dompdf ->get_canvas();
            $canvas->page_text(617, 30, "{PAGE_NUM} of {PAGE_COUNT}", null, 7, array(0, 0, 0));
            
        }
        if ($download) {
            return $pdf->download($nama_pdf);
        } else {
            return $pdf->stream($nama_pdf);
        }
        
    }
}