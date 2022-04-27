/**
 *
 * Nav
 * Main navigation and user control buttons.
 *
 * @param {boolean} matchUrl Matching the url with the menu item and setting active class
 * @param {boolean} disablePinning Disables the pin button
 * @param {number} verticalUnpinned Vertical menu unpin screen size
 * @param {number} verticalMobile Vertical menu mobile switch size
 * @param {number} horizontalMobile Horizontal menu mobile switch size
 *
 *
 **/

class Nav {
  // Default options
  get options() {
    return {
      matchUrl: true,
      disablePinning: false,
      verticalUnpinned: Globals.xxl.replace('px', ''),
      verticalMobile: Globals.lg.replace('px', ''),
      horizontalMobile: Globals.lg.replace('px', ''),
    };
  }

  constructor(element, options = {}) {
    if (!element) {
      return;
    }
    this.settings = Object.assign(this.options, options);
    this.settings = Object.assign(this.settings, element.dataset);
    this.element = element;
    this._init();
  }

  _init() {
    this.mobileButton = this.element.querySelector('#mobileMenuButton');
    this.pinButton = this.element.querySelector('#pinButton');
    this.colorButton = this.element.querySelector('#colorButton');
    this.menuContainer = this.element.querySelector('.menu-container');
    this.menuPlainOuter = this.element.querySelector('#menu').outerHTML;
    this.menuPlainInner = this.element.querySelector('#menu').innerHTML;

    this.html = document.documentElement;

    this._onCollapseShow = this._onCollapseShow.bind(this);
    this._onCollapseHide = this._onCollapseHide.bind(this);
    this._onCollapseShown = this._onCollapseShown.bind(this);
    this._onVerticalMenuClick = this._onVerticalMenuClick.bind(this);
    this._onHorizontalMenuDropdownHidden = this._onHorizontalMenuDropdownHidden.bind(this);
    this._onHorizontalMenuDropdownClick = this._onHorizontalMenuDropdownClick.bind(this);
    if (document.querySelector('#menuSide')) {
      this.menuSideInner = document.querySelector('#menuSide').innerHTML;
    }

    this._initMenuVariables();

    // Breakpoints
    this.verticalUnpinned = this.settings.verticalUnpinned;
    this.verticalMobile = this.settings.verticalMobile;
    this.horizontalMobile = this.settings.horizontalMobile;

    this.placementStatus = 0;
    this.behaviourStatus = 0;

    // Holds users selection, is not set directly to the html element
    this.selectedMenuBehaviour = this.html.getAttribute('data-behaviour');
    this.selectedMenuPlacement = this.html.getAttribute('data-placement');

    this.scrollbar;
    this.prevScrollpos = window.pageYOffset;
    this.windowScrolled = false;
    this.collapseTimeout;

    if (this.settings.disablePinning) {
      this._disablePinButton();
    }

    this._initMenuPlacement();
    this._addListeners();
  }

  _initMenuVariables() {
    this.menuVertical = document.createElement('DIV');
    this.menuVertical.innerHTML = this.menuPlainOuter;
    this.menuVertical = this.menuVertical.firstChild;

    this.menuHorizontal = document.createElement('DIV');
    this.menuHorizontal.innerHTML = this.menuPlainOuter;
    this.menuHorizontal = this.menuHorizontal.firstChild;

    // Checking if there is a side menu, if so injecting it to the menuVertical for mobile
    if (this.menuSideInner) {
      if (this.html.getAttribute('data-dimension') == 'mobile') {
        // this.menuVertical = jQuery('<ul id="menu" class="menu">' + this.menuSideInner + this.menuPlainInner + '</ul>');
        this.menuVertical = document.createElement('DIV');
        this.menuVertical.innerHTML = '<ul id="menu" class="menu">' + this.menuSideInner + this.menuPlainInner + '</ul>';
        this.menuVertical = this.menuVertical.firstChild;
      } else {
        // this.menuVertical = jQuery('<ul id="menu" class="menu">' + this.menuPlainInner + '</ul>');
        this.menuVertical = document.createElement('DIV');
        this.menuVertical.innerHTML = '<ul id="menu" class="menu">' + this.menuPlainInner + '</ul>';
        this.menuVertical = this.menuVertical.firstChild;
      }
    }
  }

