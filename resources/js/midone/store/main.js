import { SET_DARK_MODE, USERCONTEXT, SELECTEDUSERCOMPANY } from './mutation-types';

const state = () => {
    return {
        darkMode: false,
        userContext: { },
        selectedUserCompany: '',
    }
};

const getters = {
    darkMode: state => state.darkMode,
    userContext: state => state.userContext,
    selectedUserCompany: state => state.selectedUserCompany,
};

const actions = {
    setDarkMode({ commit }, darkMode) {
        commit(SET_DARK_MODE, { darkMode });
    },
    setSelectedCompany({ commit }, payload) {
        commit(SELECTEDUSERCOMPANY, { payload });
    },
    fetchUserContext({ commit }) {
        axios.get('/api/get/dashboard/core/profile/read').then(response => {
            var userPayload = response.data;
            commit(USERCONTEXT, { userPayload });
        });
    },
};

const mutations = {
    [SET_DARK_MODE](state, { darkMode }) {
        state.darkMode = darkMode
    },
    [USERCONTEXT](state, { userPayload }) {
        state.userContext = userPayload;
    },
    [SELECTEDUSERCOMPANY](state, { payload }) {
        state.selectedUserCompany = payload;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
