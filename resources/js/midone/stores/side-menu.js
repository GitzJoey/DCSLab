import { defineStore } from "pinia";

export const useSideMenuStore = defineStore("sideMenu", {
  state: () => ({
    menu: [
      {
        icon: "HomeIcon",
        pageName: "side-menu-dashboard",
        title: "Dashboard",
        subMenu: [
          {
            icon: "",
            pageName: "side-menu-dashboard-maindashboard",
            title: "Main Dashboard",
          }
        ],
      },
    ],
  }),
});