  // Adding listners for mouse events like hover, scroll, click. Also resize and some Global event listeners.
  _addListeners() {
    window.addEventListener('click', (event) => {
      this._onWindowClick(event);
    });
    window.addEventListener('resize', (event) => {
      this._onWindowResize(event);
    });
    window.addEventListener('scroll', (event) => {
      this._onWindowScroll(event);
    });

    this.element.addEventListener('mouseenter', (event) => {
      this._onMouseEnter(event);
    });
    this.element.addEventListener('mouseleave', (event) => {
      this._onMouseLeave(event);
    });

    this.html.addEventListener(Globals.menuPlacementChange, (event) => this._onMenuPlacementChange(event));
    this.html.addEventListener(Globals.menuBehaviourChange, (event) => this._onMenuBehaviourChange(event));

    this.colorButton &&
      this.colorButton.addEventListener('click', (event) => {
        this._onColorClick(event);
      });

    this.mobileButton &&
      this.mobileButton.addEventListener('click', (event) => {
        this._showMobileMenu(event);
      });

    this.pinButton &&
      this.pinButton.addEventListener('click', (event) => {
        this._onPinClick(event);
      });
    setInterval(() => {
      this._onWindowScrollInterval();
    }, 200);
  }

  // Converts menu ul li structure to Bootstrap collapsable and appends it in the #menu
  _addVerticalMenu() {
    document.querySelector('#menu').remove();
    this._initMenuVariables();
    const menu = this.menuVertical;
    menu.querySelectorAll('a').forEach((element) => {
      if (element.nextElementSibling && element.nextElementSibling.tagName === 'UL') {
        element.setAttribute('data-bs-toggle', 'collapse');
        element.setAttribute('data-role', 'button');
        element.setAttribute('aria-expanded', false);
        element.nextElementSibling.classList.add('collapse');
        new bootstrap.Collapse(element.nextElementSibling, {
          toggle: false,
        });
        if (element.getAttribute('data-bs-target')) {
          element.nextElementSibling.setAttribute('id', element.getAttribute('data-bs-target').substring(1));
        }
      }
    });

    this.menuContainer.insertAdjacentElement('beforeend', this.menuVertical);
    document.querySelector('#menu').classList.add('show');

    this._matchUrl();
    this._initVerticalMenu();
    this._initIcons();
  }

  // Initializes the vertical menu and expands the active nav item
  _initVerticalMenu() {
    this._removeHorizontalMenuListeners();
    this._addVerticalMenuListeners();
    const menu = this.menuVertical;
    menu.querySelectorAll('a.active').forEach((element) => {
      if (element.nextElementSibling && element.nextElementSibling.tagName === 'UL') {
        element.setAttribute('data-clicked', true);
        element.setAttribute('aria-expanded', true);
        element.nextElementSibling.classList.add('show');
      }
    });
    this._destroyScrollbar();
    this._initScrollbar();
    this._initOtherDropdownsVertical();
  }

  // Using popperjs to align user,language and notification dropdowns in the menu
  _initOtherDropdownsVertical() {
    this.userDropdown && this.userDropdown.dispose();
    this.languageDropdown && this.languageDropdown.dispose();
    this.notificationDropdown && this.notificationDropdown.dispose();

    if (document.querySelector('.user-container .user')) {
      this.userDropdown = new bootstrap.Dropdown(document.querySelector('.user-container .user'), {
        popperConfig: function (defaultBsPopperConfig) {
          var newPopperConfig = {placement: 'bottom'};
          return newPopperConfig;
        },
      });
    }
    if (document.querySelector('.language-switch-container .language-button')) {
      this.languageDropdown = new bootstrap.Dropdown(document.querySelector('.language-switch-container .language-button'), {
        popperConfig: function (defaultBsPopperConfig) {
          var newPopperConfig = {placement: 'bottom'};
          return newPopperConfig;
        },
      });
    }
    if (document.querySelector('.menu-icons .notification-button')) {
      this.notificationDropdown = new bootstrap.Dropdown(document.querySelector('.menu-icons .notification-button'), {
        reference: document.querySelector('.menu-icons'),
        popperConfig: function (defaultBsPopperConfig) {
          var newPopperConfig = {placement: 'bottom'};
          return newPopperConfig;
        },
      });
    }
  }

