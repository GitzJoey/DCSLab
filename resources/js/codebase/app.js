import './bootstrap';

import Template from './modules/template';

export default class App extends Template {
    constructor() {
        super();
    }
}
jQuery(() => {
   window.Codebase = new App();
});
