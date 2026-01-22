import { type Icon } from "@/components/Base/Lucide/Lucide.vue";
import { type Themes } from "@/stores/theme";
import { defineStore } from "pinia";

export interface Menu {
  icon: Icon;
  title: string;
  pageName?: string;
  subMenu?: Menu[];
  ignore?: boolean;
}

export interface MenuState {
  menuValue: Array<Menu | "divider">;
}

export const useMenuStore = defineStore("menu", {
  state: (): MenuState => ({
    menuValue: [
      {
        icon: "Home",
        pageName: "side-menu-dashboard-maindashboard",
        title: "Dashboard",
      },
    ],
  }),
  getters: {
    menu: (state) => (layout: Themes["layout"]) => {
      if (layout == "top-menu") {
        return state.menuValue;
      }

      if (layout == "simple-menu") {
        return state.menuValue;
      }

      return state.menuValue;
    },
  },
  actions: {
    setMenu(menu: Array<Menu>) {
      this.menuValue = menu;
    }
  }
});
