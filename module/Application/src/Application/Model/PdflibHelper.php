<?php

namespace Application\Model;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PdflibHelper
 *
 * @author Hoang Phan Bao Trung
 */
class PdflibHelper implements PdfFormInterface {

    protected $pdf;
    protected $pdfFile;
    protected $uploadDir;
    protected $error;
    static $_self;
    protected $fontFile;
    protected $supportedFont = [];

    const FONT_ARIAL = 'Arial';
    const FONT_RYUMIN = 'Ryumin';
    const FONT_GOTHIC = 'Gothic';
    const FONT_MIDASHI = 'Midashi';
    const FONT_FUTO = 'Futo';
    
    public function __construct($file = null) {
        $this->uploadDir = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data/fileupload/';
        if ($file) {
            $this->pdfFile = $file;
        }
        $this->fontFile = 'ARIALUNI.TTF';// default font
        
        $this->supportedFont = [
            self::FONT_ARIAL => 'ARIALUNI.TTF', 
            self::FONT_RYUMIN => 'A-OTF-RyuminPro-Medium.otf',
            self::FONT_GOTHIC => 'A-OTF-GothicMB101Pro-Medium.otf',
            self::FONT_MIDASHI => 'A-OTF-MidashiGoPro-MB31.otf',
            self::FONT_FUTO =>'A-OTF-FutoGoB101Pro-Bold.otf',
        ];
    }

    public function getError() {
        return $this->error;
    }
    
    public function setFont($fontname){
        
        if (array_key_exists($fontname, $this->supportedFont)){
            $this->fontFile = $this->supportedFont[$fontname];
        }        
        return $this;
    }

    public function getFields() {
        $resultset = [];
        $resultsetCount = 0;
        $p = new \PDFlib();
        /* start using pdflib document */
        if ($p->begin_document('', "") == 0) {
            return $this->setError($p->get_errmsg());
        }

        /* Open a document */
        $indoc = $p->open_pdi_document($this->uploadDir . $this->pdfFile, "");
        if ($indoc == 0)
            return $this->setError($p->get_errmsg());

        /* Open a page */
        $inpage = $p->open_pdi_page($indoc, 1, "");
        if ($inpage == 0)
            return $this->setError($p->get_errmsg());

        $width = $p->pcos_get_number($indoc, "pages[0]/width");
        $height = $p->pcos_get_number($indoc, "pages[0]/height");

        $p->begin_page_ext($width, $height, "");
        $p->fit_pdi_page($inpage, 0, 0, "");

        // List field
        $count = $p->pcos_get_number($indoc, "length:fields");
        for ($i = 0; $i < $count; $i++) {
            $id_name = $p->pcos_get_string($indoc, 'fields[' . $i . ']/T');
            $field_type = $p->pcos_get_string($indoc, 'fields[' . $i . ']/FT');
            
            if ($field_type == 'Tx') { 
                try {
                    $value = $p->pcos_get_string($indoc, 'fields[' . $i . ']/V');
                } catch (\PDFlibException $pdfex) {
                    $value = "";
                } catch (Exception $ex){
                    $value = "";
                }           
                $resultset[$resultsetCount]['FieldName'] = $id_name;
                $resultset[$resultsetCount]['FieldType'] = 'Text';
                if ($value){
                    $resultset[$resultsetCount]['FieldValue'] = $value;
                }
                $resultsetCount++;
            }
        }

        // close page
        $p->end_page_ext("");
        $p->close_pdi_page($inpage);

        // close document
        $p->end_document("");
        $p->close_pdi_document($indoc);
        return $resultset;
    }

    public function getPdfFiles() {
        return array_values(array_diff(scandir($this->uploadDir), ['..', '.','.DS_Store']));
    }

    public function setError($error) {
        $this->error .= $error;
        return null;
    }

