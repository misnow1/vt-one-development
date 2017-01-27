var height = 0;
var verticalOffset = 0;
var scrollSpeed = 5;

function handleWindowResize() {
    var imgHeight = jQuery('img#intro-image').height();
    verticalOffset = Math.floor(-60 / scrollSpeed);
    height = imgHeight + verticalOffset * 2;

    //console.log("Image Height: " + imgHeight);
    //console.log("Vertical Offset: " + verticalOffset);
    //console.log("Section Height: " + height + "px");

    jQuery('img#intro-image').css({ top: verticalOffset + "px" });
    jQuery('div#child-section-nav-wrap').attr('data-offset-top', height + 65);
    jQuery('div#child-section-nav-wrap').css({ "margin-top": height + "px" });
    jQuery('section#intro').height(height + "px");

    jQuery('div.bg-fill-height').each(function() {
        var $thing = jQuery(this);
        $thing.height($thing.parent().height());
    });
}

jQuery(document).ready(function(){
    // cache the window object
    $window = jQuery(window);

    /*
     * Go ahead and resize the things we can, otherwise the window looks
     * really odd until load finishes.
     */
    handleWindowResize();

    jQuery('img#intro-image').each(function(){
        // declare the variable to affect the defined data-type
        var $scroll = jQuery(this);
        var $menu = jQuery('div#child-section-nav-wrap');

        jQuery(window).scroll(function() {
            var yPos = ($window.scrollTop() / scrollSpeed) + verticalOffset;

            // background position
            var coords = yPos + 'px';
            $scroll.css({ top: coords });

        }); // end window scroll
    });  // end section function

    jQuery('div#child-section-nav-wrap').on('affix.bs.affix', function () {
        jQuery(this).css({ "margin-top": "" });
    })

    jQuery('div#child-section-nav-wrap').on('affixed-top.bs.affix', function () {
        jQuery(this).css({ "margin-top": height + "px" });
    })

    jQuery(window).on('load', function() {
        handleWindowResize();
        jQuery('img#intro-image').fadeIn(1500);
    });

    jQuery(window).on('resize', function() {
        handleWindowResize();
    });

    /*
     * Function to "lock" the section backgrounds when they finish scrolling into view
     */
    jQuery(window).on('scroll', function() {
        if ($window.width() < 992) {
            return;
        }

        jQuery('div.bg-fill-height').each(function(){
            var windowTop = $window.scrollTop();
            var div = jQuery(this);
            var divTop = div.offset().top;
            if (windowTop >= divTop && !div.hasClass('bg-fixed')) {
                console.log("Binding div with top " + divTop + "px to window at scroll position " + windowTop + "px");
                div.addClass('bg-fixed');
            }
            else if (windowTop < divTop && div.hasClass('bg-fixed')) {
                console.log("Unbinding div with top " + divTop + "px to window at scroll position " + windowTop + "px");
                div.removeClass('bg-fixed');
            }
        });

    });

});
