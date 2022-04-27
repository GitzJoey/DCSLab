/**
 *
 * Search
 * Website generic search. Customized version of autoComplete plugin to work within the modal. Edit this class according to your project needs.
 *
 * @param {string} searchModalId Id of the search modal
 * @param {string} searchInputId Id of the input to init autocomplete
 * @param {string} searchResultsId Id to give created result element
 * @param {string} placeholder Placeholder text
 * @param {string} loading Placeholder text for loading json data
 * @param {string} jsonPath Path to json data
 *
 */

class Search {
  get options() {
    return {
      searchModalId: 'searchPagesModal',
      searchInputId: 'searchPagesInput',
      searchResultsId: 'searchPagesResults',
      placeholder: 'Search',
      loading: 'Loading',
      jsonPath: Helpers.UrlFix('/json/search.json'),
    };
  }

  constructor(options = {}) {
    this.settings = Object.assign(this.options, options);
    if (!document.getElementById(this.settings.searchInputId)) {
      return null;
    }
    this._addListeners();
    this._init();
    return this._autoComplete;
  }

  _init() {
    const searchInput = document.getElementById(this.settings.searchInputId);
    this._autoComplete = new autoComplete({
      data: {
        src: async () => {
          searchInput.setAttribute('placeholder', this.settings.loading);
          const source = await fetch(this.settings.jsonPath);
          const data = await source.json();
          searchInput.setAttribute('placeholder', this.settings.placeholder);
          return data;
        },
        key: ['label'],
        cache: true,
      },
      sort: (a, b) => {
        if (a.match < b.match) return -1;
        if (a.match > b.match) return 1;
        return 0;
      },
      placeHolder: this.settings.placeholder,
      selector: '#' + this.settings.searchInputId,
      threshold: 1,
      debounce: 0,
      searchEngine: 'loose',
      highlight: true,
      maxResults: 10,
      resultsList: {
        render: true,
        container: (source) => {
          source.setAttribute('id', this.settings.searchResultsId);
          source.setAttribute('class', 'auto-complete-result');
        },
        destination: document.getElementById(this.settings.searchInputId),
        position: 'afterend',
        element: 'ul',
      },
      resultItem: {
        content: (data, source) => {
          source.innerHTML = '<p class="mb-0">' + data.match + '</p>' + '<p class="text-small text-muted mb-0">' + Helpers.UrlFix(data.value.url) + '</p>';
          source.setAttribute('class', 'auto-complete-result-item');
        },
        element: 'li',
      },
      noResults: () => {
        const result = document.createElement('li');
        result.setAttribute('class', 'no_resulst');
        result.setAttribute('tabindex', '1');
        result.innerHTML = 'No Results';
        document.getElementById(this.settings.searchResultsId).appendChild(result);
      },
      onSelection: (feedback) => {
        window.location.href = Helpers.UrlFix(feedback.selection.value['url']);
        searchInput.value = '';
        searchInput.setAttribute('placeholder', 'Search');
      },
    });
  }

  _addListeners() {
    document.getElementById(this.settings.searchModalId).addEventListener('shown.bs.modal', this._onSearchModalShown.bind(this));
    document.getElementById(this.settings.searchModalId).addEventListener('hidden.bs.modal', this._onSearchModalHidden.bind(this));
    document.getElementById(this.settings.searchInputId).addEventListener('focus', this._onInputFocus.bind(this));
    document.getElementById(this.settings.searchInputId).addEventListener('focusout', this._onInputFocusOut.bind(this));
  }

  // Focusing to input when modal is shown
  _onSearchModalShown() {
    document.getElementById(this.settings.searchInputId).focus();
  }

  // Clearing search results and input when modal is hidden
  _onSearchModalHidden() {
    document.getElementById(this.settings.searchInputId).value = '';
    const reuslts = document.getElementById(this.settings.searchResultsId);
    while (reuslts.firstChild) {
      reuslts.removeChild(reuslts.firstChild);
    }
  }

  _onInputFocus() {
    document.getElementById(this.settings.searchInputId).addEventListener('keydown', this._onKeyDown.bind(this));
  }

  _onInputFocusOut() {
    document.getElementById(this.settings.searchInputId).removeEventListener('keydown', this._onKeyDown.bind(this));
  }

  // Preventing up and down key events to go start and end of the input
  _onKeyDown(event) {
    if (event.which === 38 || event.which === 40) {
      event.preventDefault();
    }
  }
}
