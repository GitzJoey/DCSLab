import Tools from './tools';
import Helpers from './helpers';

export default class Template {
    constructor() {
        this._uiInit();
    }

    _uiInit() {
        this._lHtml                 = jQuery('html');
        this._lBody                 = jQuery('body');
        this._lpageLoader           = jQuery('#page-loader');
        this._lPage                 = jQuery('#page-container');
        this._lSidebar              = jQuery('#sidebar');
        this._lSidebarScrollCon     = jQuery('.js-sidebar-scroll', '#sidebar');
        this._lSideOverlay          = jQuery('#side-overlay');
        this._lResize               = false;
        this._lHeader               = jQuery('#page-header');
        this._lHeaderSearch         = jQuery('#page-header-search');
        this._lHeaderSearchInput    = jQuery('#page-header-search-input');
        this._lHeaderLoader         = jQuery('#page-header-loader');
        this._lMain                 = jQuery('#main-container');
        this._lFooter               = jQuery('#page-footer');

        this._lSidebarScroll        = false;
        this._lSideOverlayScroll    = false;
        this._windowW               = Tools.getWidth();

        this._uiHandleSidebars('init');
        this._uiHandleHeader();
        this._uiHandleNav();
        this._uiHandleForms();

        this._uiApiLayout();
        this._uiApiBlocks();

        this.helpers([
            'core-tooltip',
            'core-popover',
            'core-tab',
            'core-custom-file-input',
            'core-toggle-class',
            'core-scrollTo',
            'core-year-copy',
            'core-appear',
            'core-appear-countTo',
            'core-ripple'
        ]);

        this._uiHandlePageLoader();
    }

    _uiHandleSidebars(mode) {
        let self = this;

        if (mode === 'init') {
            self._lPage.addClass('side-trans-enabled');
            jQuery(window).on('resize', () => {
                clearTimeout(self._lResize);

                self._lPage.removeClass('side-trans-enabled');

                self._lResize = setTimeout(() => { self._lPage.addClass('side-trans-enabled'); }, 500);
            });
            this._uiHandleSidebars();
        } else {
            if (self._lPage.hasClass('side-scroll')) {
                if ((self._lSidebar.length > 0) && !self._lSidebarScroll) {
                    self._lSidebarScroll = new SimpleBar(self._lSidebarScrollCon[0]);

                    jQuery('.simplebar-content-wrapper', self._lSidebar).scrollLock('enable');
                }

                if ((self._lSideOverlay.length > 0) && !self._lSideOverlayScroll) {
                    self._lSideOverlayScroll = new SimpleBar(self._lSideOverlay[0]);

                    jQuery('.simplebar-content-wrapper', self._lSideOverlay).scrollLock('enable');
                }
            } else {
                if (self._lSidebar && self._lSidebarScroll) {
                    jQuery('.simplebar-content-wrapper', self._lSidebar).scrollLock('disable');

                    self._lSidebarScroll.unMount();
                    self._lSidebarScroll = null;

                    self._lSidebarScrollCon.removeAttr('data-simplebar')
                        .html(jQuery('.simplebar-content', self._lSidebar).html());
                }

                if (self._lSideOverlay && self._lSideOverlayScroll) {
                    jQuery('.simplebar-content-wrapper', self._lSideOverlay).scrollLock('disable');

                    self._lSideOverlayScroll.unMount();
                    self._lSideOverlayScroll = null;

                    self._lSideOverlay.removeAttr('data-simplebar')
                        .html(jQuery('.simplebar-content', self._lSideOverlay).html());
                }
            }
        }
    }

    _uiHandleHeader() {
        let self = this;

        jQuery(window).off('scroll.cb.header');

        if (self._lPage.hasClass('page-header-glass') && self._lPage.hasClass('page-header-fixed')) {
            jQuery(window).on('scroll.cb.header', e => {
                if (jQuery(e.currentTarget).scrollTop() > 60) {
                    self._lPage.addClass('page-header-scroll');
                } else {
                    self._lPage.removeClass('page-header-scroll');
                }
            }).trigger('scroll.cb.header');
        }
    }

    _uiHandleNav() {
        let self = this;

        self._lPage.off('click.cb.menu');

        self._lPage.on('click.cb.menu', '[data-toggle="nav-submenu"]', e => {
            let link = jQuery(e.currentTarget);

            let parentLi = link.parent('li');

            if (parentLi.hasClass('open')) {
                parentLi.removeClass('open');
            } else {
                link.closest('ul').children('li').removeClass('open');
                parentLi.addClass('open');
            }

            link.trigger('blur');

            return false;
        });
    }

