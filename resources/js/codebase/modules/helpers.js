import Tools from './tools';

export default class Helpers {
    static run(helpers, options = {}) {
        let helperList = {
            'core-tooltip': () => this.coreBootstrapTooltip(),
            'core-popover': () => this.coreBootstrapPopover(),
            'core-tab': () => this.coreBootstrapTabs(),
            'core-custom-file-input': () => this.coreBootstrapCustomFileInput(),
            'core-toggle-class': () => this.coreToggleClass(),
            'core-scrollTo': () => this.coreScrollTo(),
            'core-year-copy': () => this.coreYearCopy(),
            'core-appear': () => this.coreAppear(),
            'core-appear-countTo': () => this.coreAppearCountTo(),
            'core-ripple': () => this.coreRipple(),
            'content-filter': () => this.contentFilter(),
            slimscroll: () => this.slimscroll(),
            notify: (options) => this.notify(options)
        };

        if (helpers instanceof Array) {
            for (let index in helpers) {
                if (helperList[helpers[index]]) {
                    helperList[helpers[index]](options);
                }
            }
        } else {
            if (helperList[helpers]) {
                helperList[helpers](options);
            }
        }
    }

    static coreBootstrapTooltip() {
        jQuery('[data-toggle="tooltip"]:not(.js-tooltip-enabled)').add('.js-tooltip:not(.js-tooltip-enabled)').each((index, element) => {
            let el = jQuery(element);

            el.addClass('js-tooltip-enabled').tooltip({
                container: el.data('container') || 'body',
                animation: el.data('animation') || false
            });
        });
    }

    static coreBootstrapPopover() {
        jQuery('[data-toggle="popover"]:not(.js-popover-enabled)').add('.js-popover:not(.js-popover-enabled)').each((index, element) => {
            let el = jQuery(element);

            el.addClass('js-popover-enabled').popover({
                container: el.data('container') || 'body',
                animation: el.data('animation') || false,
                trigger: el.data('trigger') || 'hover focus'
            });
        });
    }

    static coreBootstrapTabs() {
        jQuery('[data-toggle="tabs"]:not(.js-tabs-enabled)').add('.js-tabs:not(.js-tabs-enabled)').each((index, element) => {
            jQuery(element).addClass('js-tabs-enabled').find('a').on('click.cb.helpers.core', e => {
                e.preventDefault();
                jQuery(e.currentTarget).tab('show');
            });
        });
    }

    static coreBootstrapCustomFileInput() {
        jQuery('[data-toggle="custom-file-input"]:not(.js-custom-file-input-enabled)').each((index, element) => {
            let el = jQuery(element);

            el.addClass('js-custom-file-input-enabled').on('change', e => {
                let fileName = (e.target.files.length > 1) ? e.target.files.length + ' ' + (el.data('lang-files') || 'Files') : e.target.files[0].name;

                el.next('.custom-file-label').css('overflow-x', 'hidden').html(fileName);
            });
        });
    }

    static coreToggleClass() {
        jQuery('[data-toggle="class-toggle"]:not(.js-class-toggle-enabled)')
                .add('.js-class-toggle:not(.js-class-toggle-enabled)')
                .on('click.cb.helpers.core', e => {
            let el = jQuery(e.currentTarget);

            el.addClass('js-class-toggle-enabled').trigger('blur');

            jQuery(el.data('target').toString()).toggleClass(el.data('class').toString());
        });
    }

    static coreScrollTo() {
        jQuery('[data-toggle="scroll-to"]:not(.js-scroll-to-enabled)').on('click.cb.helpers.core', e => {
            e.stopPropagation();

            // Set variables
            let lHeader         = jQuery('#page-header');
            let el              = jQuery(e.currentTarget);
            let elTarget        = el.data('target') || el.attr('href');
            let elSpeed         = el.data('speed') || 1000;
            let headerHeight    = (lHeader.length && jQuery('#page-container').hasClass('page-header-fixed')) ? lHeader.outerHeight() : 0;

            el.addClass('js-scroll-to-enabled');

            jQuery('html, body').animate({
                scrollTop: jQuery(elTarget).offset().top - headerHeight
            }, elSpeed);
        });
    }

