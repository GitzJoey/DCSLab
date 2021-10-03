import { createStore } from 'vuex';
import main from './main';
import sideMenu from './side-menu';

const store = createStore({
    modules: {
        main,
        sideMenu,
    }
});

export function useStore() {
    return store
}

export default store;
