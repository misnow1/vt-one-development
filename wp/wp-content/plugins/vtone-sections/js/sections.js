jQuery(document).ready(function(){
    // cache the window object
    $window = jQuery(window);

    var height = 0;

    jQuery('img#intro-image').each(function(){
        // declare the variable to affect the defined data-type
        var $scroll = jQuery(this);

        jQuery(window).scroll(function() {
            // HTML5 proves useful for helping with creating JS functions!
            // also, negative value because we're scrolling upwards
            var yPos = ($window.scrollTop() / 5) - 30;

            // background position
            var coords = yPos + 'px';

            // move the background
            $scroll.css({ top: coords });

        }); // end window scroll
    });  // end section function

    jQuery('section#intro').height(function() {
        // the amount that the background will have scrolled by once The
        // image is out of view, less the size of the menu bar
        height = jQuery('img#intro-image').height();
        if (height > 500) {
            height = 500;
        }

        jQuery('div#child-section-nav-wrap').attr('data-offset-top', height + 65);
        return height + 'px';
    });

    jQuery('img#intro-image').fadeIn(1500);

    var $isScrolling = {};

    //$scrolldiv = jQuery('div.full-width-page-wrap');
    jQuery('div.bg-fill-height').each(function(){
        // declare the variable to affect the defined data-type
        var $scroll = jQuery(this);
        var $top = $scroll.offset().top;

        // set the bg columns to the same height as the parent
        $scroll.height($scroll.parent().height());

        if ($window.width() >= 992) {
            $window.scroll(function() {
                // move the background
                if ($window.scrollTop() > $top) {
                    if (!$scroll.hasClass('bg-fixed')) {
                        $scroll.addClass('bg-fixed');
                    }
                }
                else {
                    if ($scroll.hasClass('bg-fixed')) {
                        $scroll.removeClass('bg-fixed');
                    }
                }
            }); // end window scroll
        }
    });  // end section function



});
