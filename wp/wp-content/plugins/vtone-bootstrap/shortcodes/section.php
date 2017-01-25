<?php


class VtOnePageSection {
    var $name = '';
    var $headline = '';
    var $content = '';

    function VtOnePageSection($name, $headline) {
        $this->name = $name;
        $this->headline = $headline;
    }
}

$vtoneSectionCounter = 0;
$vtonePageSections = array();

//add_shortcode('section', 'vtone_section_shortcode');

function vtone_section_shortcode ($attrs, $content = null) {
	global $vtonePageCounter, $vtoneSectionCounter, $vtonePageSections;

	// we expect that the section content will be in the $content variable. If it's null, just bail out
	if ($content == null) return '';

    // set some defaults
    $attrs = shortcode_atts(
        array(
            'name' => $vtoneSectionCounter,
            'headline' => '',
        ), $attrs, 'section' );

    $sectionName = $attrs['name'];

    if (array_key_exists($sectionName, $vtonePageSections)) {
        $section = $vtonePageSections[$sectionName];
    }
    else {
        // the output string
        $str = '';

        $section = new VtOnePageSection($sectionName, $attrs['headline']);

        // wrap the content in some useful section magic
        $class = "";
        if ($vtoneSectionCounter == 0) {
            $attributes = 'id="intro" data-speed="6" data-type="background"';
        }
        elseif ($vtoneSectionCounter % 2 == 1) {
            $attributes = '';
        }
        else {
            $attributes = 'class="lt-gray" data-speed="6" data-type="background"';
        }

        $str .= "<div class=\"section-container\" id=\"$sectionName\">\r\n";
        $str .= "  <section $attributes>\r\n";
        $str .= "    <div class=\"container\">\r\n";

        // add some data to the sections array
        if ($attrs['name'] != '') {
            $vtoneSectionName = $attrs['name'];
            $vtonePageSections[$vtoneSectionCounter] = $vtoneSectionName;
            $divid = $vtoneSectionName;
            $str .= "      <a name=\"$vtoneSectionName\"></a>\r\n";
        }

        // add code for div collapse and header
        $target = '';
        if ($attrs['headline'] != '') {
            if ($vtoneSectionName && $vtoneSectionCounter != 0) {
                $target = "filter-$vtoneSectionName";
                $str .= "      <button type=\"button\" data-toggle=\"collapse\" data-target=\"#$target\" class=\"hidden-sm hidden-md hidden-lg section-toggle collapsed\">\n";
                $str .= "        <span class=\"sr-only\">Toggle Section</span>\n";
                $str .= "        <span class=\"icon-bar\"></span>\n";
                $str .= "        <span class=\"icon-bar\"></span>\n";
                $str .= "        <span class=\"icon-bar\"></span>\n";
                $str .= "      </button>";
            }
            $str .= "      <h1 class=\"section-header\">" . $attrs['headline'] . "</h1>\r\n";
        }

        // process the actual content through the shortcode filter
        if ($target) {
            $str .= "    <div id=\"$target\" class=\"section-content-wrapper\">\r\n";
        }
        $str .= do_shortcode($content);
        $str .= "<div class=\"target-top-wrapper pull-right\"><a href=\"#\">top</a></div>\n";
        if ($target) {
            $str .= "    </div> <!-- end content wrapper -->\r\n";
        }

        $str .= "    </div> <!-- end container -->\r\n";
        $str .= "  </section><!-- close section $vtoneSectionCounter -->\r\n";
        $str .= "</div> <!-- end section wrapper -->\r\n";

        // increment the counter
        $vtoneSectionCounter++;

        // cache the content
        $section->content = $str;
        $vtonePageSections[$sectionName] = $section;
    }

    return $section->content;

}

function vtone_sections_get_sections () {
	global $vtonePageSections;
	return $vtonePageSections;
}

function vtone_sections_reset() {
	global $vtoneSectionCounter, $vtonePageSections;

	//$vtoneSectionCounter = 0;
	//$vtonePageSections = array();
}
