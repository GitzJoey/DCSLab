/**
 *
 * Common.js
 *
 * Initialization and overriding of project wide plugins.
 *
 *
 */

class Common {
  get options() {
    return {};
  }

  constructor(options = {}) {
    this.settings = Object.assign(this.options, options);
    this._init();
  }

  _init() {
    this._initScrolls();
    this._initModalPadding();
    this._initTooltips();
    this._initPopovers();
    this._initToasts();
    this._initDropdownAsSelect();
    this._initDropdownSubMenu();
    this._initClamp();
    this._initScrollspy();
    this._setQuillDefaults();
    this._setDatePickerDefaults();
    this._setValidationDefaults();
    this._setNotifyDefaults();
    this._setSelect2Defaults();
    this._momentWarnings();
  }

  // Bootstrap tooltips
  _initTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl, {
        delay: {show: 1000, hide: 0},
      });
    });

    var tooltipTriggerListNoDelay = [].slice.call(document.querySelectorAll('.no-delay[data-bs-toggle="tooltip"][data-bs-delay="0"]'));
    var tooltipListNoDelay = tooltipTriggerListNoDelay.map(function (tooltipTriggerElNoDelay) {
      return new bootstrap.Tooltip(tooltipTriggerElNoDelay, {
        delay: {show: 0, hide: 0},
      });
    });
  }

  // Bootstrap popovers
  _initPopovers() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl);
    });
  }

  // Bootstrap toasts
  _initToasts() {
    var toastElList = [].slice.call(document.querySelectorAll('.toast'));
    var toastList = toastElList.map(function (toastEl) {
      return new bootstrap.Toast(toastEl);
    });
  }

  // Dropdown submenu
  _initDropdownSubMenu() {
    if (jQuery().submenupicker) {
      jQuery('[data-submenu]').submenupicker();
    }
  }

  // Clamp plugin
  _initClamp() {
    if (typeof $clamp !== 'undefined') {
      document.querySelectorAll('.clamp').forEach((el) => {
        $clamp(el, {clamp: 'auto'});
      });
      document.querySelectorAll('.clamp-line').forEach((el) => {
        const line = el.getAttribute('data-line');
        if (line) {
          $clamp(el, {clamp: parseInt(line)});
        }
      });
    }
  }

  // Scrollspy implementation for interface content
  _initScrollspy() {
    if (typeof ScrollSpy !== 'undefined') {
      const scrollSpy = new ScrollSpy();
    }
  }

  // Initialization of scrollbars throughout the project
  _initScrolls() {
    if (typeof OverlayScrollbars !== 'undefined') {
      const tempScrolls = [];
      OverlayScrollbars(document.querySelectorAll('.scroll'), {
        scrollbars: {autoHide: 'leave', autoHideDelay: 600},
        overflowBehavior: {x: 'hidden', y: 'scroll'},
      });
      OverlayScrollbars(document.querySelectorAll('.scroll-horizontal'), {
        scrollbars: {autoHide: 'leave', autoHideDelay: 600},
        overflowBehavior: {x: 'scroll', y: 'hidden'},
      });
      OverlayScrollbars(document.querySelectorAll('.data-table-rows .table-container'), {
        overflowBehavior: {x: 'scroll', y: 'hidden'},
      });
      OverlayScrollbars(document.querySelectorAll('.scroll-track-visible'), {overflowBehavior: {x: 'hidden', y: 'scroll'}});
      OverlayScrollbars(document.querySelectorAll('.scroll-horizontal-track-visible'), {
        overflowBehavior: {x: 'scroll', y: 'hidden'},
      });
      document.querySelectorAll('.scroll-by-count').forEach((el) => {
        if (typeof ScrollbarByCount === 'undefined') {
          console.log('ScrollbarByCount is undefined!');
          return;
        }
        let scrollByCount = new ScrollbarByCount(el);
      });
    }
  }

  // Dropdown as select implementation
  _initDropdownAsSelect() {
    // data-childSelector provides a way to set inner item selector of button. Used as a span in datatables per page count to provide both data-bs-toggle=dropdown and data-bs-toggle=tooltip.
    // data-setActive provides a way to enable or disable setting active class. Used in tab navigation dropdowns since tabs should set active class themself otherwise they don't work.
    document.querySelectorAll('.dropdown-as-select .dropdown-menu a').forEach((element) => {
      element.addEventListener('click', (event) => {
        event.preventDefault();
        const currentTarget = event.currentTarget;
        const selText = currentTarget.textContent;
        const parent = currentTarget.closest('.dropdown-as-select');
        let childSelector = '';
        if (parent.getAttribute('data-childSelector')) {
          childSelector = ' ' + parent.getAttribute('data-childSelector');
        }
        parent.querySelector('[data-bs-toggle="dropdown"]' + childSelector).innerHTML = selText;
        parent.querySelectorAll('a').forEach((anchor) => {
          anchor.classList.remove('active');
        });
        if (parent.getAttribute('data-setActive') !== 'false') {
          currentTarget.classList.add('active');
        }
      });
    });
    document.querySelectorAll('.dropdown-as-select').forEach((element) => {
      let childSelector = '';
      if (element.getAttribute('data-childSelector')) {
        childSelector = ' ' + element.getAttribute('data-childSelector');
      }
      element.querySelector('[data-bs-toggle="dropdown"]' + childSelector).innerHTML = element.querySelector('.dropdown-menu a.active').textContent;
    });
  }

  _initModalPadding() {
    // Adding a modal and removing it to get Bootstrap's padding right value for the page
    document.body.insertAdjacentHTML(
      'afterbegin',
      '<div id="paddingModal" class="modal"> <div class="modal-dialog d-none"><div class="modal-content"></div></div> </div>',
    );
    const myModalEl = document.getElementById('paddingModal');
    const paddingModal = new bootstrap.Modal(myModalEl, {backdrop: false});
    myModalEl.addEventListener('shown.bs.modal', function (event) {
      const rightPadding = document.body.style.paddingRight;
      document.body.setAttribute('data-bs-padding', rightPadding);
      paddingModal.hide();
    });
    paddingModal.show();

    document.querySelectorAll('.modal').forEach((el) => {
      // Adding the stored padding to horizontal nav to prevent unnecessary movement of content when any modal is opened
      el.addEventListener('show.bs.modal', function (event) {
        const rightPadding = document.body.getAttribute('data-bs-padding');
        if (document.querySelector('html[data-placement="horizontal"] .nav-container .nav-content')) {
          document.querySelector('html[data-placement="horizontal"] .nav-container .nav-content').style.paddingRight = rightPadding;
        }
      });

      // Removing padding when any modal is closed
      el.addEventListener('hidden.bs.modal', function (event) {
        if (document.querySelector('.nav-container .nav-content')) {
          document.querySelector('.nav-container .nav-content').style.paddingRight = 0;
        }
      });
    });

    document.querySelectorAll('.offcanvas').forEach((el) => {
      // Adding the stored padding to horizontal nav to prevent unnecessary movement of content when any offcanvas is opened
      el.addEventListener('show.bs.offcanvas', function (event) {
        const rightPadding = document.body.getAttribute('data-bs-padding');
        if (document.querySelector('html[data-placement="horizontal"] .nav-container .nav-content')) {
          document.querySelector('html[data-placement="horizontal"] .nav-container .nav-content').style.paddingRight = rightPadding;
        }
      });

      // Removing padding when any offcanvas is closed
      el.addEventListener('hidden.bs.offcanvas', function (event) {
        if (document.querySelector('.nav-container .nav-content')) {
          document.querySelector('.nav-container .nav-content').style.paddingRight = 0;
        }
      });
    });
  }

  // Activating quill module
  _setQuillDefaults() {
    if (typeof Quill !== 'undefined') {
      Quill.register('modules/active', Active);
    }
  }

  // Datepicker defaults
  _setDatePickerDefaults() {
    if (jQuery().datepicker) {
      jQuery.fn.datepicker.defaults.templates = {
        leftArrow: '<i class="cs-chevron-left"></i>',
        rightArrow: '<i class="cs-chevron-right"></i>',
      };
    }
  }

  // Notify defaults
  _setNotifyDefaults() {
    if (jQuery.notify) {
      jQuery.notifyDefaults({
        template:
          '<div data-notify="container" class="col-10 col-sm-6 col-xl-3 alert  alert-{0} " role="alert">' +
          '<button type="button" aria-hidden="true" class="btn-close" data-notify="dismiss"></button>' +
          '<span data-notify="icon"></span> ' +
          '<span data-notify="title">{1}</span> ' +
          '<span data-notify="message">{2}</span>' +
          '<div class="progress" data-notify="progressbar">' +
          '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
          '</div>' +
          '<a href="{3}" target="{4}" data-notify="url"></a>' +
          '</div>',
      });
    }
  }

  // Select2 theme
  _setSelect2Defaults() {
    if (jQuery.fn.select2) {
      jQuery.fn.select2.defaults.set('theme', 'bootstrap4');
    }
  }

  // Validate defaults
  _setValidationDefaults() {
    if (jQuery().validate) {
      jQuery.validator.setDefaults({
        ignore: [],
        errorElement: 'div',
        errorPlacement: function (error, element) {
          // Adding error in the parent if the element is checkbox or radio
          if (element.attr('class').indexOf('form-check-input') != -1) {
            error.insertAfter(element.parent());
          } else {
            error.insertAfter(element);
          }
          // Positioning the tooltip-label-end error based on the label width
          if (element.parents('.tooltip-label-end').length > 0) {
            if (element.parents('.form-group').find('.form-label').length > 0) {
              // Standard form element with a form-label
              let width = Math.round(element.parents('.form-group').find('.form-label').width()) + 10;
              jQuery(error).css('left', width);
            } else {
              // Form element withou a form-label such as single checkbox
              let width = Math.round(element.parents('.form-group').find('label').width()) + 40;
              jQuery(error).css('left', width);
            }
          }
        },
      });
      // Validator regex method
      jQuery.validator.addMethod(
        'regex',
        function (value, element, regexp) {
          return this.optional(element) || regexp.test(value);
        },
        'Please check your input.',
      );
    }
  }

  // Suppressing moment.js warning since they are related to other external plugin versions
  _momentWarnings() {
    if (typeof moment !== 'undefined') {
      moment.suppressDeprecationWarnings = true;
    }
  }
}
