<script setup lang="ts">
import "@/assets/css/themes/tinker/top-nav.css";
import { useRoute, useRouter } from "vue-router";
import logoUrl from "@/assets/images/logo.svg";
import MobileMenu from "@/components/MobileMenu";
import _ from "lodash";
import { useMenuStore, Menu as sMenu } from "@/stores/menu";
import {
  type ProvideForceActiveMenu,
  forceActiveMenu,
  type Route,
  type FormattedMenu,
  nestedMenu,
  linkTo,
} from "./top-menu";
import Lucide from "@/components/Base/Lucide";
import { watch, reactive, computed, onMounted, ref, provide } from "vue";
import ScrollToTop from "@/components/Base/ScrollToTop";
import LoadingOverlay from "@/components/LoadingOverlay";
import { EmailVerificationAlert } from "@/components/AlertPlaceholder";
import { useDashboardStore } from "@/stores/dashboard";
import DashboardService from "@/services/DashboardService";
import { useZiggyRouteStore } from "@/stores/ziggy-route";
import { Config } from "ziggy-js";
import { useI18n } from "vue-i18n";
import HomeLogo from "@/components/HomeLogo/HomeLogo.vue";
import UserLocation from "@/components/UserLocation/UserLocation.vue";
import SearchBox from "@/components/SearchBox/SearchBox.vue";
import ProfileMenu from "@/components/ProfileMenu/ProfileMenu.vue";
import LanguageSwitcher from "@/components/LanguageSwitcher/LanguageSwitcher.vue";
import SidebarPop from "@/components/SidebarPop/SidebarPop.vue";

const { t } = useI18n();
const dashboardServices = new DashboardService();

const route: Route = useRoute();
const router = useRouter();
let formattedMenu = reactive<Array<FormattedMenu | "divider">>([]);
const setFormattedMenu = (
  computedFormattedMenu: Array<FormattedMenu | "divider">
) => {
  Object.assign(formattedMenu, computedFormattedMenu);
};
const menuStore = useMenuStore();
const menu = computed(() => nestedMenu(menuStore.menu("top-menu"), route));

const dashboardStore = useDashboardStore();
const screenMask = computed(() => dashboardStore.screenMaskValue);

const ziggyRouteStore = useZiggyRouteStore();

const showBackToTop = ref<boolean>(false);

const handlescroll = () => {
  if (window.scrollY > 100) {
    showBackToTop.value = true;
  } else {
    showBackToTop.value = false;
  }
}

window.addEventListener('scroll', handlescroll);

provide<ProvideForceActiveMenu>("forceActiveMenu", (pageName: string) => {
  forceActiveMenu(route, pageName);
  setFormattedMenu(menu.value);
});

watch(menu, () => {
  setFormattedMenu(menu.value);
});

watch(
  computed(() => route.path),
  () => {
    delete route.forceActiveMenu;
  }
);

onMounted(async () => {
  await updateMenu();

  setFormattedMenu(menu.value);
});

const updateMenu = async () => {
  let menuResult = await dashboardServices.readUserMenu();
  menuStore.setMenu(menuResult.data as Array<sMenu>);

  let apiResult = await dashboardServices.readUserApi();
  ziggyRouteStore.setZiggy(apiResult.data as Config);
};
</script>

