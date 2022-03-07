import { defineStore } from "pinia";

export const useColorSchemeStore = defineStore("colorScheme", {
  state: () => ({
    colorSchemeValue:
      localStorage.getItem("colorScheme") === null
        ? "theme-1"
        : localStorage.getItem("colorScheme"),
  }),
  getters: {
    colorScheme(state) {
      if (localStorage.getItem("colorScheme") === null) {
        localStorage.setItem("colorScheme", "theme-1");
      }

      return state.colorSchemeValue;
    },
  },
  actions: {
    setColorScheme(colorScheme) {
      localStorage.setItem("colorScheme", colorScheme);
      this.colorSchemeValue = colorScheme;
    },
  },
});
