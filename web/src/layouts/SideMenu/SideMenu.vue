<script setup lang="ts">
import { useRoute } from "vue-router";
import Divider from "./Divider.vue";
import Menu from "./Menu.vue";
import SimpleMenu from "../SimpleMenu/Menu.vue";
import TopBar from "../../components/TopBar";
import DarkModeSwitcher from "../../components/DarkModeSwitcher";
import MainColorSwitcher from "../../components/MainColorSwitcher";
import MobileMenu from "../../components/MobileMenu";
import { useSideMenuStore } from "../../stores/side-menu";
import {
  ProvideForceActiveMenu,
  forceActiveMenu,
  Route,
  FormattedMenu,
  nestedMenu,
  enter,
  leave,
} from "./side-menu";
import { watch, reactive, computed, onMounted, provide } from "vue";
import { useDashboardStore } from "../../stores/dashboard";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import ScrollToTop from "../../base-components/ScrollToTop";
import NotificationManager from "../../base-components/NotificationManager";

const route: Route = useRoute();
let formattedMenu = reactive<Array<FormattedMenu | "divider">>([]);
const setFormattedMenu = (
  computedFormattedMenu: Array<FormattedMenu | "divider">
) => {
  Object.assign(formattedMenu, computedFormattedMenu);
};
const sideMenuStore = useSideMenuStore();
const sideMenu = computed(() => nestedMenu(sideMenuStore.menu, route));

const dashboardStore = useDashboardStore();
const screenMask = computed(() => dashboardStore.screenMaskValue);

provide<ProvideForceActiveMenu>("forceActiveMenu", (pageName: string) => {
  forceActiveMenu(route, pageName);
  setFormattedMenu(sideMenu.value);
});

watch(sideMenu, () => {
  setFormattedMenu(sideMenu.value);
});

watch(
  computed(() => route.path),
  () => {
    delete route.forceActiveMenu;
  }
);

onMounted(() => {
  setFormattedMenu(sideMenu.value);
});
</script>

