/**
 *
 * Settings
 * Modifies and applies theme settings. Needs to be exist in the page even the page doesn't have settings modal.
 *
 * @param {Object} attributes            Object that contains settings for the theme.
 * @param {string} attributes.placement  Menu type, accepted values are "vertical", "horizontal".
 * @param {string} attributes.behaviour  Menu pin behaviour, accepted values are "pinned", "unpinned".
 * @param {string} attributes.layout     Layout mode, accepted values are "boxed", "fluid".
 * @param {string} attributes.radius     Border radius of the whole template, accepted values are "rounded", "standard", "flat".
 * @param {string} attributes.color      Theme color, accepted values are "light-blue", "light-sky", "light-teal", "light-green", "light-lime", "light-red", "light-pink", "light-purple", "dark-blue", "dark-sky", "dark-teal", "dark-green", "dark-lime", "dark-red", "dark-pink", "dark-purple".
 * @param {string} attributes.navcolor   Navcolor override, accepted values are default, "light", "dark".
 * @param {string} storagePrefix         Local storage key.
 * @param {boolean} showSettings         Hides settings button and panel when set to false.
 * @param {boolean} carryParams          Modifies anchor tag clicks and carries parameters from the current url to the new one. It's basicly for showing colors via an anchor.
 *
 * @method updateAttribute               Receives an id, value and updates the attribute.
 * @method getAttribute                  Receives an id and returns attribute.
 *
 * @event Globals.menuPlacementChange    Fired when placement value changes. Carries value in detail variable.
 * @event Globals.menuBehaviourChange    Fired when behaviour value changes. Carries value in detail variable.
 * @event Globals.colorAttributeChange   Fired when color value changes. Carries value in detail variable.
 * @event Globals.borderRadiusChange     Fired when border radius changes. Carries value in detail variable.
 * @event Globals.layoutChange           Fired when layout value changes to boxed or fluid.
 *
 *
 */

class Settings {
  get options() {
    return {
      attributes: {
        placement: 'vertical',
        behaviour: 'pinned',
        layout: 'fluid',
        radius: 'rounded',
        color: 'light-blue',
        navcolor: 'default',
      },
      storagePrefix: 'acorn-standard-',
      showSettings: true,
      carryParams: false,
    };
  }

  constructor(options = {}) {
    this.settings = Object.assign(this.options, options);
    this.settings.attributes = Object.assign(this.options.attributes, options.attributes);

    this.attributeOptions = {
      placement: {
        event: Globals.menuPlacementChange,
        update: false,
        attribute: 'data-placement',
      },
      behaviour: {
        event: Globals.menuBehaviourChange,
        update: false,
        attribute: 'data-behaviour',
      },
      layout: {
        event: Globals.layoutChange,
        update: true,
        attribute: 'data-layout',
      },
      radius: {
        event: Globals.borderRadiusChange,
        update: true,
        attribute: 'data-radius',
      },
      color: {
        event: Globals.colorAttributeChange,
        update: true,
        attribute: 'data-color',
      },
      navcolor: {
        event: null,
        update: true,
        attribute: 'data-navcolor',
      },
    };
    this.optionSelector = '#settings .option';
    this._init();
  }

  _init() {
    this._mergeOverridePrefix();
    this._mergeAttributesFromStorage();
    this._mergeOverrides();
    this._mergeUrlParameters();
    this._modifyLinksCarryParams();
    this._setAttributes();
    this._setActiveOptions();
    this._addListeners();
    this._setVisibility();
  }

  _mergeAttributesFromStorage() {
    for (const prop in this.settings.attributes) {
      if (localStorage.getItem(this.settings.storagePrefix + prop)) {
        this.settings.attributes[prop] = localStorage.getItem(this.settings.storagePrefix + prop);
      } else {
        localStorage.setItem(this.settings.storagePrefix + prop, this.settings.attributes[prop]);
      }
    }
  }

  // Sets overrides that are passed via override attribute over html or js
  // This provides a way to change settings for individual pages
  _mergeOverrides() {
    const overrideJSON = this._getOverrideJSON();
    if (!overrideJSON) {
      return;
    }
    const overridedAttributes = Object.assign(this.settings.attributes, overrideJSON.attributes);
    this.settings = Object.assign(this.settings, overrideJSON);
    this.settings.attributes = overridedAttributes;
  }