    _uiHandlePageLoader(mode = 'hide', colorClass) {
        if (mode === 'show') {
            if (this._lpageLoader.length) {
                if (colorClass) {
                    this._lpageLoader.removeClass().addClass(colorClass);
                }

                this._lpageLoader.addClass('show');
            } else {
                this._lBody.prepend(`<div id="page-loader" class="show${colorClass ? ' ' + colorClass : ''}"></div>`);
            }
        } else if (mode === 'hide') {
            if (this._lpageLoader.length) {
                this._lpageLoader.removeClass('show');
            }
        }
    }

    _uiHandleForms() {
        jQuery('.form-material.floating > .form-control').each((index, element) => {
            let input  = jQuery(element);
            let parent = input.parent('.form-material');

            setTimeout(e => {
                if (input.val() ) {
                    parent.addClass('open');
                }
            }, 150);

            input.off('change.cb.inputs').on('change.cb.inputs', e => {
                if (input.val()) {
                    parent.addClass('open');
                } else {
                    parent.removeClass('open');
                }
            });
        });
    }

    _uiApiLayout(mode = 'init') {
        let self = this;

        self._windowW = Tools.getWidth();

        let layoutAPI = {
            init: () => {
                self._lPage.off('click.cb.layout');
                self._lPage.off('click.cb.overlay');

                self._lPage.on('click.cb.layout', '[data-toggle="layout"]', e => {
                    let el = jQuery(e.currentTarget);

                    self._uiApiLayout(el.data('action'));

                    el.trigger('blur');
                });

                if (self._lPage.hasClass('enable-page-overlay')) {
                    self._lPage.prepend('<div id="page-overlay"></div>');

                    jQuery('#page-overlay').on('click.cb.overlay', e => {
                        self._uiApiLayout('side_overlay_close');
                    });
                }
            },
            sidebar_pos_toggle: () => {
                self._lPage.toggleClass('sidebar-r');
            },
            sidebar_pos_left: () => {
                self._lPage.removeClass('sidebar-r');
            },
            sidebar_pos_right: () => {
                self._lPage.addClass('sidebar-r');
            },
            sidebar_toggle: () => {
                if (self._windowW > 991) {
                    self._lPage.toggleClass('sidebar-o');
                } else {
                    self._lPage.toggleClass('sidebar-o-xs');
                }
            },
            sidebar_open: () => {
                if (self._windowW > 991) {
                    self._lPage.addClass('sidebar-o');
                } else {
                    self._lPage.addClass('sidebar-o-xs');
                }
            },
            sidebar_close: () => {
                if (self._windowW > 991) {
                    self._lPage.removeClass('sidebar-o');
                } else {
                    self._lPage.removeClass('sidebar-o-xs');
                }
            },
            sidebar_mini_toggle: () => {
                if (self._windowW > 991) {
                    self._lPage.toggleClass('sidebar-mini');
                }
            },
            sidebar_mini_on: () => {
                if (self._windowW > 991) {
                    self._lPage.addClass('sidebar-mini');
                }
            },
            sidebar_mini_off: () => {
                if (self._windowW > 991) {
                    self._lPage.removeClass('sidebar-mini');
                }
            },
            sidebar_style_inverse_toggle: () => {
                self._lPage.toggleClass('sidebar-inverse');
            },
            sidebar_style_inverse_on: () => {
                self._lPage.addClass('sidebar-inverse');
            },
            sidebar_style_inverse_off: () => {
                self._lPage.removeClass('sidebar-inverse');
            },
            side_overlay_toggle: () => {
                if (self._lPage.hasClass('side-overlay-o')) {
                    self._uiApiLayout('side_overlay_close');
                } else {
                    self._uiApiLayout('side_overlay_open');
                }
            },
            side_overlay_open: () => {
                self._lPage.addClass('side-overlay-o');

                // When ESCAPE key is hit close the side overlay
                jQuery(document).on('keydown.cb.sideOverlay', e => {
                    if (e.which === 27) {
                        e.preventDefault();
                        self._uiApiLayout('side_overlay_close');
                    }
                });
            },
            side_overlay_close: () => {
                self._lPage.removeClass('side-overlay-o');

                // Unbind ESCAPE key
                jQuery(document).off('keydown.cb.sideOverlay');
            },
            side_overlay_hoverable_toggle: () => {
                self._lPage.toggleClass('side-overlay-hover');
            },
            side_overlay_hoverable_on: () => {
                self._lPage.addClass('side-overlay-hover');
            },
            side_overlay_hoverable_off: () => {
                self._lPage.removeClass('side-overlay-hover');
            },
            header_fixed_toggle: () => {
                self._lPage.toggleClass('page-header-fixed');
                self._uiHandleHeader();
            },
            header_fixed_on: () => {
                self._lPage.addClass('page-header-fixed');
                self._uiHandleHeader();
            },
            header_fixed_off: () => {
                self._lPage.removeClass('page-header-fixed');
                self._uiHandleHeader();
            },
            header_style_modern: () => {
                self._lPage.removeClass('page-header-glass page-header-inverse').addClass('page-header-modern');
                self._uiHandleHeader();
            },
            header_style_classic: () => {
                self._lPage.removeClass('page-header-glass page-header-modern');
                self._uiHandleHeader();
            },
            header_style_glass: () => {
                self._lPage.removeClass('page-header-modern').addClass('page-header-glass');
                self._uiHandleHeader();
            },
            header_style_inverse_toggle: () => {
                if (!self._lPage.hasClass('page-header-modern')) {
                    self._lPage.toggleClass('page-header-inverse');
                }
            },
            header_style_inverse_on: () => {
                if (!self._lPage.hasClass('page-header-modern')) {
                    self._lPage.addClass('page-header-inverse');
                }
            },
            header_style_inverse_off: () => {
                if (!self._lPage.hasClass('page-header-modern')) {
                    self._lPage.removeClass('page-header-inverse');
                }
            },
            header_search_on: () => {
                self._lHeaderSearch.addClass('show');
                self._lHeaderSearchInput.focus();

                jQuery(document).on('keydown.cb.header.search', e => {
                    if (e.which === 27) {
                        e.preventDefault();
                        self._uiApiLayout('header_search_off');
                    }
                });
            },
            header_search_off: () => {
                self._lHeaderSearch.removeClass('show');
                self._lHeaderSearchInput.trigger('blur');

                // Unbind ESCAPE key
                jQuery(document).off('keydown.cb.header.search');
            },
            header_loader_on: () => {
                self._lHeaderLoader.addClass('show');
            },
            header_loader_off: () => {
                self._lHeaderLoader.removeClass('show');
            },
            side_scroll_toggle: () => {
                self._lPage.toggleClass('side-scroll');
                self._uiHandleSidebars();
            },
            side_scroll_on: () => {
                self._lPage.addClass('side-scroll');
                self._uiHandleSidebars();
            },
            side_scroll_off: () => {
                self._lPage.removeClass('side-scroll');
                self._uiHandleSidebars();
            },
            content_layout_toggle: () => {
                if (self._lPage.hasClass('main-content-boxed')) {
                    self._uiApiLayout('content_layout_narrow');
                } else if (self._lPage.hasClass('main-content-narrow')) {
                    self._uiApiLayout('content_layout_full_width');
                } else {
                    self._uiApiLayout('content_layout_boxed');
                }
            },
            content_layout_boxed: () => {
                self._lPage.removeClass('main-content-narrow').addClass('main-content-boxed');
            },
            content_layout_narrow: () => {
                self._lPage.removeClass('main-content-boxed').addClass('main-content-narrow');
            },
            content_layout_full_width: () => {
                self._lPage.removeClass('main-content-boxed main-content-narrow');
            }
        };

        if (layoutAPI[mode]) {
            layoutAPI[mode]();
        }
    }

