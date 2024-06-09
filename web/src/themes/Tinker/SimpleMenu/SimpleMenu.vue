<script setup lang="ts">
import "@/assets/css/themes/rubick/side-nav.css";
import { useRoute, useRouter } from "vue-router";
import logoUrl from "@/assets/images/logo.svg";
import Tippy from "@/components/Base/Tippy";
import Lucide from "@/components/Base/Lucide";
import TopBar from "@/components/Themes/Rubick/TopBar";
import MobileMenu from "@/components/MobileMenu";
import { useMenuStore, Menu as sMenu } from "@/stores/menu";
import {
  type ProvideForceActiveMenu,
  forceActiveMenu,
  type Route,
  type FormattedMenu,
  nestedMenu,
  linkTo,
  enter,
  leave,
} from "./simple-menu";
import { watch, reactive, ref, computed, onMounted, provide } from "vue";
import ScrollToTop from "@/components/Base/ScrollToTop";
import LoadingOverlay from "@/components/LoadingOverlay";
import { EmailVerificationAlert } from "@/components/AlertPlaceholder";
import { useDashboardStore } from "@/stores/dashboard";
import DashboardService from "@/services/DashboardService";
import { useZiggyRouteStore } from "@/stores/ziggy-route";
import { Config } from "ziggy-js";
import { useI18n } from "vue-i18n";

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
const menu = computed(() => nestedMenu(menuStore.menu("simple-menu"), route));

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
        <div class="flex mt-[4.7rem] md:mt-0 overflow-hidden">
          <nav
            class="side-nav side-nav--simple hidden md:block md:w-[100px] xl:w-[100px] px-5 pb-16 overflow-x-hidden z-10"
          >
            <RouterLink
              :to="{ name: 'side-menu-dashboard-maindashboard' }"
              class="flex items-center pt-4 pl-5 mt-3 intro-x"
            >
              <img
                alt="DCSLab"
                class="w-6"
                :src="logoUrl"
              />
            </RouterLink>
            <div class="my-6 side-nav__divider"></div>
            <ul>
              <template v-for="(menu, menuKey) in formattedMenu">
                <li
                  v-if="menu == 'divider'"
                  type="li"
                  class="my-6 side-nav__divider"
                  :key="'divider-' + menuKey"
                ></li>
                <li v-else :key="menuKey">
                  <Tippy
                    as="a"
                    :content="t(menu.title)"
                    :options="{
                      placement: 'right',
                    }"
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
                    @click="(event: MouseEvent) => {
                      event.preventDefault();
                      linkTo(menu, router);
                      setFormattedMenu([...formattedMenu]);
                    }"
                    :class="[
                      menu.active ? 'side-menu side-menu--active' : 'side-menu',
                    ]"
                  >
                    <div class="side-menu__icon">
                      <Lucide :icon="menu.icon" />
                    </div>
                    <div class="side-menu__title">
                      {{ t(menu.title) }}
                      <div
                        v-if="menu.subMenu"
                        :class="[
                          'side-menu__sub-icon',
                          { 'transform rotate-180': menu.activeDropdown },
                        ]"
                      >
                        <Lucide icon="ChevronDown" />
                      </div>
                    </div>
                  </Tippy>
                  <Transition @enter="enter" @leave="leave">
                    <ul
                      v-if="menu.subMenu && menu.activeDropdown"
                      :class="{ 'side-menu__sub-open': menu.activeDropdown }"
                    >
                      <li
                        v-for="(subMenu, subMenuKey) in menu.subMenu"
                        :key="subMenuKey"
                      >
                        <Tippy
                          as="a"
                          :content="t(subMenu.title)"
                          :options="{
                            placement: 'right',
                          }"
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
                          :class="[
                            subMenu.active
                              ? 'side-menu side-menu--active'
                              : 'side-menu',
                          ]"
                          @click="(event: MouseEvent) => {
                            event.preventDefault();
                            linkTo(subMenu, router);
                            setFormattedMenu([...formattedMenu]);
                          }"
                        >
                          <div class="side-menu__icon">
                            <Lucide :icon="subMenu.icon" />
                          </div>
                          <div class="side-menu__title">
                            {{ t(subMenu.title) }}
                            <div
                              v-if="subMenu.subMenu"
                              :class="[
                                'side-menu__sub-icon',
                                { 'transform rotate-180': subMenu.activeDropdown },
                              ]"
                            >
                              <Lucide icon="ChevronDown" />
                            </div>
                          </div>
                        </Tippy>
                        <Transition
                          @enter="enter"
                          @leave="leave"
                          v-if="subMenu.subMenu"
                        >
                          <ul
                            v-if="subMenu.subMenu && subMenu.activeDropdown"
                            :class="{
                              'side-menu__sub-open': subMenu.activeDropdown,
                            }"
                          >
                            <li
                              v-for="(
                                lastSubMenu, lastSubMenuKey
                              ) in subMenu.subMenu"
                              :key="lastSubMenuKey"
                            >
                              <Tippy
                                as="a"
                                :content="t(lastSubMenu.title)"
                                :options="{
                                  placement: 'right',
                                }"
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
                                :class="[
                                  lastSubMenu.active
                                    ? 'side-menu side-menu--active'
                                    : 'side-menu',
                                ]"
                                @click="(event: MouseEvent) => {
                                  event.preventDefault();
                                  linkTo(lastSubMenu, router);
                                  setFormattedMenu([...formattedMenu]);
                                }"
                              >
                                <div class="side-menu__icon">
                                  <Lucide :icon="lastSubMenu.icon" />
                                </div>
                                <div class="side-menu__title">
                                  {{ t(lastSubMenu.title) }}
                                </div>
                              </Tippy>
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
              'rounded-[30px] md:rounded-[35px/50px_0px_0px_0px] min-w-0 min-h-screen max-w-full md:max-w-none bg-slate-100 flex-1 pb-10 px-4 md:px-6 relative md:ml-4 dark:bg-darkmode-700',
              'before:content-[\'\'] before:w-full before:h-px before:block',
              'after:content-[\'\'] after:z-[-1] after:rounded-[40px_0px_0px_0px] after:w-full after:inset-y-0 after:absolute after:left-0 after:bg-white/10 after:mt-8 after:-ml-4 after:dark:bg-darkmode-400/50 after:hidden md:after:block',
            ]"
          >
            <TopBar />
            <EmailVerificationAlert />
            <RouterView @update-menu="updateMenu" />
            <br v-for="i in 3" :key="i" />
            <ScrollToTop :visible="showBackToTop" />
          </div>
        </div>
      </div>
    </LoadingOverlay>
  </div>
</template>
