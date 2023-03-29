<script setup lang="ts">
import { ref } from "vue";
import { useRoute } from "vue-router";
import Divider from "./Divider.vue";
import Menu from "./Menu.vue";
import SimpleMenu from "../SimpleMenu/Menu.vue";
import TopBar from "../../components/TopBar";
import DarkModeSwitcher from "../../components/DarkModeSwitcher";
import MainColorSwitcher from "../../components/MainColorSwitcher";
import MobileMenu from "../../components/MobileMenu";
import { useSideMenuStore, Menu as sMenu } from "../../stores/side-menu";
import { FormattedMenu, nestedMenu, enter, leave } from "./side-menu";
import { watch, reactive, computed, onMounted } from "vue";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import ScrollToTop from "../../base-components/ScrollToTop";
import { useDashboardStore } from "../../stores/dashboard";
import DashboardService from "../../services/DashboardService";
import { useZiggyRouteStore } from "../../stores/ziggy-route";
import { Config } from "ziggy-js";

const route = useRoute();

const dashboardService = new DashboardService();

let formattedMenu = reactive<Array<FormattedMenu | "divider">>([]);
const setFormattedMenu = (
  computedFormattedMenu: Array<FormattedMenu | "divider">
) => {
  Object.assign(formattedMenu, computedFormattedMenu);
};
const sideMenuStore = useSideMenuStore();
const sideMenu = computed(() => nestedMenu(sideMenuStore.menu, route));

const dashboardStore = useDashboardStore();

const ziggyRouteStore = useZiggyRouteStore();

const screenMask = computed(() => dashboardStore.screenMaskValue );

watch(sideMenu, () => {
  setFormattedMenu(sideMenu.value);
});

onMounted(async () => {
  dashboardStore.toggleScreenMaskValue();

  let menu = await dashboardService.readUserMenu();
  //sideMenuStore.setUserMenu(menu as Array<sMenu>);

  let api = await dashboardService.readUserApi();
  //ziggyRouteStore.setZiggy(api as Config)

  setFormattedMenu(sideMenu.value);
  
  dashboardStore.toggleScreenMaskValue();
});
</script>

