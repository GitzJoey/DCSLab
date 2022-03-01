import { defineStore } from "pinia";

export const useUserContextStore = defineStore("userContext", {
  state: () => ({
    userContextValue: null,
  }),
  getters: {
    userContext(state) {
      return state.userContextValue;
    },
  },
  actions: {
    setUserContext() {
    },
  },
});
