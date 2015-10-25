<?php
namespace Application\Model;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PdfThumbnails
 *
 * @author Hoang Phan Bao Trung
 */
class PdfThumbnails {
    protected $pdfFileName;
    protected $pdfThumbnailsName;
    
    public function __construct($name, $thumbName = 'pdfThumb.jpg') {
        $this->pdfFileName = $name;
        $this->pdfThumbnailsName = $thumbName;
    }
    
    public function convertToImage(){
        $imagick = new \Imagick();
        $rootPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        
        $pdfThumbnailsPath = $rootPath.'/public/img/';
//        $image->setSize(200,300);
        $imagick->readImage($this->pdfFileName);
        $imagick->writeImage($pdfThumbnailsPath.$this->pdfThumbnailsName);
        
        return $pdfThumbnailsPath.$this->pdfThumbnailsName;
    }
}
