<?php
/**
 * @package WordPress
 * @subpackage vtONE Modularity v3
 */
require_once('functions.php');

get_header();

vtone_logo_menu();

get_template_part( 'blog' );

get_footer();
?>
