<?php

add_shortcode('panel', 'vtone_panel_shortcode');
function vtone_panel_shortcode($attrs, $content=null) {

    // set some defaults
    $attrs = shortcode_atts(
        array(
            'width' => 4,
            'title' => '',
        ), $attrs, 'section' );


    $width = $attrs['width'];
    $title = $attrs['title'];

    $str = "<div class=\"col-md-$width\">\r\n<div class=\"panel panel-default\">\r\n";
    if ($title != '') {
        $str .= "<div class=\"panel-heading\">\r\n";
        $str .= "<h2 class=\"panel-title\">$title</h2>\r\n";
        $str .= "</div> <!-- end panel-heading -->\r\n";
    }
    $str .= "<div class=\"panel-body\">";
    $str .= wpautop($content);
    $str .= "</div> <!-- end panel-body -->\r\n";
    $str .= "</div> <!-- end panel -->\r\n";
    $str .= "</div> <!-- end column -->\r\n";

    return $str;
}