    _uiApiBlocks(block = false, mode = 'init') {
        let self = this;

        let elBlock, btnFullscreen, btnContentToggle;

        let iconFullscreen         = 'icon icon-size-fullscreen';
        let iconFullscreenActive   = 'icon icon-size-actual';
        let iconContent            = 'icon icon-arrow-up';
        let iconContentActive      = 'icon icon-arrow-down';

        let blockAPI = {
            init: () => {
                jQuery('[data-toggle="block-option"][data-action="fullscreen_toggle"]').each((index, element) => {
                    let el = jQuery(element);

                    el.html('<i class="' + (jQuery(el).closest('.block').hasClass('block-mode-fullscreen') ? iconFullscreenActive : iconFullscreen) + '"></i>');
                });

                jQuery('[data-toggle="block-option"][data-action="content_toggle"]').each((index, element) => {
                    let el = jQuery(element);

                    el.html('<i class="' + (el.closest('.block').hasClass('block-mode-hidden') ? iconContentActive : iconContent) + '"></i>');
                });

                self._lPage.off('click.cb.blocks');

                // Call blocks API on option button click
                self._lPage.on('click.cb.blocks', '[data-toggle="block-option"]', e => {
                    this._uiApiBlocks(jQuery(e.currentTarget).closest('.block'), jQuery(e.currentTarget).data('action'));
                });
            },
            fullscreen_toggle: () => {
                elBlock.removeClass('block-mode-pinned').toggleClass('block-mode-fullscreen');

                if (elBlock.hasClass('block-mode-fullscreen')) {
                    jQuery(elBlock).scrollLock('enable');
                } else {
                    jQuery(elBlock).scrollLock('disable');
                }

                if (btnFullscreen.length) {
                    if (elBlock.hasClass('block-mode-fullscreen')) {
                        jQuery('i', btnFullscreen)
                            .removeClass(iconFullscreen)
                            .addClass(iconFullscreenActive);
                    } else {
                        jQuery('i', btnFullscreen)
                            .removeClass(iconFullscreenActive)
                            .addClass(iconFullscreen);
                    }
                }
            },
            fullscreen_on: () => {
                elBlock.removeClass('block-mode-pinned').addClass('block-mode-fullscreen');

                jQuery(elBlock).scrollLock('enable');

                if (btnFullscreen.length) {
                    jQuery('i', btnFullscreen)
                        .removeClass(iconFullscreen)
                        .addClass(iconFullscreenActive);
                }
            },
            fullscreen_off: () => {
                elBlock.removeClass('block-mode-fullscreen');

                jQuery(elBlock).scrollLock('disable');

                if (btnFullscreen.length) {
                    jQuery('i', btnFullscreen)
                        .removeClass(iconFullscreenActive)
                        .addClass(iconFullscreen);
                }
            },
            content_toggle: () => {
                elBlock.toggleClass('block-mode-hidden');

                if (btnContentToggle.length) {
                    if (elBlock.hasClass('block-mode-hidden')) {
                        jQuery('i', btnContentToggle)
                            .removeClass(iconContent)
                            .addClass(iconContentActive);
                    } else {
                        jQuery('i', btnContentToggle)
                            .removeClass(iconContentActive)
                            .addClass(iconContent);
                    }
                }
            },
            content_hide: () => {
                elBlock.addClass('block-mode-hidden');

                if (btnContentToggle.length) {
                    jQuery('i', btnContentToggle)
                        .removeClass(iconContent)
                        .addClass(iconContentActive);
                }
            },
            content_show: () => {
                elBlock.removeClass('block-mode-hidden');

                if (btnContentToggle.length) {
                    jQuery('i', btnContentToggle)
                        .removeClass(iconContentActive)
                        .addClass(iconContent);
                }
            },
            state_toggle: () => {
                elBlock.toggleClass('block-mode-loading');

                if (jQuery('[data-toggle="block-option"][data-action="state_toggle"][data-action-mode="demo"]', elBlock).length) {
                    setTimeout(() => {
                        elBlock.removeClass('block-mode-loading');
                    }, 2000);
                }
            },
            state_loading: () => {
                elBlock.addClass('block-mode-loading');
            },
            state_normal: () => {
                elBlock.removeClass('block-mode-loading');
            },
            pinned_toggle: () => {
                elBlock.removeClass('block-mode-fullscreen').toggleClass('block-mode-pinned');
            },
            pinned_on: () => {
                elBlock.removeClass('block-mode-fullscreen').addClass('block-mode-pinned');
            },
            pinned_off: () => {
                elBlock.removeClass('block-mode-pinned');
            },
            close: () => {
                elBlock.addClass('d-none');
            },
            open: () => {
                elBlock.removeClass('d-none');
            }
        };

        if (mode === 'init') {
            blockAPI[mode]();
        } else {
            elBlock = (block instanceof jQuery) ? block : jQuery(block);

            if (elBlock.length) {
                btnFullscreen       = jQuery('[data-toggle="block-option"][data-action="fullscreen_toggle"]', elBlock);
                btnContentToggle    = jQuery('[data-toggle="block-option"][data-action="content_toggle"]', elBlock);

                if (blockAPI[mode]) {
                    blockAPI[mode]();
                }
            }
        }
    }

    init() {
        this._uiInit();
    }

    layout(mode) {
        this._uiApiLayout(mode);
    }

    blocks(block, mode) {
        this._uiApiBlocks(block, mode);
    }

    loader(mode, colorClass) {
        this._uiHandlePageLoader(mode, colorClass);
    }

    helpers(helpers, options = {}) {
        Helpers.run(helpers, options);
    }

    helper(helper, options = {}) {
        Helpers.run(helper, options);
    }
}
