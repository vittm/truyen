jQuery(document).ready(function($) {
    "use strict";
    // Ticker
    $('#mt-newsTicker').bxSlider({
        minSlides: 1,
        maxSlides: 1,
        speed: 3000,
        mode: 'vertical',
        auto: true,
        controls: true,
        prevText: '<i class="fa fa-backward"> </i>',
        nextText: '<i class="fa fa-forward"> </i>',
        pager: false,
        onSliderLoad: function() {
            $('#mt-newsTicker').removeClass('cS-hidden');
        }
    });

    // Slider
    $('.editorialSlider').bxSlider({
        pager: false,
        controls: true,
        prevText: '<i class="fa fa-chevron-left"> </i>',
        nextText: '<i class="fa fa-chevron-right"> </i>',
        touchEnabled: false,
        onSliderLoad: function() {
            $('.editorialSlider').removeClass('cS-hidden');
        }
    });

    //Search toggle
    $('.header-search-wrapper .search-main').click(function() {
        $('.search-form-main').toggleClass('active-search');
        $('.search-form-main .search-field').focus();
    });

    //widget title wrap
    $('.widget .widget-title,.related-articles-wrapper .related-title').wrap('<div class="widget-title-wrapper"></div>');

    //responsive menu toggle
    $('.bottom-header-wrapper .menu-toggle').click(function(event) {
        $('.bottom-header-wrapper #site-navigation').slideToggle('slow');
    });

    //responsive sub menu toggle
    $('#site-navigation .menu-item-has-children').append('<span class="sub-toggle"> <i class="fa fa-angle-right"></i> </span>');

    $('#site-navigation .sub-toggle').click(function() {
        $(this).parent('.menu-item-has-children').children('ul.sub-menu').first().slideToggle('1000');
        $(this).children('.fa-angle-right').first().toggleClass('fa-angle-down');
    });

    // Scroll To Top
    $(window).scroll(function() {
        if ($(this).scrollTop() > 700) {
            $('#mt-scrollup').fadeIn('slow');
        } else {
            $('#mt-scrollup').fadeOut('slow');
        }
    });

    $('#mt-scrollup').click(function() {
        $("html, body").animate({
            scrollTop: 0
        }, 600);
        return false;
    });
    
    //column block wrap js 
    var divs = jQuery("section.editorial_block_column");
    for(var i=0; i<divs.length;) {
        i += divs.eq(i).nextUntil(':not(.editorial_block_column').andSelf().wrapAll('<div class="editorial_block_column-wrap"> </div>').length;
    }
});