<template>
  <div class="py-5 md:py-0">
    <DarkModeSwitcher />
    <MainColorSwitcher />
    <MobileMenu />
    <LoadingOverlay :visible="true" :transparent="false">
      <TopBar :layout="dashboardStore.getLayoutValue" />
      <div class="flex overflow-hidden">
        <nav
          class="w-[105px] px-5 pb-16 overflow-x-hidden z-50 pt-32 -mt-4 hidden md:block"
          v-if="dashboardStore.getLayoutValue == 'simple-menu'"
        >
          <ul>
            <template v-for="(menu, menuKey) in formattedMenu">
              <Divider
                v-if="menu == 'divider'"
                type="li"
                :class="[
                  'my-6',

                  `opacity-0 animate-[0.4s_ease-in-out_0.1s_intro-divider] animate-fill-mode-forwards animate-delay-${
                    (menuKey + 1) * 10
                  }`,
                ]"
                :key="'divider-' + menuKey"
              ></Divider>
              <li v-else :key="menuKey">
                <SimpleMenu
                  :class="{
                    [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${
                      (menuKey + 1) * 10
                    }`]: !menu.active,
                  }"
                  :menu="menu"
                  :formattedMenuState="[formattedMenu, setFormattedMenu]"
                  level="first"
                ></SimpleMenu>
                <Transition @enter="enter" @leave="leave">
                  <ul
                    v-if="menu.subMenu && menu.activeDropdown"
                    :class="[
                      'bg-white/[0.04] rounded-xl relative dark:bg-transparent',
                      'before:content-[\'\'] before:block before:inset-0 before:bg-white/30 before:rounded-xl before:absolute before:z-[-1] before:dark:bg-darkmode-900/30',
                      { block: menu.activeDropdown },
                      { hidden: !menu.activeDropdown },
                    ]"
                  >
                    <li
                      v-for="(subMenu, subMenuKey) in menu.subMenu"
                      :key="subMenuKey"
                    >
                      <SimpleMenu
                        :class="{
                          [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${
                            (subMenuKey + 1) * 10
                          }`]: !subMenu.active,
                        }"
                        :menu="subMenu"
                        :formattedMenuState="[formattedMenu, setFormattedMenu]"
                        level="second"
                      ></SimpleMenu>
                      <Transition
                        @enter="enter"
                        @leave="leave"
                        v-if="subMenu.subMenu"
                      >
                        <ul
                          v-if="subMenu.subMenu && subMenu.activeDropdown"
                          :class="[
                            'bg-white/[0.04] rounded-xl relative dark:bg-transparent',
                            'before:content-[\'\'] before:block before:inset-0 before:bg-white/30 before:rounded-xl before:absolute before:z-[-1] before:dark:bg-darkmode-900/30',
                            { block: subMenu.activeDropdown },
                            { hidden: !subMenu.activeDropdown },
                          ]"
                        >
                          <li
                            v-for="(
                              lastSubMenu, lastSubMenuKey
                            ) in subMenu.subMenu"
                            :key="lastSubMenuKey"
                          >
                            <SimpleMenu
                              :class="{
                                [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${
                                  (lastSubMenuKey + 1) * 10
                                }`]: !lastSubMenu.active,
                              }"
                              :menu="lastSubMenu"
                              :formattedMenuState="[
                                formattedMenu,
                                setFormattedMenu,
                              ]"
                              level="third"
                            ></SimpleMenu>
                          </li>
                        </ul>
                      </Transition>
                    </li>
                  </ul>
                </Transition>
              </li>
            </template>
          </ul>
        </nav>

        <nav
          class="w-[105px] xl:w-[260px] px-5 pb-16 overflow-x-hidden z-50 pt-32 -mt-4 hidden md:block"
          v-else
        >
          <ul>
            <template v-for="(menu, menuKey) in formattedMenu">
              <Divider
                v-if="menu == 'divider'"
                type="li"
                :class="[
                  'my-6',

                  `opacity-0 animate-[0.4s_ease-in-out_0.1s_intro-divider] animate-fill-mode-forwards animate-delay-${
                    (menuKey + 1) * 10
                  }`,
                ]"
                :key="'divider-' + menuKey"
              ></Divider>
              <li v-else :key="menuKey">
                <Menu
                  :class="{
                    [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${
                      (menuKey + 1) * 10
                    }`]: !menu.active,
                  }"
                  :menu="menu"
                  :formattedMenuState="[formattedMenu, setFormattedMenu]"
                  level="first"
                ></Menu>
                <Transition @enter="enter" @leave="leave">
                  <ul
                    v-if="menu.subMenu && menu.activeDropdown"
                    :class="[
                      'bg-white/[0.04] rounded-xl relative dark:bg-transparent',
                      'before:content-[\'\'] before:block before:inset-0 before:bg-white/30 before:rounded-xl before:absolute before:z-[-1] before:dark:bg-darkmode-900/30',
                      { block: menu.activeDropdown },
                      { hidden: !menu.activeDropdown },
                    ]"
                  >
                    <li
                      v-for="(subMenu, subMenuKey) in menu.subMenu"
                      :key="subMenuKey"
                    >
                      <Menu
                        :class="{
                          [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${
                            (subMenuKey + 1) * 10
                          }`]: !subMenu.active,
                        }"
                        :menu="subMenu"
                        :formattedMenuState="[formattedMenu, setFormattedMenu]"
                        level="second"
                      ></Menu>
                      <Transition
                        @enter="enter"
                        @leave="leave"
                        v-if="subMenu.subMenu"
                      >
                        <ul
                          v-if="subMenu.subMenu && subMenu.activeDropdown"
                          :class="[
                            'bg-white/[0.04] rounded-xl relative dark:bg-transparent',
                            'before:content-[\'\'] before:block before:inset-0 before:bg-white/30 before:rounded-xl before:absolute before:z-[-1] before:dark:bg-darkmode-900/30',
                            { block: subMenu.activeDropdown },
                            { hidden: !subMenu.activeDropdown },
                          ]"
                        >
                          <li
                            v-for="(
                              lastSubMenu, lastSubMenuKey
                            ) in subMenu.subMenu"
                            :key="lastSubMenuKey"
                          >
                            <Menu
                              :class="{
                                [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${
                                  (lastSubMenuKey + 1) * 10
                                }`]: !lastSubMenu.active,
                              }"
                              :menu="lastSubMenu"
                              :formattedMenuState="[
                                formattedMenu,
                                setFormattedMenu,
                              ]"
                              level="third"
                            ></Menu>
                          </li>
                        </ul>
                      </Transition>
                    </li>
                  </ul>
                </Transition>
              </li>
            </template>
          </ul>
        </nav>
        <div
          :class="[
            'max-w-full md:max-w-none rounded-[30px] md:rounded-none px-4 md:px-[22px] min-w-0 min-h-screen bg-slate-100 flex-1 md:pt-20 pb-10 mt-5 md:mt-1 relative dark:bg-darkmode-700',
            'before:content-[\'\'] before:w-full before:h-px before:block',
          ]"
        >
          <RouterView />
          <ScrollToTop />
        </div>
      </div>
    </LoadingOverlay>
  </div>
</template>