    public function updateContent($data) {

        $p = new \PDFlib();
        /* start using pdflib document */
        if ($p->begin_document('', "") == 0) {
            return $this->setError($p->get_errmsg());
        }
        $fontdir = '/var/www/zf/reference/fonts';
        $p->set_option("textformat=utf8");
        $p->set_option("FontOutline={ArialUnicode=$fontdir/$this->fontFile}");
//        $p->set_option("FontOutline={ArialItalic=$fontdir/ariali.ttf}");

        /* Open a document */
        $indoc = $p->open_pdi_document($this->uploadDir . $this->pdfFile, "");
        if ($indoc == 0)
            return $this->setError($p->get_errmsg());

        /* Open a page */
        $inpage = $p->open_pdi_page($indoc, 1, "");
        if ($inpage == 0)
            return $this->setError($p->get_errmsg());

        $width = $p->pcos_get_number($indoc, "pages[0]/width");
        $height = $p->pcos_get_number($indoc, "pages[0]/height");

        $p->begin_page_ext($width, $height, "");
        $p->fit_pdi_page($inpage, 0, 0, "");

        $font = $p->load_font("ArialUnicode", "unicode", "");
        if ($font == 0) {
            return $this->setError($p->get_errmsg());
        }
        $p->setfont($font, 10.0);

        // List field
        $count = $p->pcos_get_number($indoc, "length:fields");

        for ($i = 0; $i < $count; $i++) {
            $id_name = $p->pcos_get_string($indoc, 'fields[' . $i . ']/T');
            $field_type = $p->pcos_get_string($indoc, 'fields[' . $i . ']/FT');
            $x1 = $p->pcos_get_number($indoc, 'fields[' . $i . ']/Rect[0]');
            $y1 = $p->pcos_get_number($indoc, 'fields[' . $i . ']/Rect[1]');
            $x2 = $p->pcos_get_number($indoc, 'fields[' . $i . ']/Rect[2]');
            $y2 = $p->pcos_get_number($indoc, 'fields[' . $i . ']/Rect[3]');

            if ($field_type == 'Tx') {
                $value = $data[$id_name];
                $optlist = "fontname=ArialUnicode fontsize=10.0 encoding=unicode ";
                $tf = false;

                $tf = false;
                $tf = $p->add_textflow($tf, $value, $optlist);
                $result = $p->fit_textflow($tf, $x1, $y1, $x2, $y2, "blind");
                if ($result != '_stop') {
                    if ($result == '_boxempty') {
                        $fontsize = 8;
                        while ($result == '_boxempty' && --$fontsize) {
                            $optlist_nini = "fontname=ArialUnicode fontsize={$fontsize}.0 encoding=unicode ";
                            $tf = false;
                            $tf = $p->add_textflow($tf, $value, $optlist_nini);
                            $result = $p->fit_textflow($tf, $x1, $y1, $x2, $y2, "");
                        }
                    } elseif ($result == '_boxfull') {
                        $p->delete_textflow($tf);
                        $optlist_scaling = "fontname=ArialUnicode fontsize=10.0 encoding=unicode horizscaling=40%";
                        $tf = false;
                        $tf = $p->add_textflow($tf, $value, $optlist_scaling);
                        $result = $p->fit_textflow($tf, $x1, $y1, $x2, $y2, "");
                    }
                } else {
                    $result = $p->fit_textflow($tf, $x1, $y1, $x2, $y2, "rewind=-1");
                }
            }
        }

        // close page
        $p->end_page_ext("");
        $p->close_pdi_page($inpage);

        // close document
        $p->end_document("");
        $p->close_pdi_document($indoc);

        $buf = $p->get_buffer();

        if (file_put_contents(dirname($this->uploadDir) . '/filled/' . $this->pdfFile . '.filled.pdf', $buf)) {
            return true;
        } else {
            $this->setError($pdf->getError());
            return false;
        }
    }

    public static function create($file = null) {
        if (!self::$_self) {
            return new self($file);
        } else {
            return self::$_self;
        }
    }

}