  _hideOtherDropdownsVertical() {
    this.userDropdown && this.userDropdown.hide();
    this.languageDropdown && this.languageDropdown.hide();
    this.notificationDropdown && this.notificationDropdown.hide();
  }

  _addVerticalMenuListeners() {
    this._removeVerticalMenuListeners();
    document.querySelectorAll('#menu .collapse').forEach((element) => {
      element.addEventListener('show.bs.collapse', this._onCollapseShow);
    });
    document.querySelectorAll('#menu .collapse').forEach((element) => {
      element.addEventListener('hide.bs.collapse', this._onCollapseHide);
    });
    document.querySelectorAll('#menu .collapse').forEach((element) => {
      element.addEventListener('shown.bs.collapse', this._onCollapseShown);
    });
    document.querySelector('#menu').addEventListener('click', this._onVerticalMenuClick);
  }

  _removeVerticalMenuListeners() {
    document.querySelector('#menu').removeEventListener('click', this._onVerticalMenuClick);
    document.querySelectorAll('#menu .collapse').forEach((element) => {
      element.removeEventListener('show.bs.collapse', this._onCollapseShow);
    });
    document.querySelectorAll('#menu .collapse').forEach((element) => {
      element.removeEventListener('hide.bs.collapse', this._onCollapseHide);
    });
    document.querySelectorAll('#menu .collapse').forEach((element) => {
      element.removeEventListener('shown.bs.collapse', this._onCollapseShown);
    });
  }

  _onCollapseHide(event) {
    const anchor = event.target.previousElementSibling;
    if (!anchor || anchor.tagName !== 'A') {
      return;
    }
    anchor.setAttribute('aria-expanded', false);
  }

  _onCollapseShow(event) {
    const anchor = event.target.previousElementSibling;
    if (!anchor || anchor.tagName !== 'A') {
      return;
    }
    anchor.setAttribute('aria-expanded', true);
  }

  _onCollapseShown(event) {
    const anchor = event.target.previousElementSibling;
    if (!anchor || anchor.tagName !== 'A') {
      return;
    }
    anchor.setAttribute('data-clicked', true);
  }

  _onVerticalMenuClick(event) {
    const anchor = event.target.closest('a');
    if (!anchor) {
      return;
    }
    if (anchor.getAttribute('data-clicked') === 'true') {
      anchor.removeAttribute('data-clicked');
    }
  }

  // Converts menu ul li structure to Bootstrap dropdowns and appends it in the #menu
  _addHorizontalMenu() {
    document.querySelector('#menu').remove();
    this._initMenuVariables();
    const menu = this.menuHorizontal;
    menu.querySelectorAll('li').forEach((element) => {
      if (element.querySelector(':scope > ul')) {
        element.classList.add('dropdown');
      }
    });

    menu.querySelectorAll(':scope > li').forEach((element) => {
      let anchorSelector = 'a';
      if (element.classList.contains('mega')) {
        anchorSelector = ':scope > a';
        const colsCount = element.querySelectorAll(':scope > ul>li').length;

        element.querySelectorAll(':scope > ul').forEach((element) => {
          element.classList.add('row');
          element.classList.add('align-items-start');
          element.classList.add('row-cols-' + colsCount);
          element.querySelectorAll('li').forEach((innerElement) => {
            innerElement.classList.add('col');
            innerElement.classList.add('d-flex');
            innerElement.classList.add('flex-column');
          });
        });
      }

      element.querySelectorAll(anchorSelector).forEach((element) => {
        if (element.nextElementSibling && element.nextElementSibling.tagName === 'UL') {
          element.setAttribute('href', '#');
          element.nextElementSibling.classList.add('dropdown-menu');
          element.nextElementSibling.classList.add('opacityIn');
        }
      });

      element.querySelectorAll('a').forEach((element) => {
        if (element.nextElementSibling && element.nextElementSibling.tagName === 'UL') {
          element.classList.add('dropdown-toggle');
        }
      });

      element.querySelectorAll(':scope > a').forEach((element) => {
        if (element.nextElementSibling && element.nextElementSibling.tagName === 'UL') {
          element.setAttribute('data-bs-toggle', 'dropdown');
        }
      });
    });

    this.menuContainer.insertAdjacentElement('beforeend', this.menuHorizontal);
    document.querySelector('#menu').classList.add('show');

    this._matchUrl();
    this._initHorizontalMenu();
    this._initIcons();
  }

