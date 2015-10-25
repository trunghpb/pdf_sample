<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Form;

use Zend\Form\Element\File;
use Zend\Form\Form;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;

/**
 * Description of UploadForm
 *
 * @author Hoang Phan Bao Trung
 */
class UploadForm extends Form{
    public function __construct($name = null, $options = []) {
        parent::__construct($name, $options);
        $this->addElements();
        $this->addInputFilter();
    }
    
    public function addElements(){
        // File input
        $file = new File('pdf-file');
        $file->setLabel('Pdf file upload')
            ->setAttribute('id', 'pdf-file')
//            ->setAttribute('multiple', true)
       ;
        $this->add($file);
    }
    
    public function addInputFilter(){
        $inputFilter = new InputFilter();
        $fileInput = new FileInput('pdf-file');
        $basePath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        $fileInput->setRequired(true);
        
        $fileInput->getValidatorChain()
            ->attachByName('filesize', ['max' => 1024 * 1024 * 5000])
//            ->attachByName('filemimetype', ['mimeType' => 'application/pdf'])
        ;
        
        $fileInput->getFilterChain()->attachByName(
            'filerenameupload',
                [
                    'target' => $basePath.'/data/fileupload/robust.pdf',
                    'randomize' =>true
                ]
        );
        
        $inputFilter->add($fileInput);
        $this->setInputFilter($inputFilter);
    }
}
