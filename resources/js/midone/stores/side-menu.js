import { defineStore } from "pinia";
import axios from "@/axios";

export const useSideMenuStore = defineStore("sideMenu", {
  state: () => ({
    menu: [],
  }),
  actions: {
    fetchMenu() {
      axios.get('/api/get/dashboard/core/user/menu').then(response => {
        this.menu = response.data;
      });
    },
    async fetchMenuAsync() {
      let response = await axios.get('/api/get/dashboard/core/user/menu');
      this.menu = response.data;
    }
  }
});