  // Initializes the horizontal menu
  // Customizes dropdown clicks to prevent auto closing and making sure all sub menus are closed when parent is closed
  _initHorizontalMenu() {
    this._removeVerticalMenuListeners();
    this._addHorizontalMenuListeners();
    this._destroyScrollbar();
    this._initOtherDropdownsHorizontal();
  }

  // Using popperjs to align user,language and notification dropdowns in the menu
  _initOtherDropdownsHorizontal() {
    this.userDropdown && this.userDropdown.dispose();
    this.languageDropdown && this.languageDropdown.dispose();
    this.notificationDropdown && this.notificationDropdown.dispose();

    if (document.querySelector('.user-container .user')) {
      this.userDropdown = new bootstrap.Dropdown(document.querySelector('.user-container .user'), {
        popperConfig: function (defaultBsPopperConfig) {
          var newPopperConfig = {placement: 'bottom-end'};
          return newPopperConfig;
        },
      });
    }

    if (document.querySelector('.language-switch-container .language-button')) {
      this.languageDropdown = new bootstrap.Dropdown(document.querySelector('.language-switch-container .language-button'), {
        popperConfig: function (defaultBsPopperConfig) {
          var newPopperConfig = {placement: 'bottom-end'};
          return newPopperConfig;
        },
      });
    }

    if (document.querySelector('.menu-icons .notification-button')) {
      this.notificationDropdown = new bootstrap.Dropdown(document.querySelector('.menu-icons .notification-button'), {
        popperConfig: function (defaultBsPopperConfig) {
          var newPopperConfig = {placement: 'bottom-end'};
          return newPopperConfig;
        },
      });
    }
  }

  _initIcons() {
    if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
  }

  _removeHorizontalMenuListeners() {
    document.querySelectorAll('#menu > li').forEach((element) => {
      element.removeEventListener('hidden.bs.dropdown', this._onHorizontalMenuDropdownHidden);
    });

    document.querySelectorAll('#menu .dropdown-menu a.dropdown-toggle').forEach((element) => {
      element.removeEventListener('click', this._onHorizontalMenuDropdownClick);
    });
  }

  _addHorizontalMenuListeners() {
    this._removeHorizontalMenuListeners();
    document.querySelectorAll('#menu > li').forEach((element) => {
      element.addEventListener('hidden.bs.dropdown', this._onHorizontalMenuDropdownHidden);
    });

    document.querySelectorAll('#menu .dropdown-menu a.dropdown-toggle').forEach((element) => {
      element.addEventListener('click', this._onHorizontalMenuDropdownClick);
    });
  }

  _onHorizontalMenuDropdownClick(event) {
    const element = event.currentTarget;
    event.stopPropagation();
    event.preventDefault();
    const openSubmenu = element.closest('.dropdown-menu').querySelector('.show');
    if (openSubmenu && !element.nextElementSibling.classList.contains('show')) {
      openSubmenu.classList.remove('show');
      openSubmenu.querySelectorAll('.dropdown').forEach((openSubMenuDropdown) => {
        openSubMenuDropdown.classList.remove('show');
      });
      openSubmenu.querySelectorAll('.dropdown-menu').forEach((openSubMenuDropdownMenu) => {
        openSubMenuDropdownMenu.classList.remove('show');
      });
    }

    if (element.nextElementSibling.tagName === 'UL') {
      if (element.nextElementSibling.classList.contains('show')) {
        element.nextElementSibling.classList.remove('show');
        element.parentNode.classList.remove('show');
      } else {
        element.nextElementSibling.classList.add('show');
        element.parentNode.classList.add('show');
      }
    }

    return false;
  }

  _onHorizontalMenuDropdownHidden(event) {
    this._hideDropdowns();
  }

