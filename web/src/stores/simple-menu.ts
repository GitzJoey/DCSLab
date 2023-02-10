import { defineStore } from "pinia";
import { Icon } from "../base-components/Lucide/Lucide.vue";

export interface Menu {
  icon: Icon;
  title: string;
  pageName?: string;
  subMenu?: Menu[];
  ignore?: boolean;
}

export interface SimpleMenuState {
  menu: Array<Menu | "divider">;
}

export const useSimpleMenuStore = defineStore("simpleMenu", {
  state: (): SimpleMenuState => ({
    menu: [
      {
        icon: "Activity",
        pageName: "simple-menu-page-1",
        title: "Page 1",
      },
      {
        icon: "Activity",
        pageName: "simple-menu-page-2",
        title: "Page 2",
      },
    ],
  }),
});
