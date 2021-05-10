import * as types from "./mutation-types";

const state = () => {
  return {
    darkMode: false
  };
};

// getters
const getters = {
  darkMode: state => state.darkMode
};

// actions
const actions = {
  setDarkMode({ commit }, darkMode) {
    commit(types.SET_DARK_MODE, { darkMode });
  }
};

// mutations
const mutations = {
  [types.SET_DARK_MODE](state, { darkMode }) {
    state.darkMode = darkMode;
  }
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
};
