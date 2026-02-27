import { defineStore } from "pinia";

interface DarkModeState {
  darkModeValue: boolean;
}

export const useDarkModeStore = defineStore("darkMode", {
  state: (): DarkModeState => ({
    darkModeValue: localStorage.getItem("darkMode") === "true",
  }),
  getters: {
    darkMode(state) {
      if (localStorage.getItem("darkMode") === null) {
        localStorage.setItem("darkMode", "false");
      }

      return state.darkModeValue;
    },
  },
  actions: {
    setDarkMode(darkMode: boolean) {
      localStorage.setItem("darkMode", darkMode.toString());
      this.darkModeValue = darkMode;
    },
  },
});
