<script setup lang="ts">
import "@/assets/css/themes/enigma/side-nav.css";
import { useRoute, useRouter } from "vue-router";
import Tippy from "@/components/Base/Tippy";
import Lucide from "@/components/Base/Lucide";
import TopBar from "@/components/Themes/Enigma/TopBar";
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
} from "./side-menu";
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
const menu = computed(() => nestedMenu(menuStore.menu("side-menu"), route));
const windowWidth = ref(window.innerWidth);

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

  window.addEventListener("resize", () => {
    windowWidth.value = window.innerWidth;
  });
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
          'enigma py-5 px-5 md:py-0 sm:px-8 md:px-0',
          'before:content-[\'\'] before:bg-gradient-to-b before:from-theme-1 before:to-theme-2 dark:before:from-darkmode-800 dark:before:to-darkmode-800 md:before:bg-none md:bg-slate-200 md:dark:bg-darkmode-800 before:fixed before:inset-0 before:z-[-1]',
        ]"
      >
        <MobileMenu />
        <TopBar layout="side-menu" />
        <div class="flex overflow-hidden">
          <nav
            class="side-nav w-[100px] xl:w-[260px] px-5 pb-16 overflow-x-hidden z-50 pt-32 -mt-4 hidden md:block"
          >
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
                    :disable="windowWidth > 1260"
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
                          :disable="windowWidth > 1260"
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
                                {
                                  'transform rotate-180': subMenu.activeDropdown,
                                },
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
                                :disable="windowWidth > 1260"
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
              'max-w-full md:max-w-none rounded-[30px] md:rounded-none px-4 md:px-[22px] min-w-0 min-h-screen bg-slate-100 flex-1 md:pt-20 pb-10 mt-5 md:mt-1 relative dark:bg-darkmode-700',
              'before:content-[\'\'] before:w-full before:h-px before:block',
            ]"
          >
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
