<?php

add_shortcode('row', 'vtone_row_shortcode');
function vtone_row_shortcode($attrs, $content=null) {
    $str = "<div class=\"row\">";

    $c = do_shortcode($content);
    $str .= $c;

    $str .= "</div> <!-- end row -->\r\n";

    return $str;
}
