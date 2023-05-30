<script setup lang="ts">
import { useRoute } from "vue-router";
import MenuLink from "./MenuLink.vue";
import DarkModeSwitcher from "../../components/DarkModeSwitcher";
import MainColorSwitcher from "../../components/MainColorSwitcher";
import MobileMenu from "../../components/MobileMenu";
import TopBar from "../../components/TopBar";
import _ from "lodash";
import { useTopMenuStore } from "../../stores/top-menu";
import {
  ProvideForceActiveMenu,
  forceActiveMenu,
  Route,
  FormattedMenu,
  nestedMenu,
} from "./top-menu";
import { watch, reactive, computed, onMounted, provide } from "vue";

const route: Route = useRoute();
let formattedMenu = reactive<Array<FormattedMenu>>([]);
const setFormattedMenu = (computedFormattedMenu: Array<FormattedMenu>) => {
  Object.assign(formattedMenu, computedFormattedMenu);
};
const topMenuStore = useTopMenuStore();
const topMenu = computed(() => nestedMenu(topMenuStore.menu, route));

provide<ProvideForceActiveMenu>("forceActiveMenu", (pageName: string) => {
  forceActiveMenu(route, pageName);
  setFormattedMenu(topMenu.value);
});

watch(topMenu, () => {
  setFormattedMenu(topMenu.value);
});

watch(
  computed(() => route.path),
  () => {
    delete route.forceActiveMenu;
  }
);

onMounted(() => {
  setFormattedMenu(topMenu.value);
});
</script>

<template>
  <div class="py-5 md:py-0">
    <DarkModeSwitcher />
    <MainColorSwitcher />
    <MobileMenu />
    <TopBar layout="top-menu" />
    <!-- BEGIN: Top Menu -->
    <nav
      :class="[
        'relative z-50 hidden pt-32 -mt-4 md:block',

        // Animation
        'opacity-0 animate-[0.4s_ease-in-out_0.2s_intro-top-menu] animate-fill-mode-forwards',
      ]"
    >
      <ul class="flex flex-wrap px-6 xl:px-[50px]">
        <li
          v-for="(menu, menuKey) in formattedMenu"
          :class="[
            'relative [&:hover>ul]:block [&:hover>a>div:nth-child(2)>svg]:rotate-180',
            !menu.active &&
              '[&:hover>a]:bg-slate-100 [&:hover>a]:dark:bg-transparent',
            !menu.active &&
              '[&:hover>a]:before:content-[\'\'] [&:hover>a]:before:block [&:hover>a]:before:inset-0 [&:hover>a]:before:rounded-full [&:hover>a]:xl:before:rounded-xl [&:hover>a]:before:absolute [&:hover>a]:before:z-[-1] [&:hover>a]:before:border-b-[3px] [&:hover>a]:before:border-solid [&:hover>a]:before:border-black/[0.08] [&:hover>a]:before:dark:bg-darkmode-700',
          ]"
          :key="menuKey"
        >
          <MenuLink
            :class="{
              // Animation
              [`opacity-0 translate-y-[50px] animate-[0.4s_ease-in-out_0.3s_intro-menu] animate-fill-mode-forwards animate-delay-${
                (menuKey + 1) * 10
              }`]: !menu.active,
            }"
            :menu="menu"
            level="first"
          ></MenuLink>
          <!-- BEGIN: Second Child -->
          <ul
            v-if="menu.subMenu"
            :class="[
              'shadow-[0px_3px_20px_#00000014] dark:shadow-[0px_3px_7px_#0000001c] bg-slate-100 dark:bg-darkmode-600 hidden w-56 absolute rounded-md z-20 px-0 mt-1',
              'before:content-[\'\'] before:block before:absolute before:w-full before:h-full before:bg-white/[0.04] before:inset-0 before:rounded-md before:z-[-1] dark:before:bg-black/10',
              'after:content-[\'\'] after:w-full after:h-1 after:absolute after:top-0 after:left-0 after:-mt-1 after:cursor-pointer',
            ]"
          >
            <li
              v-for="(subMenu, subMenuKey) in menu.subMenu"
              class="px-5 relative [&:hover>ul]:block [&:hover>a>div:nth-child(2)>svg]:-rotate-90"
              :key="subMenuKey"
            >
              <MenuLink :menu="subMenu" level="second"></MenuLink>
              <!-- BEGIN: Third Child -->
              <ul
                v-if="subMenu.subMenu"
                :class="[
                  'shadow-[0px_3px_20px_#00000014] dark:shadow-[0px_3px_7px_#0000001c] left-[100%] bg-slate-100 dark:bg-darkmode-600 hidden w-56 absolute rounded-md mt-0 ml-0 top-0 z-20 px-0',
                  'before:content-[\'\'] before:block before:absolute before:w-full before:h-full before:bg-white/[0.04] before:inset-0 before:rounded-md before:z-[-1] dark:before:bg-black/10',
                  'after:content-[\'\'] after:w-full after:h-1 after:absolute after:top-0 after:left-0 after:-mt-1 after:cursor-pointer',
                ]"
              >
                <li
                  v-for="(lastSubMenu, lastSubMenuKey) in subMenu.subMenu"
                  class="px-5 relative [&:hover>ul]:block [&:hover>a>div:nth-child(2)>svg]:-rotate-90"
                  :key="lastSubMenuKey"
                >
                  <MenuLink :menu="lastSubMenu" level="third"></MenuLink>
                </li>
              </ul>
              <!-- END: Third Child -->
            </li>
          </ul>
          <!-- END: Second Child -->
        </li>
      </ul>
    </nav>
    <!-- END: Top Menu -->
    <!-- BEGIN: Content -->
    <div
      :class="[
        'max-w-full md:max-w-none rounded-[30px] md:rounded-[35px_35px_0_0] px-4 md:px-[22px] min-w-0 min-h-screen bg-slate-100 flex-1 pb-10 mt-5 relative dark:bg-darkmode-700',
        'before:content-[\'\'] before:w-full before:h-px before:block',
      ]"
    >
      <RouterView />
    </div>
    <!-- END: Content -->
  </div>
</template>
