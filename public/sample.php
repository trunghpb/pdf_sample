<?php 
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
$searchpath = '';
$pdfinput   = "demo.pdf";
$docoptlist = "requiredmode=minimum";
$fontdir = '/var/www/zf/reference/fonts';

try{
    $p = new PDFlib();
    /* 新規のドキュメントの作成 */
    if ($p->begin_document('', "") == 0)
        throw new Exception("Error: " . $p->get_errmsg());

    $p->set_parameter( "textformat", "utf8");
    $p->set_parameter( "FontOutline", "ArialUnicode=$fontdir/ARIALUNI.TTF");
    $p->set_parameter( "FontOutline", "ArialItalic=$fontdir/ariali.ttf");
    
    $utfstring = "既存PDFペ";

    /* PDFlibブロックを含む既存文書を開く */
    $indoc = $p->open_pdi_document($searchpath.$pdfinput, "");
    if ($indoc == 0)
        throw new Exception("Error: " . $p->get_errmsg());

    /* 既存文書の1ページ目を開く */
    $inpage = $p->open_pdi_page($indoc, 1, "");
    if ($inpage == 0)
        throw new Exception("Error: " . $p->get_errmsg());


    $width  = $p->pcos_get_number($indoc, "pages[0]/width");
    $height = $p->pcos_get_number($indoc, "pages[0]/height");

    
    /* 出力PDFページを既存文書のサイズで作成する */
    $p->begin_page_ext($width, $height, "");

    /* 既存PDFページを出力PDFページ上に配置する */
    $p->fit_pdi_page($inpage, 0, 0, "");

    $font = $p->load_font("ArialUnicode", "unicode", "");
    if ($font == 0) {
       die("Error: " . $p->get_errmsg());
    }
    $p->setfont($font, 10.0);
    
    // 座標の取得
    $count = $p->pcos_get_number($indoc, "length:fields");      
    for( $i=0 ; $i < $count; $i++){
        $id_name = $p->pcos_get_string($indoc,'fields['.$i.']/T');
        $x1 = $p->pcos_get_number($indoc,'fields['.$i.']/Rect[0]');
        $y1 = $p->pcos_get_number($indoc,'fields['.$i.']/Rect[1]');
        $x2 = $p->pcos_get_number($indoc,'fields['.$i.']/Rect[2]');
        $y2 = $p->pcos_get_number($indoc,'fields['.$i.']/Rect[3]'); 
        //fontname 
        $field_type = $p->pcos_get_string($indoc,'fields['.$i.']/FT');
        $fontname = $p->pcos_get_string($indoc,'fields['.$i.']/Q');
        
        if ($field_type == 'Tx'){
            $value = $p->pcos_get_string($indoc,'fields['.$i.']/V');  
            $optlist = "fontname=ArialUnicode fontsize=10.0 encoding=unicode ";
            $tf = false;
            $tf = $p->add_textflow($tf, $value, $optlist);
            $result = $p->fit_textflow($tf, $x1, $y1, $x2, $y2, "");
            if ($result != '_stop'){
                $fontsize = 8;
                while ($result == '_boxempty' && --$fontsize){
                    $optlist_nini = "fontname=ArialUnicode fontsize={$fontsize}.0 encoding=unicode ";
                    $tf = false;
                    $tf = $p->add_textflow($tf, $value, $optlist_nini);
                    $result = $p->fit_textflow($tf, $x1, $y1, $x2, $y2, "");
                }
            } 
//            $p->show_xy($result, $x1, $y1);
        }
        
    }
   
    $p->end_page_ext("");

    $p->close_pdi_page($inpage);

    $p->end_document("");
    $p->close_pdi_document($indoc);

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=fill_converted_formfields.pdf");
    print $buf;


}catch(pCOSException $e){
    die("PDFlib pCOS exception occurred in dumper sample:\n" .
    "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
    $e->get_errmsg() . "\n");
}catch (Exception $e) {
      die($e);
}