  // Checks if the current url is matching with the nav item href
  _matchUrl() {
    if (this.settings.matchUrl) {
      this._matchUrlByMenu('#menu');
      this._matchUrlByMenu('#menuSide');
    }
  }

  _matchUrlByMenu(selector) {
    const url = window.location.pathname.toLowerCase();
    const urlWithoutBackslash = url.replace(/^\/+/g, '').replace(/\.[^/.]+$/, '');
    let active;
    document.querySelectorAll(selector + ' a').forEach((el) => {
      const href = el
        .getAttribute('href')
        .toLowerCase()
        .replace(/^\/+/g, '')
        .replace(/\.[^/.]+$/, '');
      const hrefData =
        el.getAttribute('data-href') &&
        el
          .getAttribute('data-href')
          .toLowerCase()
          .replace(/^\/+/g, '')
          .replace(/\.[^/.]+$/, '');
      if (urlWithoutBackslash.includes(href) || urlWithoutBackslash.includes(hrefData)) {
        el.classList.add('active');
        active = el;
      }
    });

    if (!active) {
      return;
    }
    var parents = [];
    var activeLooped = active;
    while (activeLooped) {
      activeLooped = activeLooped.parentNode;
      // Checking for the injected side menu
      if (activeLooped.matches(selector)) {
        break;
      }
      if (activeLooped.matches('ul')) {
        parents.unshift(activeLooped);
      }
    }
    parents.forEach((el) => {
      if (el.previousElementSibling.matches('a')) {
        el.previousElementSibling.classList.add('active');
      }
    });
  }

  // Vertical menu scrollbar init
  _initScrollbar() {
    if (typeof OverlayScrollbars !== 'undefined') {
      this.scrollbar = OverlayScrollbars(document.querySelectorAll('.menu-container'), {
        scrollbars: {autoHide: 'leave', autoHideDelay: 600},
        overflowBehavior: {x: 'hidden', y: 'scroll'},
      });
    }
  }

  // Vertical menu scrollbar destroy
  _destroyScrollbar() {
    if (this.scrollbar) {
      this.scrollbar.destroy();
      this.scrollbar = null;
    }
  }

  // Outside of the menu click check for mobile menu
  _onWindowClick(event) {
    if (this.element.classList.contains('mobile-side-in') && !event.target.closest('#nav')) {
      this._hideMobileMenu();
    }
  }

  // Decides which type of menu to add based on the parameters or the current window size.
  // placementStatus:
  // 1 {selected: 'horizontal',  dimension: 'mobile',         html-attr: 'horizontal', render: 'vertical'}
  // 2 {selected: 'horizontal',  dimension: 'tablet|desktop', html-attr: 'horizontal', render: 'horizontal'}
  // 3 {selected: 'vertical',    dimension: 'mobile',         html-attr: 'horizontal', render: 'vertical' }
  // 4 {selected: 'vertical',    dimension: 'tablet|desktop', html-attr: 'vertical',   render: 'vertical' }
  _initMenuPlacement() {
    var windowWidth = window.innerWidth;
    var previousPlacementStatus = this.placementStatus;
    this._hideOtherDropdownsVertical();
    if (this.selectedMenuPlacement === 'horizontal') {
      if (this.horizontalMobile > windowWidth) {
        // Adding vertical menu for mobile
        if (this.placementStatus !== 1) {
          this.html.setAttribute('data-placement', 'horizontal');
          this.html.setAttribute('data-dimension', 'mobile');
          this._addVerticalMenu();
          this.placementStatus = 1;
          this._dispatchMobileEvent();
        }
      } else {
        // Adding horizontal menu for desktop
        if (this.placementStatus !== 2) {
          this._hideMobileMenuQuick();
          this._addHorizontalMenu();
          this.html.setAttribute('data-dimension', 'desktop');
          this.html.setAttribute('data-placement', 'horizontal');
          this.placementStatus = 2;
          this._dispatchDesktopEvent();
        }
      }
    }

    if (this.selectedMenuPlacement === 'vertical') {
      if (this.verticalMobile > windowWidth) {
        // Adding vertical menu for mobile
        if (this.placementStatus !== 3) {
          this.html.setAttribute('data-dimension', 'mobile');
          this.html.setAttribute('data-placement', 'horizontal');
          this._addVerticalMenu();
          this.placementStatus = 3;
          this._dispatchMobileEvent();
        }
      } else {
        // Adding vertical menu for desktop
        if (this.placementStatus !== 4) {
          this._hideMobileMenuQuick();
          this.html.setAttribute('data-dimension', 'desktop');
          this.html.setAttribute('data-placement', 'vertical');
          this._addVerticalMenu();
          this.placementStatus = 4;
          this._dispatchDesktopEvent();
        }
      }
    }
    this._initMenuBehaviour();
    if (previousPlacementStatus !== this.placementStatus) {
      this._removeAnimationAttributes();
    }
  }

