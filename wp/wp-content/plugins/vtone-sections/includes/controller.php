<?php

function vtone_sections_sorter($a, $b) {
    $am = $a->menu_order;
    $bm = $b->menu_order;

    if ($am < $bm) return -1;
    if ($am > $bm) return 1;
    return 0;
}

// Legacy support
add_shortcode('children_as_sections', 'vtone_sections_action');
function vtone_sections_action($atts, $content = '') {
    $id = get_the_ID();
    $children = get_pages(array(
        'parent' => $id,
    ));

    uasort($children, 'vtone_sections_sorter');
    $nav = "<div id=\"child-section-nav-wrap\" class=\"panel-affix2 affix-top hidden-xs hidden-sm\" data-spy=\"affix\" data-offset-top=\"500\">\n";
    $nav .= "<nav id=\"child-section-nav\" class=\"navbar navbar-inverse\" role=\"banner\">\n";
    $nav .= "    <div class=\"container-fluid\">\n";
    $nav .= "        <div class=\"navbar-header\">\n";
    $nav .= "            <button type=\"button\" class=\"navbar-toggle collapsed\" data-toggle=\"collapse\" data-target=\"#navbar-child-section-collapse\">\n";
    $nav .= "                <span class=\"sr-only\">Toggle navigation</span>\n";
    $nav .= "                <span class=\"icon-bar\"></span>\n";
    $nav .= "                <span class=\"icon-bar\"></span>\n";
    $nav .= "                <span class=\"icon-bar\"></span>\n";
    $nav .= "            </button>\n";
    $nav .= "        </div> <!-- end navbar header -->\n";
    $nav .= "        <div class=\"collapse navbar-collapse\" id=\"navbar-child-section-collapse\" aria-expanded=\"false\" role=\"navigation\">\n";
    $nav .= "            <ul class=\"nav navbar-nav\">\n";

    $section_counter = 1;
    $all = '';
    foreach ($children as $post) {
        $target = $post->post_name;

        $bg_image_first = ($section_counter % 2 == 1);

        $section_content = "<section class=\"child-page\" data-target=\"background\" id=\"section-$target\">\n";
        $section_content .= "<a name=\"$target\"></a>\n";
        // add code for div collapse and header
        $section_content .= "<div class=\"row\">\n";

        if ($bg_image_first) {
            $section_content .= "<div class=\"col-md-6 hidden-sm hidden-xs col-height bg-fill-height section-bg-$section_counter\">&nbsp;</div>\n";
        }
        $section_content .= "<div class=\"col-md-6 col-height section-col-wrapper\">\n";
        $section_content .= "<button data-toggle=\"collapse\" data-target=\"#$target\" class=\"section-toggle pull-right hidden-sm hidden-md hidden-lg\" role=\"button\" aria-expanded=\"false\" aria-controls=\"$target\">\n";
        $section_content .= "  <span class=\"sr-only\">Toggle Section</span>\n";
        $section_content .= "  <span class=\"icon-bar\"></span>\n";
        $section_content .= "  <span class=\"icon-bar\"></span>\n";
        $section_content .= "  <span class=\"icon-bar\"></span>\n";
        $section_content .= "</button>\n";
        $section_content .= "<div class=\"section-child-wrap\">\n";
        $section_content .= "<h1>" . $post->post_title . "</h1>\n";
        $section_content .= "<div id=\"$target\" class=\"section-content\">\n";
        $section_content .= do_shortcode($post->post_content);
        $section_content .= "</div> <!-- end section-content -->\n";
        $section_content .= "</div> <!-- end section-child-wrap -->\n";
        $section_content .= "</div> <!-- end section-col-wrapper, column -->\n";
        if (!$bg_image_first) {
            $section_content .= "<div class=\"col-md-6 hidden-sm hidden-xs col-height bg-fill-height section-bg-$section_counter\">&nbsp;</div>\n";
        }
        $section_content .= "</div> <!-- end row -->\n";
        $section_content .= "</section> <!-- end section $target -->\n";

        $all .= $section_content;

        // add the menu bits
        $nav .= "<li role=\"presentation\"><a href=\"#$target\">$post->post_title</a></li>\n";

        $section_counter++;
    }

    $nav .= "</ul>\n";
    $nav .= "</div> <!-- end navbar-collapse -->\n";
    $nav .= "</div> <!-- end container -->\n";
    $nav .= "</nav>\n";
    $nav .= "</div> <!-- end panel -->\n";

    return $nav . $all;
    //return $section_content;
}
