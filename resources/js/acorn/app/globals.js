/**
 *
 * Globals
 * Global variables and event names
 *
 *
 **/

// A global variable to share variables accross multiple files
var Globals = Globals || {};

// Global Events
Globals.menuPlacementChange = 'MENU_PLACEMENT_CHANGE';
Globals.menuBehaviourChange = 'MENU_BEHAVIOUR_CHANGE';
Globals.layoutChange = 'LAYOUT_CHANGE';
Globals.colorAttributeChange = 'COLOR_ATTRIBUTE_CHANGE';
Globals.borderRadiusChange = 'BORDER_RADIUS_CHANGE';
Globals.lightDarkModeClick = 'LIGHT_DARK_MODE_CLICK';
Globals.pinButtonClick = 'PIN_BUTTON_CLICK';
Globals.switchedToMobile = 'SWITCHED_TO_MOBILE';
Globals.switchedToDesktop = 'SWITCHED_TO_DESKTOP';

// A helper class to init, update and map css root variables to javascript
class Variables {
  constructor() {
    this._addListeners();
    this._initVariables();
  }

  _addListeners() {
    document.documentElement.addEventListener(Globals.colorAttributeChange, (event) => {
      this._initVariables();
    });

    document.documentElement.addEventListener(Globals.borderRadiusChange, (event) => {
      this._initVariables();
    });
  }

  _initVariables() {
    var rootStyle = getComputedStyle(document.body);
    Globals.primary = rootStyle.getPropertyValue('--primary').trim();
    Globals.secondary = rootStyle.getPropertyValue('--secondary').trim();
    Globals.tertiary = rootStyle.getPropertyValue('--tertiary').trim();
    Globals.quaternary = rootStyle.getPropertyValue('--quaternary').trim();
    Globals.body = rootStyle.getPropertyValue('--body').trim();
    Globals.alternate = rootStyle.getPropertyValue('--alternate').trim();
    Globals.lightText = rootStyle.getPropertyValue('--light-text').trim();
    Globals.warning = rootStyle.getPropertyValue('--warning').trim();
    Globals.danger = rootStyle.getPropertyValue('--danger').trim();
    Globals.success = rootStyle.getPropertyValue('--success').trim();
    Globals.info = rootStyle.getPropertyValue('--info').trim();

    Globals.font = rootStyle.getPropertyValue('--font').trim();
    Globals.fontHeading = rootStyle.getPropertyValue('--font-heading').trim();

    Globals.background = rootStyle.getPropertyValue('--background').trim();
    Globals.foreground = rootStyle.getPropertyValue('--foreground').trim();
    Globals.separator = rootStyle.getPropertyValue('--separator').trim();
    Globals.separatorLight = rootStyle.getPropertyValue('--separator-light').trim();

    Globals.transitionTimeShort = rootStyle.getPropertyValue('--transition-time-short').trim().replace('ms', '');
    Globals.transitionTime = rootStyle.getPropertyValue('--transition-time').trim().replace('ms', '');
    Globals.navSizeSlim = rootStyle.getPropertyValue('--nav-size-slim').trim();

    Globals.primaryrgb = rootStyle.getPropertyValue('--primary-rgb').trim();
    Globals.secondaryrgb = rootStyle.getPropertyValue('--secondary-rgb').trim();
    Globals.tertiaryrgb = rootStyle.getPropertyValue('--tertiary-rgb').trim();
    Globals.quaternaryrgb = rootStyle.getPropertyValue('--quaternary-rgb').trim();

    Globals.borderRadiusXl = rootStyle.getPropertyValue('--border-radius-xl').trim();
    Globals.borderRadiusLg = rootStyle.getPropertyValue('--border-radius-lg').trim();
    Globals.borderRadiusMd = rootStyle.getPropertyValue('--border-radius-md').trim();
    Globals.borderRadiusSm = rootStyle.getPropertyValue('--border-radius-sm').trim();

    Globals.sm = rootStyle.getPropertyValue('--sm').trim();
    Globals.md = rootStyle.getPropertyValue('--md').trim();
    Globals.lg = rootStyle.getPropertyValue('--lg').trim();
    Globals.xl = rootStyle.getPropertyValue('--xl').trim();
    Globals.xxl = rootStyle.getPropertyValue('--xxl').trim();
    Globals.direction = 'ltr';
  }
}
