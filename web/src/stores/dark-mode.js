import { defineStore } from "pinia";

export const useDarkModeStore = defineStore("darkMode", {
  state: () => ({
    darkModeValue: localStorage.getItem("darkMode") === "true",
  }),
  getters: {
    darkMode(state) {
      if (localStorage.getItem("darkMode") === null) {
        localStorage.setItem("darkMode", false);
      }

      return state.darkModeValue;
    },
  },
  actions: {
    setDarkMode(darkMode) {
      localStorage.setItem("darkMode", darkMode);
      this.darkModeValue = darkMode;
    },
  },
});
