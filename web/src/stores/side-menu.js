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
    refreshMenu() {
      axios.get('/api/get/dashboard/core/user/menu?refresh=true').then(response => {
        this.menu = response.data;
      });
    }
  }
});
