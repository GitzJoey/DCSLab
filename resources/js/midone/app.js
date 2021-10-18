import { createApp } from 'vue'
import './libs'
import router from './route/route'
import i18n from './lang'
import store from './store'
import Main from './Main'
import VeeValidate from './validation'
import globalComponents from './global-components'
import utils from './utils'

const app = createApp(Main)
    .use(i18n)
    .use(router)
    .use(store)
    .use(VeeValidate)
;

globalComponents(app);
utils(app);

app.mount('#app');