    static coreYearCopy() {
        let el = jQuery('.js-year-copy:not(.js-year-copy-enabled)');

        if (el.length > 0) {
            let date        = new Date();
            let curYear     = date.getFullYear();
            let baseYear    = (el.html().length > 0) ? el.html() : curYear;

            el.addClass('js-year-copy-enabled').html(
                (parseInt(baseYear) >= curYear) ? curYear : baseYear + '-' + curYear.toString().substr(2, 2)
            );
        }
    }

    static coreAppear() {
        jQuery('[data-toggle="appear"]:not(.js-appear-enabled)').each((index, element) => {
            let windowW     = Tools.getWidth();
            let el          = jQuery(element);
            let elCssClass  = el.data('class') || 'animated fadeIn';
            let elOffset    = el.data('offset') || 0;
            let elTimeout   = (windowW < 992) ? 0 : (el.data('timeout') ? el.data('timeout') : 0);

            el.addClass('js-appear-enabled').appear(() => {
                setTimeout(() => {
                    el.removeClass('invisible').addClass(elCssClass);
                }, elTimeout);
            }, {accY: elOffset});
        });
    }

    static coreAppearCountTo() {
        jQuery('[data-toggle="countTo"]:not(.js-count-to-enabled)').each((index, element) => {
            let el         = jQuery(element);
            let elAfter    = el.data('after');
            let elBefore   = el.data('before');

            el.addClass('js-count-to-enabled').appear(() => {
                el.countTo({
                    speed: el.data('speed') || 1500,
                    refreshInterval: el.data('refresh-interval') || 15,
                    onComplete: () => {
                        if(elAfter) {
                            el.html(el.html() + elAfter);
                        } else if (elBefore) {
                            el.html(elBefore + el.html());
                        }
                    }
                });
            });
        });
    }

    static coreRipple() {
        jQuery('[data-toggle="click-ripple"]:not(.js-click-ripple-enabled)').each((index, element) => {
            let el = jQuery(element);

            el.addClass('js-click-ripple-enabled')
                .css({
                    overflow: 'hidden',
                    position: 'relative',
                    'z-index': 1
                }).on('click.cb.helpers.core', e => {
                    let cssClass = 'click-ripple', ripple, d, x, y;

                    if (el.children('.' + cssClass).length === 0) {
                        el.prepend('<span class="' + cssClass + '"></span>');
                    }
                    else {
                        el.children('.' + cssClass).removeClass('animate');
                    }

                    ripple = el.children('.' + cssClass);

                    if(!ripple.height() && !ripple.width()) {
                        d = Math.max(el.outerWidth(), el.outerHeight());
                        ripple.css({height: d, width: d});
                    }

                    x = e.pageX - el.offset().left - ripple.width()/2;
                    y = e.pageY - el.offset().top - ripple.height()/2;

                    ripple.css({top: y + 'px', left: x + 'px'}).addClass('animate');
                });
        });
    }

