<?php

add_action('wp_enqueue_scripts', 'vtobs_enqueue_scripts');

function vtobs_enqueue_scripts () {
	/*
     * Bootstrap!
     */
	wp_register_script('bootstrapjs', plugins_url('/vtone-bootstrap/bootstrap/js/bootstrap.min.js'), array('jquery'), '3.2.2');

    wp_register_style('bootstrap', plugins_url('/vtone-bootstrap/bootstrap/css/bootstrap.min.css'), array(), '3.2.2');
    wp_register_style('bootstrap-theme', plugins_url('/vtone-bootstrap/bootstrap/css/bootstrap-theme.min.css'), array('bootstrap'), '3.2.2');
}

//add_filter('the_content', 'vtone_fix_shorttag_breaks', 9);

function vtone_fix_shorttag_breaks($content) {
    $content = preg_replace('/\]\r\n/', ']', $content);
    return $content;
}

//add_filter('the_content', 'vtone_fix_empty_pee', 99);

function vtone_fix_empty_pee ($content) {
    // TODO: Don't blindly strip opening and closing p tags
    $content = preg_replace('/^<p>/', '', $content);
    $content = preg_replace('/<\/p>$/', '', $content);

    return $content;
}
