import * as bootstrap from 'bootstrap';
import $ from 'cash-dom';

$(document).ready(function() {
    var scrollLink = $('.page-scroll');

    $(window).on('scroll', function() {
        if ($('.navbar').offset().top > 50) {
            $('.navbar-fixed-top').addClass('top-nav-collapse');
        } else {
            $('.navbar-fixed-top').removeClass('top-nav-collapse');
        }

        if (window.pageYOffset < 10) {
            $('.navigation').removeClass('sticky');

            $('.back-to-top').removeClass('show').addClass('hide');
        }else{
            $('.navigation').addClass('sticky');

            $('.back-to-top').removeClass('hide').addClass('show');
        }

        var scrollbarLocation = window.pageYOffset;

        scrollLink.each(function() {
            var sectionOffset = $(this.hash).offset().top - 90;

            if ( sectionOffset <= scrollbarLocation ) {
                $(this).parent().addClass('active');
                $(this).parent().siblings().removeClass('active');
            }
        });
    });

    $('a.page-scroll[href*="#"]:not([href="#"])').on('click', function () {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
            if (target.length) {
                window.scroll({
                    top: $(target).position().top,
                    left: $(target).position().left,
                    behavior: 'smooth'
                });

                $('.nav-item').each(function(idx, el) {
                    $(el).removeClass('active');

                    if ($(target).attr('id') === $(el).data('menu')) {
                        $(el).addClass('active');
                    }
                });

                return false;
            }
        }
    });

    $('.navbar-toggler').on('click', function() {
        $(this).toggleClass('active');
    });

    $('.navbar-nav a').on('click', function() {
        $('.navbar-toggler').removeClass('active');
        $('.navbar-collapse').removeClass('show');
    });
});
