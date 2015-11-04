<?php
namespace Application\Model;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PdfFormInterface
 *
 * @author Hoang Phan Bao Trung
 */
interface PdfFormInterface {
    public function getError();
    public function setError($error);
    public function __construct($file =   null);
    public function updateContent($data);    
    public static function create($filePath = null);
    public function getPdfFiles();    
    public function getFields();
    
}
