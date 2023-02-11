import { createApp } from 'vue'
import { createPinia } from "pinia";
import i18n from "./lang";
import App from "./App.vue";
import router from "./router";
import VeeValidate from "./validation";

import "./assets/css/app.css";

createApp(App)
    .use(router)
    .use(i18n)
    .use(createPinia())
    .use(VeeValidate)
    .mount('#app');