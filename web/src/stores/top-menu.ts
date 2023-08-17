import { defineStore } from "pinia";
import { Icon } from "../base-components/Lucide/Lucide.vue";

export interface Menu {
  icon: Icon;
  title: string;
  pageName?: string;
  subMenu?: Menu[];
  ignore?: boolean;
}

export interface TopMenuState {
  menu: Array<Menu>;
}

export const useTopMenuStore = defineStore("topMenu", {
  state: (): TopMenuState => ({
    menu: [
      {
        icon: "Activity",
        pageName: "top-menu-page-1",
        title: "Page 1",
      },
      {
        icon: "Activity",
        pageName: "top-menu-page-2",
        title: "Page 2",
      },
    ],
  }),
});
