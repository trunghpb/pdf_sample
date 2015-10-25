<?php
namespace Application\Model;

use DOMXPath;
use mikehaertl\pdftk\Pdf;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of pdfHelper
 *
 * @author Hoang Phan Bao Trung
 */
class PdfHelper {
    protected $pdf;
    protected $metadataDOM;
    protected $filePath;
    public function __construct($filePath) {
        if ($filePath && file_exists($filePath)){
            $this->filePath = $filePath;
//            $this->pdf = new \ZendPdf\PdfParser\StructureParser;
            
//            $this->pdf = PdfDocument::load($filePath);
//            $this->metadataDOM = new \DOMDocument();
//            $this->pdf->pages[0]->
//            $this->metadataDOM->loadXML($this->pdf->));                    
        }
    }
    
    public function test(){
        // Fill form with data array
        $pdf = new Pdf('/var/www/zf/data/fileupload/bbbb_form_new.pdf');
        $pdf->fillForm(array('title'=>'abcdef'))
            ->needAppearances();
        if ($pdf->saveAs('/var/www/zf/data/fileupload/filled.pdf')){
            return true;
        }else{
            return $pdf->getError();
        }

    }

    public function changeText($search, $newText){
        $xpath = new DOMXPath($this->metadataDOM);
        $search = "[Report Title]"; 
        $searchedNode = $xpath->query("//path[text()=".$search."\"]"); 
        $searchedNode->nodeValue = "$newText";
        return $this;
    }
    
    public function save(){
        $this->pdf->setMetadata($this->metadataDOM->saveXML());
        $this->pdf->save($this->filePath.'.pdf');
    }
}