  // Decides which type of menu behaviour to init based on the parameters or the current window size.
  //  behaviourStatus:
  //  1 {selected: 'unpinned', placement: 'vertical',   dimension: 'mobile|desktop',  html-attr: 'unpinned'}
  //  2 {selected: 'unpinned', placement: 'vertical',   dimension: 'tablet',          html-attr: 'unpinned'}
  //  3 {selected: 'pinned',   placement: 'vertical',   dimension: 'mobile|desktop',  html-attr: 'pinned'}
  //  4 {selected: 'pinned',   placement: 'vertical',   dimension: 'tablet',          html-attr: 'unpinned'}
  //  5 {selected: 'unpinned', placement: 'horizontal', dimension: 'all',             html-attr: 'unpinned'}
  //  6 {selected: 'pinned',   placement: 'horizontal', dimension: 'all',             html-attr: 'pinned'}
  _initMenuBehaviour() {
    var previousBehaviourStatus = this.behaviourStatus;
    var windowWidth = window.innerWidth;
    var menuPlacement = this.html.getAttribute('data-placement');
    // Vertical rules
    if (menuPlacement === 'vertical' && this.selectedMenuBehaviour === 'unpinned') {
      if (this.verticalMobile > windowWidth || this.verticalUnpinned <= windowWidth) {
        // Mobile and Desktop
        if (this.behaviourStatus !== 1) {
          // A small fix to make sure nav config at Vertical No Semi Hidden overrides theme settings.
          if (this.verticalUnpinned !== this.verticalMobile) {
            this.html.setAttribute('data-behaviour', 'unpinned');
          } else {
            this.html.setAttribute('data-behaviour', 'pinned');
          }
          this._enablePinButton();
          this.behaviourStatus = 1;
          this._hideShowMenu();
        }
      } else {
        // Tablet
        if (this.behaviourStatus !== 2) {
          this.html.setAttribute('data-behaviour', 'unpinned');
          this._disablePinButton();
          this.behaviourStatus = 2;
          this._hideShowMenu();
        }
      }
    }
    if (menuPlacement === 'vertical' && this.selectedMenuBehaviour === 'pinned') {
      if (this.verticalMobile > windowWidth || this.verticalUnpinned <= windowWidth) {
        // Mobile and Desktop
        if (this.behaviourStatus !== 3) {
          this.html.setAttribute('data-behaviour', 'pinned');
          this._enablePinButton();
          this.behaviourStatus = 3;
          this._unCollapseMenu();
        }
      } else {
        // Tablet
        if (this.behaviourStatus !== 4) {
          this.html.setAttribute('data-behaviour', 'unpinned');
          this._disablePinButton();
          this.behaviourStatus = 4;
          this._hideShowMenu();
        }
      }
    }

    // Horizontal rules
    if (menuPlacement === 'horizontal' && this.selectedMenuBehaviour === 'unpinned') {
      if (this.behaviourStatus !== 5) {
        this.html.setAttribute('data-behaviour', 'unpinned');
        this._enablePinButton();
        this.behaviourStatus = 5;
      }
    }
    if (menuPlacement === 'horizontal' && this.selectedMenuBehaviour === 'pinned') {
      if (this.behaviourStatus !== 6) {
        this.html.setAttribute('data-behaviour', 'pinned');
        this._enablePinButton();
        this.behaviourStatus = 6;
      }
    }

    if (previousBehaviourStatus !== this.behaviourStatus) {
      this._removeAnimationAttributes();
    }
  }

  // Prevents menu animation to make a fast switch
  _removeAnimationAttributes() {
    this.html.removeAttribute('data-menu-animate');
  }