  // Merging prefix value before calling _mergeAttributesFromStorage()
  _mergeOverridePrefix() {
    const overrideJSON = this._getOverrideJSON();
    if (!overrideJSON) {
      return;
    }
    if (overrideJSON.storagePrefix) {
      this.settings.storagePrefix = overrideJSON.storagePrefix;
    }
  }

  _getOverrideJSON() {
    const override = document.documentElement.getAttribute('data-override');
    if (!override) {
      return null;
    }
    const overrideJSON = JSON.parse(override);
    return overrideJSON;
  }

  _mergeUrlParameters() {
    const queryString = window.location.search;
    const urlParamsDecoded = decodeURIComponent(queryString);
    const urlParams = new URLSearchParams(urlParamsDecoded);
    const urlJSON = JSON.parse(urlParams.get('params'));
    if (urlJSON && urlJSON.attributes) {
      const overridedAttributes = Object.assign(this.settings.attributes, urlJSON.attributes);
      this.settings = Object.assign(this.settings, urlJSON);
      this.settings.attributes = overridedAttributes;
    }
  }

  _modifyLinksCarryParams() {
    const queryString = window.location.search;
    if (queryString !== '') {
      document.addEventListener('click', (event) => {
        if (!this.settings.carryParams) {
          return;
        }
        const closestAnchor = event.target.closest('a');
        if (closestAnchor && !closestAnchor.getAttribute('href').includes('#')) {
          const queryString = window.location.search;
          document.location = closestAnchor.getAttribute('href') + queryString;
        }
        event.preventDefault();
      });
    }
  }

  _setAttributes() {
    for (const prop in this.settings.attributes) {
      document.documentElement.setAttribute(this.attributeOptions[prop].attribute, this.settings.attributes[prop]);
    }
  }

  _setActiveOptions() {
    this._clearActiveOptions();
    document.querySelectorAll(this.optionSelector).forEach((el, i) => {
      if (el.dataset.value === this.settings.attributes[el.dataset.parent]) {
        el.classList.add('active');
      }
    });
  }

  _clearActiveOptions() {
    document.querySelectorAll(this.optionSelector).forEach((el, i) => {
      el.classList.remove('active');
    });
  }

  _addListeners() {
    document.querySelectorAll(this.optionSelector).forEach((el, i) => {
      el.addEventListener('click', this._onOptionClick.bind(this));
    });
    document.documentElement.addEventListener(Globals.lightDarkModeClick, this._onLightDarkModeClick.bind(this));
    document.documentElement.addEventListener(Globals.pinButtonClick, this._onPinButtonClick.bind(this));
  }

  _onOptionClick(event) {
    event.preventDefault();
    const clickedEl = event.currentTarget;
    const clickedVal = clickedEl.dataset.value;
    const parentId = clickedEl.dataset.parent;
    this.updateAttribute(parentId, clickedVal);
  }

  _setVisibility() {
    if (!this.settings.showSettings) {
      document.getElementById('settings') && document.getElementById('settings').classList.add('d-none');
      document.querySelector('.settings-buttons-container') && document.querySelector('.settings-buttons-container').classList.add('d-none');
    }
  }

  _onLightDarkModeClick() {
    let color = this.settings.attributes.color;
    if (color.includes('light')) {
      color = color.replace('light', 'dark');
    } else {
      color = color.replace('dark', 'light');
    }
    this.updateAttribute('color', color);
  }

  _onPinButtonClick() {
    let behaviour = this.settings.attributes.behaviour;
    if (behaviour === 'pinned') {
      behaviour = 'unpinned';
    } else {
      behaviour = 'pinned';
    }
    this.updateAttribute('behaviour', behaviour);
  }

  updateAttribute(id, value) {
    if (id === 'color') {
      this.settings.carryParams = false;
    }
    if (this.settings.attributes[id] !== value) {
      this.settings.attributes[id] = value;
      localStorage.setItem(this.settings.storagePrefix + id, value);
      this._setActiveOptions();
      if (this.attributeOptions[id].update) {
        document.documentElement.setAttribute(this.attributeOptions[id].attribute, value);
      }
      if (this.attributeOptions[id].event) {
        document.documentElement.dispatchEvent(new CustomEvent(this.attributeOptions[id].event, {detail: value}));
      }
    }
  }

  getAttribute(attribute) {
    return this.settings.attributes[attribute];
  }
}
