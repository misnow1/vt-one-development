<?php
/**
 * @package WordPress
 * @subpackage vtONE Modularity v3
 */

/*
 * The main nav menu in the bootstrap style
 */

function vtone_logo_menu ($themeLocation = 'main_nav_menu') {
	?>
    <nav id="navbar-top" class="navbar navbar-default" role="banner">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?php bloginfo('url') ?>">
                    <img src="<?php echo get_template_directory_uri() ?>/images/vtone_logo_tag.png" alt="vtone logo">
                </a>
            </div> <!-- end navbar header -->

            <div class="collapse navbar-collapse" id="navbar-collapse" aria-expanded="false" role="navigation">
                <ul class="nav navbar-nav navbar-right">
        			<?php
						if (is_front_page()) {
							wp_nav_menu( array('fallback_cb' => '',
				            	'theme_location' => $themeLocation,
				            	'container' => false,
				            	'depth' => 2,
				            	'menu_class' => 'nav navbar-nav navbar-right',
				            	'walker' => new vtone_bootstrap_menu(),
							));
						}
						else {
							GetSubpageMenu();
						}
					?>
                </ul>
    		</div> <!-- end navbar-collapse -->
		</div> <!-- end container -->
    </nav>
	<?php
}
