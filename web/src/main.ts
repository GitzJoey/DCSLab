import { createApp } from 'vue'
import { createPinia } from "pinia";
import i18n from "./lang";
import App from "./App.vue";
import router from "./router";
import "./assets/css/app.css";

createApp(App).use(router).use(createPinia()).use(i18n).mount('#app')