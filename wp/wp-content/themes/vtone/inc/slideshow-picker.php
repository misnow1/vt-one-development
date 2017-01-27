<?php
add_action( 'admin_init', 'vtone_modularity_lite_theme_options_init' );
add_action( 'admin_menu', 'vtone_modularity_lite_theme_options_add_page' );

function vtone_modularity_lite_theme_options_init (){
    /*
     * Process the POST if there is one
     */
    if (isset($_POST['vtone-ml-theme-opts-submit'])) {
        check_admin_referer('vtone-modularity-list-theme-opts-save');
        if (isset($_POST['vtone-ml-theme-opts-ss'])) {
            $a = LoadImagePostArray($_POST['vtone-ml-theme-opts-ss']);
            $serializedImageArray = serialize($a);
            update_option('vtone-modularity-lite-slideshow-images', $serializedImageArray);
        }
        else {
            // the image array isn't present, so blow it away
            update_option('vtone-modularity-lite-slideshow-images', '');
        }

        $eventImage = '';
        $eventLink = '';
        if (isset($_POST["vtone-ml-theme-opts-event-image"])) {
            $eventImage = trim($_POST["vtone-ml-theme-opts-event-image"]);
        }
        if (isset($_POST["vtone-ml-theme-opts-event-link"])) {
            $eventLink = trim($_POST["vtone-ml-theme-opts-event-link"]);
        }
        if ($eventLink && $eventImage) {
            update_option('vtone-theme-event-image', $eventImage);
            update_option('vtone-theme-event-link', $eventLink);
        }
        else {
            delete_option('vtone-theme-event-link');
            delete_option('vtone-theme-event-image');
        }
    }
}

function vtone_modularity_lite_theme_options_add_page () {
    add_theme_page("Theme Slideshow Options", "Slideshow", 'edit_theme_options', 'vtone_modularity_ss_options', 'vtone_modularity_lite_theme_options_get_page');
}

function vtone_modularity_lite_theme_options_get_page () {
    include(get_template_directory() . '/inc/theme-options-page.php');
}

add_action('admin_enqueue_scripts', 'vtone_modularity_lite_theme_options_scripts');
function vtone_modularity_lite_theme_options_scripts ($pageName) {
    if ($pageName == "appearance_page_vtone_modularity_ss_options") {
        wp_enqueue_media();
        wp_enqueue_script('vtone-modularity-lite-admin', get_template_directory_uri() . '/js/admin.js', array('jquery'), '20160124.01');

        wp_enqueue_script('bootstrapjs', get_template_directory_uri() . '/bootstrap/js/bootstrap.min.js', array('jquery'), '3.3.7');
        wp_enqueue_style('bootstrap', get_template_directory_uri() . '/bootstrap/css/bootstrap.min.css', array(), '3.3.7');
        wp_enqueue_style('bootstrap-theme', get_template_directory_uri() . '/bootstrap/css/bootstrap-theme.min.css', array('bootstrap'), '3.3.7');

    }
}

/**
 * Loads images from a post array
 * @param unknown $images
 */
function LoadImagePostArray ($images) {
    if (!is_array($images)) return false;

    $a = array();

    foreach ($images as $key => $img) {
        $imgObj = new SlideshowImage();
        $imgObj->enabled = ($img['enabled'] == 'on');
        $imgObj->href = $img['href'];
        $imgObj->order = $img['order'];
        $imgObj->url = $img['imgurl'];

        $a[$key] = $imgObj;
    }

    return $a;
}

/**
 * Helper function to sort slideshow images based on db order
 * @param unknown $a
 * @param unknown $b
 * @return number
 */
function slideshowSorter ($a, $b) {
    if ($a->order == $b->order) return 0;
    return ($a->order < $b->order) ? -1 : 1;
}

class SlideshowImage {
    var $order = 0;
    var $url = '';
    var $href = '';
    var $enabled = true;
}
