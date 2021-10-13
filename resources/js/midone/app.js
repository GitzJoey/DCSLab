import { createApp } from 'vue'
import router from './route/route'
import i18n from './lang/index'
import store from './store'
import Main from './Main'
import globalComponents from './global-components'
import utils from './utils'
import './libs'

const app = createApp(Main)
    .use(i18n)
    .use(router)
    .use(store)
;

globalComponents(app);
utils(app);

app.mount('#app');
