import { SET_DARK_MODE, USERCONTEXT } from './mutation-types';

const state = () => {
    return {
        darkMode: false,
        userContext: { }
    }
};

const getters = {
    darkMode: state => state.darkMode,
    userContext: state => state.userContext
};

const actions = {
    setDarkMode({ commit }, darkMode) {
        commit(SET_DARK_MODE, { darkMode });
    },
    setUserContext({ commit }, userPayload) {
        commit(types.USERCONTEXT, { userPayload });
    },
    fetchUserContext({ commit }) {
        axios.get('/api/get/dashboard/core/user/profile').then(response => {
            var userPayload = response.data;
            commit(USERCONTEXT, { userPayload });
        });
    }
};

const mutations = {
    [SET_DARK_MODE](state, { darkMode }) {
        state.darkMode = darkMode
    },
    [USERCONTEXT](state, { userPayload }) {
        state.userContext = userPayload;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
