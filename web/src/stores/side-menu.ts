import { defineStore } from "pinia";
import { Icon } from "../base-components/Lucide/Lucide.vue";

export interface Menu {
  icon: Icon | "";
  title: string;
  pageName?: string;
  subMenu?: Menu[];
  ignore?: boolean;
}

export interface SideMenuState {
  menu: Array<Menu | "divider">;
}

export const useSideMenuStore = defineStore("sideMenu", {
  state: (): SideMenuState => ({
    menu: [
      {
        icon: 'HomeIcon',
        pageName: 'side-menu-dashboard',
        title: 'Dashboard',
        subMenu: [
          {
            icon: "",
            pageName: "side-menu-dashboard-maindashboard",
            title: "Main Dashboard",
          }          
        ]
      }
    ],
  }),
  getters: {
    getUserMenu: state => state.menu,
  },
  actions: {
    setUserMenu(userMenu: SideMenuState) {
      this.menu = userMenu.menu;
    }
  } 
});
