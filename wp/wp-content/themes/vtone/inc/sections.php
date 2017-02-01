<?php

$vtone_sections = array();
$vtone_section_counter = 0;
$vtone_active_section = false;

add_shortcode('section', 'vtone_do_section');
function vtone_do_section($atts, $content = '') {
    global $vtone_section_counter, $vtone_active_section, $vtone_sections;

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
    $content .= "<div class=\"section-child-wrap\">\n";
    if (!$hidetitle) {
        $content .= "<h1>" . $title . "</h1>\n";
    }
    else {
        $content .= "<h1 class=\"visible-xs visible-sm\">" . $title . "</h1>\n";
    }
    $content .= "<div id=\"$anchor\" class=\"section-content\">\n";

    return $content;
}

add_filter('the_content', 'vtone_close_section', 99);
function vtone_close_section($content) {
    global $vtone_section_counter, $vtone_active_section;

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

function vtone_template_event_page_sections($more_link_text=null, $stripteaser=false) {
    global $vtone_sections, $vtone_active_section, $vtone_section_counter;

    echo "<div id=\"child-section-nav-wrap\" class=\"panel-affix affix-top hidden-xs hidden-sm\" data-spy=\"affix\" data-offset-top=\"500\">\n";
    echo "<nav id=\"child-section-nav\" class=\"navbar navbar-inverse\" role=\"banner\">\n";
    echo "    <div class=\"container-fluid\">\n";
    echo "        <div class=\"navbar-header\">\n";
    echo "            <button type=\"button\" class=\"navbar-toggle collapsed\" data-toggle=\"collapse\" data-target=\"#navbar-child-section-collapse\">\n";
    echo "                <span class=\"sr-only\">Toggle navigation</span>\n";
    echo "                <span class=\"icon-bar\"></span>\n";
    echo "                <span class=\"icon-bar\"></span>\n";
    echo "                <span class=\"icon-bar\"></span>\n";
    echo "            </button>\n";
    echo "        </div> <!-- end navbar header -->\n";
    echo "        <div class=\"collapse navbar-collapse\" id=\"navbar-child-section-collapse\" aria-expanded=\"false\" role=\"navigation\">\n";
    echo "            <ul class=\"nav navbar-nav\">\n";

    // Do the shortcodes on the page to build the sections
    $content = apply_filters('the_content', get_the_content($more_link_text, $stripteaser));

    foreach ($vtone_sections as $section) {
        // add the menu bits
        echo "<li role=\"presentation\"><a href=\"#" . $section["anchor"] . "\">" . $section["title"] . "</a></li>\n";
    }
    echo "</ul>\n";
    echo "</div> <!-- end navbar-collapse -->\n";
    echo "</div> <!-- end container -->\n";
    echo "</nav>\n";
    echo "</div> <!-- end panel -->\n";

    echo $content;
}
