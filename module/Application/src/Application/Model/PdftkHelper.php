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
class PdftkHelper implements PdfFormInterface{
    protected $pdf;
    protected $metadataDOM;
    protected $pdfFile;
    protected $uploadDir;
    protected $error;
    static $_self;


    public function getError() {
        return $this->error;
    }

    public function setError($error) {
        $this->error = $error;
        return $this;
    }

    public function __construct($file =   null) {
        $this->uploadDir = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/data/fileupload/';
        if ($file){
            $this->pdfFile = $file ;
            $this->pdf = new Pdf($this->uploadDir.$this->pdfFile);
        }
        
    }

    public function updateContent($data){
        $this->pdf->fillForm($data)
            ->needAppearances()
//            ->allow('Printing')
            ;
        if ($this->pdf->saveAs(dirname($this->uploadDir).'/filled/'.$this->pdfFile.'.filled.pdf')){
            return true;
        }else{
            $this->setError($pdf->getError());
            return false;
        }
    }
    
    public static function create($filePath = null) {
        if (!self::$_self){
            return new self($filePath);
        } else { return self::$_self;}
    }

    public function getPdfFiles() {
        return array_values(array_diff(scandir($this->uploadDir), ['..', '.','.DS_Store']));
    }
    
    public function getFields(){
        $fieldList = [];
        $fieldListString = $this->pdf->getDataFields();
        $dataFieldArray = explode('---', $fieldListString);
        unset($dataFieldArray[0]);
        foreach ($dataFieldArray as $key => $fieldString){
            $fieldArray = explode("\n", $fieldString);
            unset($fieldArray[0]);
            unset($fieldArray[count($fieldArray)]);
            foreach ($fieldArray as $valueString){
                $value = explode(':', $valueString);
                $fieldList[$key][$value[0]] = trim($value[1]);
            }
        }
        
        return $fieldList;
    }

}