  // Collapses menu for semi hidden vertical menu
  _collapseMenu() {
    document.querySelectorAll('#menu>li>a').forEach((element) => {
      if (element.getAttribute('data-clicked') === 'true') {
        const collapse = bootstrap.Collapse.getInstance(element.nextElementSibling);
        if (collapse) {
          collapse.hide();
        }
      }
    });
    this._hideDropdowns();
  }

  // Uncollapses menu for swithing back to normal from semi hidden vertical menu
  _unCollapseMenu() {
    document.querySelectorAll('#menu>li>a').forEach((element) => {
      if (element.getAttribute('data-clicked') === 'true') {
        const collapse = bootstrap.Collapse.getInstance(element.nextElementSibling);
        if (collapse) {
          collapse.show();
        }
      }
    });
  }

  // Hiding all dropdowns to make sure they are closed when menu collapses
  _hideDropdowns() {
    const dropdownElementList = [].slice.call(document.querySelectorAll('#menu>li>ul [data-bs-toggle="dropdown"]'));
    dropdownElementList.map(function (dropdownToggleEl) {
      if (dropdownToggleEl.classList.contains('show')) {
        const dropdown = bootstrap.Dropdown.getInstance(dropdownToggleEl);
        if (dropdown) {
          dropdown.hide();
        }
      }
    });

    document.querySelectorAll('.dropdown-menu .show').forEach((element) => {
      element.classList.remove('show');
      if (element.closest('ul')) {
        element.closest('ul').classList.remove('show');
      }
    });

    this._hideOtherDropdownsVertical();
  }

  // Enables pin button.
  _enablePinButton() {
    if (this.settings.disablePinning) {
      return;
    }
    this.pinButton && this.pinButton.classList.remove('disabled');
  }

  // Disables pin button. It is disabled for the vertical menu when the screen size is smaller but not mobile.
  _disablePinButton() {
    this.pinButton && this.pinButton.classList.add('disabled');
  }

  // Resize handler
  _onWindowResize(event) {
    this._initMenuPlacement();
  }

  // Hides or shows the vertical menu based on the behaviour and window size
  _hideShowMenu() {
    if (
      this.html.getAttribute('data-placement') === 'vertical' &&
      this.html.getAttribute('data-mobile') !== 'true' &&
      this.html.getAttribute('data-behaviour') === 'unpinned'
    ) {
      var collapsing = false;
      document.querySelectorAll('#menu>li>a').forEach((element) => {
        if (element.nextElementSibling && element.nextElementSibling.tagName === 'UL' && element.nextElementSibling.classList.contains('collapsing')) {
          collapsing = true;
        }
      });

      if (collapsing) {
        this._hideShowMenuDelayed();
        return;
      }

      if (this.html.getAttribute('data-menu-animate') === 'show') {
        this._unCollapseMenu();
      } else {
        this._collapseMenu();
      }
    }
    clearTimeout(this.collapseTimeout);
  }

  // Delayed one that hides or shows the menu. It's required to prevent collapse animation getting stucked
  _hideShowMenuDelayed() {
    if (this.collapseTimeout) {
      clearTimeout(this.collapseTimeout);
    }
    this.collapseTimeout = setTimeout(() => {
      this._hideShowMenu();
    }, 60);
  }

  // Vertical menu semihidden state showing
  // Only works when the vertical menu is active and mobile menu closed
  _onMouseEnter(event) {
    if (
      this.html.getAttribute('data-placement') === 'vertical' &&
      this.html.getAttribute('data-mobile') !== 'true' &&
      this.html.getAttribute('data-behaviour') === 'unpinned'
    ) {
      this.html.setAttribute('data-menu-animate', 'show');
      // Centering dropdowns again after the menu is shown
      setTimeout(() => {
        this._initOtherDropdownsVertical();
      }, Globals.transitionTime);
      this._hideShowMenuDelayed();
    }
  }

