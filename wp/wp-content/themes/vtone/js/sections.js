jQuery(document).ready(function(){
    // cache the window object
    $window = jQuery(window);

    var height = 0;
    var verticalOffset = 0;
    var scrollSpeed = 5;

    jQuery('img#intro-image').each(function(){
        // declare the variable to affect the defined data-type
        var $scroll = jQuery(this);
        var $menu = jQuery('div#child-section-nav-wrap');

        jQuery(window).scroll(function() {
            // HTML5 proves useful for helping with creating JS functions!
            // also, negative value because we're scrolling upwards
            var yPos = ($window.scrollTop() / scrollSpeed) + verticalOffset;

            // background position
            var coords = yPos + 'px';

            // move the background
            $scroll.css({ top: coords });

        }); // end window scroll
    });  // end section function

    jQuery('div#child-section-nav-wrap').on('affix.bs.affix', function () {
        jQuery(this).css({ "margin-top": "" });
    })

    jQuery('div#child-section-nav-wrap').on('affixed-top.bs.affix', function () {
        jQuery(this).css({ "margin-top": height + "px" });
    })

    jQuery('section#intro').height(function() {
        var imgHeight = jQuery('img#intro-image').height();
        verticalOffset = Math.floor((imgHeight - 60) / scrollSpeed / -2);
        verticalOffset = Math.floor(60 / scrollSpeed / -1);
        height = imgHeight + verticalOffset * 2;

        //console.log("Image Height: " + imgHeight);
        //console.log("Vertical Offset: " + verticalOffset);
        //console.log("Section Height: " + height + "px");

        jQuery('img#intro-image').css({ top: verticalOffset + "px" });
        jQuery('div#child-section-nav-wrap').attr('data-offset-top', height + 65);
        jQuery('div#child-section-nav-wrap').css({ "margin-top": height + "px" });
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