<template>
  <div>
    <LoadingOverlay :visible="screenMask" :transparent="false">
      <div class="py-5 md:py-0">
        <DarkModeSwitcher />
        <MainColorSwitcher />
        <MobileMenu />
        <TopBar :layout="dashboardStore.getLayoutValue" />
        <div class="flex overflow-hidden">
          <nav v-if="dashboardStore.getLayoutValue == 'simple-menu'"
            class="w-[105px] px-5 pb-16 overflow-x-hidden z-50 pt-32 -mt-4 hidden md:block">
            <ul>
              <template v-for="(menu, menuKey) in formattedMenu">
                <Divider v-if="menu == 'divider'" :key="'divider-' + menuKey" type="li" :class="[
                  'my-6',
                  `opacity-0 animate-[0.4s_ease-in-out_0.1s_intro-divider] animate-fill-mode-forwards animate-delay-${(menuKey + 1) * 10
                  }`,
                ]"></Divider>
                <li v-else :key="menuKey">
                  <SimpleMenu :class="{
                    [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${(menuKey + 1) * 10
                      }`]: !menu.active,
                  }" :menu="menu" :formattedMenuState="[formattedMenu, setFormattedMenu]" level="first"></SimpleMenu>
                  <Transition @enter="() => enter" @leave="() => leave">
                    <ul v-if="menu.subMenu && menu.activeDropdown" :class="[
                      'bg-white/[0.04] rounded-xl relative dark:bg-transparent',
                      'before:content-[\'\'] before:block before:inset-0 before:bg-white/30 before:rounded-xl before:absolute before:z-[-1] before:dark:bg-darkmode-900/30',
                      { block: menu.activeDropdown },
                      { hidden: !menu.activeDropdown },
                    ]">
                      <li v-for="(subMenu, subMenuKey) in menu.subMenu" :key="subMenuKey">
                        <SimpleMenu :class="{
                          [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${(subMenuKey + 1) * 10
                            }`]: !subMenu.active,
                        }" :menu="subMenu" :formattedMenuState="[formattedMenu, setFormattedMenu]" level="second">
                        </SimpleMenu>
                        <Transition v-if="subMenu.subMenu" @enter="() => enter" @leave="() => leave">
                          <ul v-if="subMenu.subMenu && subMenu.activeDropdown" :class="[
                            'bg-white/[0.04] rounded-xl relative dark:bg-transparent',
                            'before:content-[\'\'] before:block before:inset-0 before:bg-white/30 before:rounded-xl before:absolute before:z-[-1] before:dark:bg-darkmode-900/30',
                            { block: subMenu.activeDropdown },
                            { hidden: !subMenu.activeDropdown },
                          ]">
                            <li v-for="(
                            lastSubMenu, lastSubMenuKey
                          ) in subMenu.subMenu" :key="lastSubMenuKey">
                              <SimpleMenu :class="{
                                [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${(lastSubMenuKey + 1) * 10
                                  }`]: !lastSubMenu.active,
                              }" :menu="lastSubMenu" :formattedMenuState="[formattedMenu, setFormattedMenu]"
                                level="third">
                              </SimpleMenu>
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

          <nav v-else class="w-[105px] xl:w-[260px] px-5 pb-16 overflow-x-hidden z-50 pt-32 -mt-4 hidden md:block">
            <ul>
              <template v-for="(menu, menuKey) in formattedMenu">
                <Divider v-if="menu == 'divider'" :key="'divider-' + menuKey" type="li" :class="[
                  'my-6',
                  `opacity-0 animate-[0.4s_ease-in-out_0.1s_intro-divider] animate-fill-mode-forwards animate-delay-${(menuKey + 1) * 10
                  }`,
                ]"></Divider>
                <li v-else :key="menuKey">
                  <Menu :class="{
                    [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${(menuKey + 1) * 10
                      }`]: !menu.active,
                  }" :menu="menu" :formattedMenuState="[formattedMenu, setFormattedMenu]" level="first"></Menu>
                  <Transition @enter="() => enter" @leave="() => leave">
                    <ul v-if="menu.subMenu && menu.activeDropdown" :class="[
                      'bg-white/[0.04] rounded-xl relative dark:bg-transparent',
                      'before:content-[\'\'] before:block before:inset-0 before:bg-white/30 before:rounded-xl before:absolute before:z-[-1] before:dark:bg-darkmode-900/30',
                      { block: menu.activeDropdown },
                      { hidden: !menu.activeDropdown },
                    ]">
                      <li v-for="(subMenu, subMenuKey) in menu.subMenu" :key="subMenuKey">
                        <Menu :class="{
                          [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${(subMenuKey + 1) * 10
                            }`]: !subMenu.active,
                        }" :menu="subMenu" :formattedMenuState="[formattedMenu, setFormattedMenu]" level="second">
                        </Menu>
                        <Transition v-if="subMenu.subMenu" @enter="() => enter" @leave="() => leave">
                          <ul v-if="subMenu.subMenu && subMenu.activeDropdown" :class="[
                            'bg-white/[0.04] rounded-xl relative dark:bg-transparent',
                            'before:content-[\'\'] before:block before:inset-0 before:bg-white/30 before:rounded-xl before:absolute before:z-[-1] before:dark:bg-darkmode-900/30',
                            { block: subMenu.activeDropdown },
                            { hidden: !subMenu.activeDropdown },
                          ]">
                            <li v-for="(
                            lastSubMenu, lastSubMenuKey
                          ) in subMenu.subMenu" :key="lastSubMenuKey">
                              <Menu :class="{
                                [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${(lastSubMenuKey + 1) * 10
                                  }`]: !lastSubMenu.active,
                              }" :menu="lastSubMenu" :formattedMenuState="[formattedMenu, setFormattedMenu]"
                                level="third">
                              </Menu>
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

          <div :class="[
            'max-w-full md:max-w-none rounded-[30px] md:rounded-none px-4 md:px-[22px] min-w-0 min-h-screen bg-slate-100 flex-1 md:pt-20 pb-10 mt-5 md:mt-1 relative dark:bg-darkmode-700',
            'before:content-[\'\'] before:w-full before:h-px before:block',
          ]">
            <RouterView />
            <br v-for="i in 3" :key="i" />
            <ScrollToTop />
          </div>
        </div>
        <NotificationManager />
      </div>
    </LoadingOverlay>
  </div>
</template>
