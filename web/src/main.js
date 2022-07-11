import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import { createPinia } from "pinia";
import globalComponents from "./global-components";
import utils from "./utils";
import i18n from "./lang";
import VeeValidate from "./validation";

import "./assets/css/app.css";

const app = createApp(App)
            .use(router)
            .use(createPinia())
            .use(i18n)
            .use(VeeValidate)
;

globalComponents(app);
utils(app);

app.mount("#app");