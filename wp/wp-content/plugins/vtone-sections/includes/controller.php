<?php

function vtone_sections_sorter($a, $b) {
    $am = $a->menu_order;
    $bm = $b->menu_order;

    if ($am < $bm) return -1;
    if ($am > $bm) return 1;
    return 0;
}

add_action('wp_enqueue_scripts', 'vtone_sections_scripts');
function vtone_sections_scripts () {
    wp_enqueue_style('vtone-sections', plugins_url('vtone-sections/css/sections.css'), array(), '20160124.04');
    wp_enqueue_script('sections-js', plugins_url('vtone-sections/js/sections.js'), array('jquery'), '20160124.04');
}

add_filter('the_content', 'vtone_close_section', 99);
function vtone_close_section($content) {
    global $vtone_section_counter, $vtone_active_section, $vtone_sections, $vtone_computing_menu;

    $bg_image_first = ($vtone_section_counter % 2 == 1);
    if ($vtone_active_section) {
        $content .= "</div> <!-- end section-content -->\n";
        $content .= "</div> <!-- end section-child-wrap -->\n";
        $content .= "</div> <!-- end section-col-wrapper, column -->\n";
        if (!$bg_image_first) {
            $content .= "<div class=\"col-md-6 hidden-sm hidden-xs col-height bg-fill-height section-bg-$vtone_section_counter\">&nbsp;</div>\n";
        }
        $content .= "</div> <!-- end row -->\n";
        $content .= "</section>\n";
    }
    return $content;
}

add_shortcode('section', 'vtone_do_section');
$vtone_sections = [];
$vtone_section_counter = 0;
$vtone_active_section = false;
function vtone_do_section($atts, $content = '') {
    global $vtone_section_counter, $vtone_active_section, $vtone_sections, $vtone_computing_menu;

    $anchor = strtolower($atts['anchor']);
    $title = $atts['title'];

    $hidetitle = false;
    if (isset($atts[0]) && $atts[0] == "hidetitle") {
        $hidetitle = true;
    }

    $vtone_sections[] = array(
        "anchor" => $anchor,
        "title" => $title
    );
    if ($vtone_computing_menu) {
        return;
    }

    $bg_image_first = ($vtone_section_counter % 2 == 1);
    if ($vtone_active_section) {
        $content .= "</div> <!-- end section-content -->\n";
        $content .= "</div> <!-- end section-child-wrap -->\n";
        $content .= "</div> <!-- end section-col-wrapper, column -->\n";
        if (!$bg_image_first) {
            $content .= "<div class=\"col-md-6 hidden-sm hidden-xs col-height bg-fill-height section-bg-$vtone_section_counter\">&nbsp;</div>\n";
        }
        $content .= "</div> <!-- end row -->\n";
        $content .= "</section>\n";
    }

    $vtone_section_counter++;
    $bg_image_first = ($vtone_section_counter % 2 == 1);
    $vtone_active_section = true;

    $content .= "<section class=\"child-page\" data-target=\"background\" id=\"section-$anchor\">\n";
    $content .= "<a name=\"$anchor\"></a>\n";
    // add code for div collapse and header
    $content .= "<div class=\"row\">\n";

    if ($bg_image_first) {
        $content .= "<div class=\"col-md-6 hidden-sm hidden-xs col-height bg-fill-height section-bg-$vtone_section_counter\">&nbsp;</div>\n";
    }
    $content .= "<div class=\"col-md-6 col-height section-col-wrapper\">\n";
    $content .= "<button data-toggle=\"collapse\" data-target=\"#$anchor\" class=\"section-toggle pull-right hidden-sm hidden-md hidden-lg\" role=\"button\" aria-expanded=\"false\" aria-controls=\"$anchor\">\n";
    $content .= "  <span class=\"sr-only\">Toggle Section</span>\n";
    $content .= "  <span class=\"icon-bar\"></span>\n";
    $content .= "  <span class=\"icon-bar\"></span>\n";
    $content .= "  <span class=\"icon-bar\"></span>\n";
    $content .= "</button>\n";
    $content .= "<div class=\"section-child-wrap\">\n";
    if (!$hidetitle) {
        $content .= "<h1>" . $title . "</h1>\n";
    }
    $content .= "<div id=\"$anchor\" class=\"section-content\">\n";

    return $content;
}

add_shortcode('section_menu', 'vtone_sections_menu');
$vtone_computing_menu = false;
function vtone_sections_menu($atts, $content = '') {
    global $vtone_sections, $vtone_active_section, $vtone_section_counter, $vtone_computing_menu;

    if ($vtone_computing_menu) {
        return;
    }
    $vtone_computing_menu = true;

    $nav = "<div id=\"child-section-nav-wrap\" class=\"panel-affix affix-top hidden-xs hidden-sm\" data-spy=\"affix\" data-offset-top=\"500\">\n";
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

    // Do the shortcodes on the page to build the sections
    $post = get_post();
    apply_filters( 'the_content', $post->post_content );

    foreach ($vtone_sections as $section) {
        // add the menu bits
        $nav .= "<li role=\"presentation\"><a href=\"#" . $section["anchor"] . "\">" . $section["title"] . "</a></li>\n";
    }
    $nav .= "</ul>\n";
    $nav .= "</div> <!-- end navbar-collapse -->\n";
    $nav .= "</div> <!-- end container -->\n";
    $nav .= "</nav>\n";
    $nav .= "</div> <!-- end panel -->\n";

    $vtone_computing_menu = false;
    return $nav;

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
