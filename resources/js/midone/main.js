import { createApp } from 'vue';
import App from 'App';
import router from "./router";
import store from "./store";
import utils from "./utils";

const app = createApp(App)
    .use(store)
    .use(router);
;

utils(app);

app.mount("#app");

