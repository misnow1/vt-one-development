<?php
/*
 Plugin Name: vtONE Tabs
 Plugin URI: http://vt-one.org
 Description: This does some cool stuff with tabs
 Author: Michael Snow
 Version: 0.10
 Author URI: http://vt-one.org
*/

add_action('wp_enqueue_scripts','vtone_tabs_enqueue_scripts');

function vtone_tabs_enqueue_scripts () {
	// scripts
	wp_enqueue_script( 'vtone-tabs', plugins_url( 'vtone-tabs/js/vtone-tabs.js' ), array(), '0.10');
	wp_enqueue_script( 'jquery-ui', plugins_url('vtone-tabs/js/jquery-ui-1.10.3.custom.min.js'), array('jquery'), '1.10.3.custom');
	wp_enqueue_script( 'jquery-history.js', plugins_url('vtone-tabs/js/jquery.history.js'), array('jquery'), '1.8b2');
	
	// and styles
	wp_register_style('jquery-ui-smoothness', plugins_url('vtone-tabs/css/smoothness/jquery-ui-1.10.3.custom.min.css'), array(), '1.10.3.custom');
	wp_enqueue_style('jquery-ui-smoothness');
	wp_register_style( 'vtone-tabs', plugins_url( 'vtone-tabs/css/style.css' ), array(), '0.10');
	wp_enqueue_style( 'vtone-tabs' );
}

add_shortcode('tabgroup', 'vtone_tabgroup_shortcode');

$vtoneTabGroupCounter = 0;
$vtoneTabCounter = 0;
$vtoneTabGroupTabs = array();

function vtone_tabgroup_shortcode ($attrs, $content = null) {
	global $vtoneTabGroupCounter, $vtoneTabCounter, $vtoneTabGroupTabs;
	
	// incremement the group counter and reset the tab counter
	$vtoneTabGroupCounter++;
	$vtoneTabCounter = 0;
	
	// process the inner shortcodes
	$c2 = do_shortcode($content);
	
	$str = "<!-- tab group $vtoneTabGroupCounter -->\n";
	$str .= "<div class=\"vtone_tab_wrapper\">\n";
	$str .= "<script>\n";
	$str .= "jQuery(function() {\n";
	$str .= "	jQuery( \"#tabs-$vtoneTabGroupCounter\" ).tabs({\n";
	$str .= "      event: \"mouseover\", heightStyle: \"auto\", hide: true, show: true, ";
	$str .= "      create: function (event, ui) { vtoneTabCreateHandler(event, ui); }, ";
	$str .= "      activate: function (event, ui) { vtoneTabActivateHandler(event, ui) }\n";
	$str .= "	});\n";
	$str .= "});\n";
	$str .= "</script>\n";
	
	$str .= "<div id=\"tabs-$vtoneTabGroupCounter\">\n";
	
	$str .= "\t<ul><!-- tab list -->\n";
	foreach ($vtoneTabGroupTabs[$vtoneTabGroupCounter] as $idx => $vtoneTabName) {
		$str .= "\t\t<li class=\"ui-corner-bottom\"><a href=\"#tabs-$vtoneTabGroupCounter-$idx\">" . $vtoneTabName . "</a></li>\n";
	}
	$str .= "\t</ul><!-- end tab list -->\n";
	
	// process the output of the recursive call to do_shorttags()
	$lines = preg_split("/\n/", $c2);
	
	// remove lines that are just a line break because why not
	$c = count($lines);
	for ($i = 0; $i < $c; $i++) {
		if (rtrim($lines[$i]) == "<br />") {
			unset($lines[$i]);	// blow it away, kids
		}
	}
	
	$str .= join("\n", $lines);
	
	$str .= "</div><!-- close tabgroup $vtoneTabGroupCounter -->\n";
	$str .= "</div><!-- close tab wrapper for $vtoneTabGroupCounter -->\n";
	
	return $str;
}

add_shortcode('tab', 'vtone_tab_shortcode');

function vtone_tab_shortcode ($attrs, $content = null) {
	global $vtoneTabGroupCounter, $vtoneTabCounter, $vtoneTabGroupTabs;
	
	// we expect that the tab content will be in the $content variable. If it's null, just bail out
	if ($content == null) return '';
	
	// add some data to the tabs array
	$vtoneTabName = preg_replace('/^:/', '', $attrs[0]);
	$vtoneTabGroupTabs[$vtoneTabGroupCounter][$vtoneTabCounter] = $vtoneTabName;
	
	// wrap the content in some useful tab magic
	$str = "\t<div id=\"tabs-$vtoneTabGroupCounter-$vtoneTabCounter\">\n";
	
	// split the lines of this section into an array to handle some formatting oddities
	$lines = preg_split('/\n/', $content);
	
	// trim stupid things off the front of the post
	$foundInvalidChar = true;
	while ($foundInvalidChar) {
		$lines[0] = trim($lines[0]);	// strip whitespace from both ends
		if (($lines[0] == "<br />") || ($lines[0] == "</p>") || ($lines[0] == "")) {
			array_shift($lines);
		}
		else {
			$foundInvalidChar = false;
		}
	}
	
	// and trim stupid things off the end
	if (trim($lines[count($lines)]) == "<p>") array_pop($lines);
	if (trim($lines[count($lines)]) == "") array_pop($lines);
	
	// indent each line and look for missing closing <p> tags that the parser likes to add
	$openPCount = 0;
	$c = count ($lines);
	for ($i = 0; $i < $c; $i++) {
		$matches = array();
		$openPCount += preg_match_all('/<p>/', $lines[$i], $matches);
		$openPCount -= preg_match_all('/<\/p>/', $lines[$i], $matches);
		
		$lines[$i] = "\t\t" . $lines[$i];
	}
	
	// look for a stray line break at the end of the tab block
	if ($c > 0) {
		$c--;	// working on the last line of the array

		// remove stray line break
		$lines[$c] = preg_replace('/<br \/>/', '', $lines[$c]);
		
		// and close remaining <p> tags
		if ($openPCount > 0) {
			$lines[$c] .= str_repeat("</p>", $openPCount);
		}
	}
		
	// and put them back together
	$str .= join("\n", $lines);
	
	// woo!
	$str .= "\n\t</div><!-- close tab group $vtoneTabGroupCounter tab $vtoneTabCounter -->\n";
	
	// increment the counter
	$vtoneTabCounter++;

	return $str;
}

function vtone_tabs_get_tabgroups () {
	global $vtoneTabGroupTabs;
	
	return $vtoneTabGroupTabs;
}

function vtone_tabs_reset_tabgroups() {
	global $vtoneTabCounter, $vtoneTabGroupCounter, $vtoneTabGroupTabs;
	
	$vtoneTabCounter = 0;
	$vtoneTabGroupCounter = 0;
	$vtoneTabGroupTabs = array();
}