  // Vertical menu semihidden state hiding
  // Only works when the vertical menu is active and mobile menu closed
  _onMouseLeave(event) {
    if (
      this.html.getAttribute('data-placement') === 'vertical' &&
      this.html.getAttribute('data-mobile') !== 'true' &&
      this.html.getAttribute('data-behaviour') === 'unpinned'
    ) {
      this.html.setAttribute('data-menu-animate', 'hidden');
      this._hideShowMenuDelayed();
      this._hideOtherDropdownsVertical();
    }
  }

  // Listens to menu placement event and updates menu
  _onMenuPlacementChange(event) {
    this.selectedMenuPlacement = event.detail;
    this._initMenuPlacement();
  }

  // Menu pin behaviour change
  _onMenuBehaviourChange(event) {
    this.selectedMenuBehaviour = event.detail;
    this._initMenuBehaviour();
  }

  _onWindowScroll(event) {
    this.windowScrolled = true;
  }

  // Horizontal menu hiding and showing based on menu behaviour and scroll position
  _onWindowScrollInterval() {
    if (this.windowScrolled) {
      var currentScrollPos = window.pageYOffset;
      var navSize = this.element.offsetHeight;
      this.windowScrolled = false;
      if (Math.abs(this.prevScrollpos - currentScrollPos) <= navSize && currentScrollPos > navSize) {
        this.prevScrollpos = currentScrollPos;
        return;
      }
      if (
        this.html.getAttribute('data-placement') === 'horizontal' &&
        this.html.getAttribute('data-mobile') !== 'true' &&
        this.html.getAttribute('data-behaviour') === 'unpinned'
      ) {
        if (this.prevScrollpos > currentScrollPos || currentScrollPos <= navSize) {
          this._removeAnimationAttributes();
        } else if (this.prevScrollpos <= currentScrollPos && currentScrollPos > navSize) {
          this.html.setAttribute('data-menu-animate', 'hidden');
          this._hideDropdowns();
        }
      }
      this.prevScrollpos = currentScrollPos;
    }
  }

  _onPinClick(event) {
    event.preventDefault();
    if (this.pinButton.classList.contains('disabled')) {
      return;
    }
    // Dispatching an event for settings to handle change
    this.html.dispatchEvent(new CustomEvent(Globals.pinButtonClick));
  }

  _onColorClick(event) {
    event.preventDefault();
    // Dispatching an event for settings to handle change
    this.html.dispatchEvent(new CustomEvent(Globals.lightDarkModeClick));
  }

  // Starts mobile menu opening sequence
  _showMobileMenu(event) {
    event.preventDefault();
    this.html.setAttribute('data-mobile', 'true');
    this.element.classList.add('mobile-top-out');
    this.element.classList.remove('mobile-top-in');
    this.element.classList.remove('mobile-top-ready');
    setTimeout(() => {
      this.element.classList.remove('mobile-top-out');
      this.element.classList.add('mobile-side-ready');
    }, 200);
    setTimeout(() => {
      this.element.classList.add('mobile-side-in');
    }, 230);
  }

  // Starts mobile menu closing sequence
  _hideMobileMenu() {
    this.element.classList.add('mobile-side-out');
    this.element.classList.remove('mobile-side-in');
    setTimeout(() => {
      this.element.classList.remove('mobile-side-ready');
      this.element.classList.remove('mobile-side-out');
      this.element.classList.add('mobile-top-ready');
    }, 200);
    setTimeout(() => {
      this.element.classList.add('mobile-top-in');
      this.html.removeAttribute('data-mobile');
    }, 230);
  }

  // Switching back from the mobile menu layout fast
  _hideMobileMenuQuick() {
    this.element.classList.remove('mobile-top-out');
    this.element.classList.remove('mobile-top-in');
    this.element.classList.remove('mobile-top-ready');
    this.element.classList.remove('mobile-side-in');
    this.element.classList.remove('mobile-side-ready');
    this.element.classList.remove('mobile-side-out');
    this.html.removeAttribute('data-mobile');
  }

  // Dispatches a global event to broadcast that the project switched to the desktop menu
  _dispatchDesktopEvent() {
    this.html.dispatchEvent(new CustomEvent(Globals.switchedToDesktop));
  }

  // Dispatches a global event to broadcast that the project switched to the mobile menu
  _dispatchMobileEvent() {
    this.html.dispatchEvent(new CustomEvent(Globals.switchedToMobile));
  }
}