<template>
  <div>
    <LoadingOverlay :visible="screenMask" :transparent="false">
      <div
        :class="[
          'tinker md:bg-black/[0.15] dark:bg-transparent relative py-5 px-5 md:py-0 sm:px-8 md:px-0',
          'after:content-[\'\'] after:bg-gradient-to-b after:from-theme-1 after:to-theme-2 dark:after:from-darkmode-800 dark:after:to-darkmode-800 after:fixed after:inset-0 after:z-[-2]',
        ]"
      >
        <MobileMenu />

        <div
          class="h-[70px] z-[51] relative border-b border-white/[0.08] mt-12 md:mt-0 -mx-3 sm:-mx-8 md:mx-0 px-4 sm:px-8 md:px-6 mb-10 md:mb-8"
        >
          <div class="flex items-center h-full">
            <HomeLogo theme="tinker" layout="top-menu" />
            <UserLocation theme="tinker" layout="top-menu" />
            <SearchBox />
            <SidebarPop theme="tinker" layout="top-menu" />
            <LanguageSwitcher theme="tinker" layout="top-menu" />
            <ProfileMenu theme="tinker" layout="top-menu" />
          </div>
        </div>

        <nav
          class="top-nav relative z-50 -mt-[3px] hidden translate-y-[50px] opacity-0 md:block"
        >
          <ul class="flex flex-wrap h-[58px] px-6 xl:px-[50px]">
            <li v-for="(menu, menuKey) in formattedMenu" :key="menuKey">
              <template v-if="menu != 'divider'">
                <a
                  :href="
                  menu.subMenu
                    ? '#'
                    : ((pageName: string | undefined) => {
                        try {
                          return router.resolve({
                            name: pageName,
                          }).fullPath;
                        } catch (err) {
                          return '';
                        }
                      })(menu.pageName)
                "
                  :class="[menu.active ? 'top-menu top-menu--active' : 'top-menu']"
                  @click="(event: MouseEvent) => {
                  event.preventDefault();
                  linkTo(menu, router);
                }"
                >
                  <div class="top-menu__icon">
                    <Lucide :icon="menu.icon" />
                  </div>
                  <div class="top-menu__title">
                    {{ t(menu.title) }}
                    <Lucide
                      v-if="menu.subMenu"
                      class="top-menu__sub-icon"
                      icon="ChevronDown"
                    />
                  </div>
                </a>
                <ul
                  v-if="menu.subMenu"
                  :class="{ 'side-menu__sub-open': menu.activeDropdown }"
                >
                  <li
                    v-for="(subMenu, subMenuKey) in menu.subMenu"
                    :key="subMenuKey"
                  >
                    <a
                      :href="
                      subMenu.subMenu
                        ? '#'
                        : ((pageName: string | undefined) => {
                            try {
                              return router.resolve({
                                name: pageName,
                              }).fullPath;
                            } catch (err) {
                              return '';
                            }
                          })(subMenu.pageName)
                    "
                      class="top-menu"
                      @click="(event: MouseEvent) => {
                      event.preventDefault();
                      linkTo(subMenu, router);
                    }"
                    >
                      <div class="top-menu__icon">
                        <Lucide :icon="subMenu.icon" />
                      </div>
                      <div class="top-menu__title">
                        {{ t(subMenu.title) }}
                        <Lucide
                          v-if="subMenu.subMenu"
                          class="top-menu__sub-icon"
                          icon="ChevronDown"
                        />
                      </div>
                    </a>
                    <ul
                      v-if="subMenu.subMenu"
                      :class="{ 'side-menu__sub-open': subMenu.activeDropdown }"
                    >
                      <li
                        v-for="(lastSubMenu, lastSubMenuKey) in subMenu.subMenu"
                        :key="lastSubMenuKey"
                      >
                        <a
                          :href="
                          lastSubMenu.subMenu
                            ? '#'
                            : ((pageName: string | undefined) => {
                                try {
                                  return router.resolve({
                                    name: pageName,
                                  }).fullPath;
                                } catch (err) {
                                  return '';
                                }
                              })(lastSubMenu.pageName)
                        "
                          class="top-menu"
                          @click="(event: MouseEvent) => {
                          event.preventDefault();
                          linkTo(lastSubMenu, router);
                        }"
                        >
                          <div class="top-menu__icon">
                            <Lucide :icon="lastSubMenu.icon" />
                          </div>
                          <div class="top-menu__title">
                            {{ t(lastSubMenu.title) }}
                          </div>
                        </a>
                      </li>
                    </ul>
                  </li>
                </ul>
              </template>
            </li>
          </ul>
        </nav>
        <div
          :class="[
            'rounded-[30px] md:rounded-[35px_35px_0px_0px] min-w-0 min-h-screen max-w-full md:max-w-none bg-slate-100 flex-1 pb-10 px-4 md:px-6 relative mt-8 dark:bg-darkmode-700',
            'before:content-[\'\'] before:w-full before:h-px before:block',
            'after:content-[\'\'] after:z-[-1] after:rounded-[40px_40px_0px_0px] after:w-[97%] after:inset-y-0 after:absolute after:left-0 after:right-0 after:bg-white/10 after:-mt-4 after:mx-auto after:dark:bg-darkmode-400/50',
          ]"
        >
          <EmailVerificationAlert />
          <RouterView @update-menu="updateMenu" />
          <br v-for="i in 3" :key="i" />
          <ScrollToTop :visible="showBackToTop" />
        </div>
      </div>
    </LoadingOverlay>
  </div>
</template>
