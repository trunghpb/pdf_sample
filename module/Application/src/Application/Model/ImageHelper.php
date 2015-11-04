<?php
namespace Application\Model;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FileHelper
 *
 * @author Hoang Phan Bao Trung
 */
class ImageHelper {
    protected $imageDir;
    protected $imageFile;


    public function __construct($file = null) {
        $this->imageDir = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data/images/';
        if ($file) {
            $this->imageFile = $file;
        }
    }
    
    public function getImageFiles() {
        return array_values(array_diff(scandir($this->imageDir), ['..', '.','.DS_Store']));
    }
}