    static contentFilter() {
        jQuery('.js-filter:not(.js-filter-enabled)').each((index, element) => {
            let el          = jQuery(element);
            let filterNav   = jQuery('.nav-pills', el);
            let filterLinks = jQuery('a[data-category-link]', el);
            let filterItems = jQuery('[data-category]', el);
            let filterSpeed = el.data('speed') || 200;

            el.addClass('js-filter-enabled');

            if (filterNav.length) {
                let resizeTimeout, windowW;

                jQuery(window).on('resize.cb.helpers', () => {
                    clearTimeout(resizeTimeout);

                    resizeTimeout = setTimeout(() => {
                        windowW = Tools.getWidth();

                        if (windowW < 768) {
                            filterNav.addClass('flex-column');
                        } else {
                            filterNav.removeClass('flex-column');
                        }
                    }, 150);
                }).trigger('resize.cb.helpers');
            }

            if (el.data('numbers')) {
                filterLinks.each((index, element) => {
                    let filterLink  = jQuery(element);
                    let filterCat   = filterLink.data('category-link');

                    if (filterCat === 'all') {
                        filterLink.append(' (' + filterItems.length + ')');
                    } else {
                        filterLink.append(' (' + filterItems.filter('[data-category="' + filterCat + '"]').length + ')');
                    }
                });
            }

            filterLinks.on('click.cb.helpers', e => {
                let filterLink = jQuery(e.currentTarget);
                let filterCat;

                if (!filterLink.hasClass('active')) {
                    filterLinks.removeClass('active');

                    filterLink.addClass('active');

                    filterCat = filterLink.data('category-link');

                    if (filterCat === 'all') {
                        if (filterItems.filter(':visible').length) {
                            filterItems.filter(':visible').fadeOut(filterSpeed, () => {
                                filterItems.fadeIn(filterSpeed);
                            });
                        } else {
                            filterItems.fadeIn(filterSpeed);
                        }
                    } else {
                        if (filterItems.filter(':visible').length) {
                            filterItems.filter(':visible').fadeOut(filterSpeed, () => {
                                filterItems .filter('[data-category="' + filterCat + '"]') .fadeIn(filterSpeed);
                            });
                        } else {
                            filterItems.filter('[data-category="' + filterCat + '"]').fadeIn(filterSpeed);
                        }
                    }
                }

                return false;
            });
        });
    }

    static slimscroll() {
        jQuery('[data-toggle="slimscroll"]:not(.js-slimscroll-enabled)').each((index, element) => {
            let el = jQuery(element);

            el.addClass('js-slimscroll-enabled').slimScroll({
                height: el.data('height') || '200px',
                size: el.data('size') || '5px',
                position: el.data('position') || 'right',
                color: el.data('color') || '#000',
                opacity: el.data('opacity') || '.25',
                distance: el.data('distance') || '0',
                alwaysVisible: el.data('always-visible') ? true : false,
                railVisible: el.data('rail-visible') ? true : false,
                railColor: el.data('rail-color') ||'#999',
                railOpacity: el.data('rail-opacity') || .3
            });
        });
    }

    static notify(options = {}) {
        if (jQuery.isEmptyObject(options)) {
            jQuery('.js-notify:not(.js-notify-enabled)').each((index, element) => {
                jQuery(element).addClass('js-notify-enabled').on('click.cb.helpers', e => {
                    let el = jQuery(e.currentTarget);

                    jQuery.notify({
                            icon: el.data('icon') || '',
                            message: el.data('message'),
                            url: el.data('url') || ''
                        },
                        {
                            element: 'body',
                            type: el.data('type') || 'info',
                            placement: {
                                from: el.data('from') || 'top',
                                align: el.data('align') || 'right'
                            },
                            allow_dismiss: true,
                            newest_on_top: true,
                            showProgressbar: false,
                            offset: 20,
                            spacing: 10,
                            z_index: 1033,
                            delay: 5000,
                            timer: 1000,
                            animate: {
                                enter: 'animated fadeIn',
                                exit: 'animated fadeOutDown'
                            }
                        });
                });
            });
        } else {
            jQuery.notify({
                    icon: options.icon || '',
                    message: options.message,
                    url: options.url || ''
                },
                {
                    element: options.element || 'body',
                    type: options.type || 'info',
                    placement: {
                        from: options.from || 'top',
                        align: options.align || 'right'
                    },
                    allow_dismiss: (options.allow_dismiss === false) ? false : true,
                    newest_on_top: (options.newest_on_top === false) ? false : true,
                    showProgressbar: options.show_progress_bar ? true : false,
                    offset: options.offset || 20,
                    spacing: options.spacing || 10,
                    z_index: options.z_index || 1033,
                    delay: options.delay || 5000,
                    timer: options.timer || 1000,
                    animate: {
                        enter: options.animate_enter || 'animated fadeIn',
                        exit: options.animate_exit || 'animated fadeOutDown'
                    }
                });
        }
    }
}
