<script setup lang="ts">
import { useRoute, useRouter } from "vue-router";
import Tippy from "../../base-components/Tippy";
import Lucide from "../../base-components/Lucide";
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
  linkTo,
  enter,
  leave,
} from "./side-menu";
import { watch, reactive, ref, computed, onMounted, provide } from "vue";
import { useDashboardStore } from "../../stores/dashboard";
import { useI18n } from "vue-i18n";
import ScrollToTop from "../../base-components/ScrollToTop";
import NotificationWidget from "../../base-components/NotificationWidget";
import { EmailVerificationAlert } from "../../base-components/AlertPlaceholder";

const { t } = useI18n();
const route: Route = useRoute();
const router = useRouter();
let formattedMenu = reactive<Array<FormattedMenu | "divider">>([]);
const setFormattedMenu = (
  computedFormattedMenu: Array<FormattedMenu | "divider">
) => {
  Object.assign(formattedMenu, computedFormattedMenu);
};
const sideMenuStore = useSideMenuStore();
const sideMenu = computed(() => nestedMenu(sideMenuStore.menu, route));
const windowWidth = ref(window.innerWidth);

const dashboardStore = useDashboardStore();
const screenMask = computed(() => dashboardStore.screenMaskValue);

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
  <div class="py-5 md:py-0">
    <DarkModeSwitcher />
    <MainColorSwitcher />
    <MobileMenu />
    <TopBar :layout="dashboardStore.getLayoutValue" />
    <div class="flex overflow-hidden">

      <nav v-if="dashboardStore.getLayoutValue == 'side-menu'"
        class="side-nav w-[105px] xl:w-[260px] px-5 pb-16 overflow-x-hidden z-50 pt-32 -mt-4 hidden md:block">
        <ul>
          <template v-for="(menu, menuKey) in formattedMenu">
            <li v-if="menu == 'divider'" type="li"
              :class="[
                'side-nav__divider my-6',
                `opacity-0 animate-[0.4s_ease-in-out_0.1s_intro-divider] animate-fill-mode-forwards animate-delay-${(menuKey + 1) * 10}`,]"
              :key="'divider-' + menuKey"></li>
            <li v-else :key="menuKey">
              <Tippy as="a" :content="t(menu.title)" :options="{ placement: 'right', }" :disable="windowWidth > 1260"
                :href="menu.subMenu
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
                @click="(event: MouseEvent) => { event.preventDefault(); linkTo(menu, router); setFormattedMenu([...formattedMenu]); }"
                :class="[menu.active ? 'side-menu side-menu--active' : 'side-menu', { [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${(menuKey + 1) * 10}`]: !menu.active, },]">
                <div class="side-menu__icon">
                  <Lucide :icon="menu.icon" />
                </div>
                <div class="side-menu__title">
                  {{ t(menu.title) }}
                  <div v-if="menu.subMenu" :class="[
                    'side-menu__sub-icon',
                    { 'transform rotate-180': menu.activeDropdown },
                  ]">
                    <Lucide icon="ChevronDown" />
                  </div>
                </div>
              </Tippy>
              <Transition @enter="enter" @leave="leave">
                <ul v-if="menu.subMenu && menu.activeDropdown" :class="{ 'side-menu__sub-open': menu.activeDropdown }">
                  <li v-for="(subMenu, subMenuKey) in menu.subMenu" :key="subMenuKey">
                    <Tippy as="a" :content="t(subMenu.title)" :options="{ placement: 'right', }"
                      :disable="windowWidth > 1260" :href="subMenu.subMenu
                        ? '#'
                        : ((pageName: string | undefined) => {
                          try {
                            return router.resolve({
                              name: pageName,
                            }).fullPath;
                          } catch (err) {
                            return '';
                          }
                        })(subMenu.pageName)"
                      :class="[subMenu.active ? 'side-menu side-menu--active' : 'side-menu', { [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${(subMenuKey + 1) * 10}`]: !subMenu.active, },]"
                      @click="(event: MouseEvent) => {
                        event.preventDefault();
                        linkTo(subMenu, router);
                        setFormattedMenu([...formattedMenu]);
                      }">
                      <div class="side-menu__icon">
                        <Lucide :icon="subMenu.icon" />
                      </div>
                      <div class="side-menu__title">
                        {{ t(subMenu.title) }}
                        <div v-if="subMenu.subMenu" :class="[
                          'side-menu__sub-icon',
                          {
                            'transform rotate-180': subMenu.activeDropdown,
                          },
                        ]">
                          <Lucide icon="ChevronDown" />
                        </div>
                      </div>
                    </Tippy>
                    <Transition @enter="enter" @leave="leave" v-if="subMenu.subMenu">
                      <ul v-if="subMenu.subMenu && subMenu.activeDropdown" :class="{
                        'side-menu__sub-open': subMenu.activeDropdown,
                      }">
                        <li v-for="(
                            lastSubMenu, lastSubMenuKey
                          ) in subMenu.subMenu" :key="lastSubMenuKey">
                          <Tippy as="a" :content="t(lastSubMenu.title)" :options="{ placement: 'right', }"
                            :disable="windowWidth > 1260" :href="lastSubMenu.subMenu
                              ? '#'
                              : ((pageName: string | undefined) => {
                                try {
                                  return router.resolve({
                                    name: pageName,
                                  }).fullPath;
                                } catch (err) {
                                  return '';
                                }
                              })(lastSubMenu.pageName)"
                            :class="[lastSubMenu.active ? 'side-menu side-menu--active' : 'side-menu', { [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${(lastSubMenuKey + 1) * 10}`]: !lastSubMenu.active, },]"
                            @click="(event: MouseEvent) => {
                              event.preventDefault();
                              linkTo(lastSubMenu, router);
                              setFormattedMenu([...formattedMenu]);
                            }">
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

      <nav v-else
        class="side-nav side-nav--simple w-[105px] px-5 pb-16 overflow-x-hidden z-50 pt-32 -mt-4 hidden md:block">
        <ul>
          <template v-for="(menu, menuKey) in formattedMenu">
            <li v-if="menu == 'divider'" type="li" :class="[
              'side-nav__divider my-6',
              `opacity-0 animate-[0.4s_ease-in-out_0.1s_intro-divider] animate-fill-mode-forwards animate-delay-${(menuKey + 1) * 10}`,
            ]" :key="'divider-' + menuKey"></li>
            <li v-else :key="menuKey">
              <Tippy as="a" :content="t(menu.title)" :options="{ placement: 'right', }" :href="menu.subMenu
                ? '#'
                : ((pageName: string | undefined) => {
                  try {
                    return router.resolve({
                      name: pageName,
                    }).fullPath;
                  } catch (err) {
                    return '';
                  }
                })(menu.pageName)"
                @click="(event: MouseEvent) => { event.preventDefault(); linkTo(menu, router); setFormattedMenu([...formattedMenu]); }"
                :class="[menu.active ? 'side-menu side-menu--active' : 'side-menu',
                {
                  [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${(menuKey + 1) * 10}`]: !menu.active,
                },
                ]">
                <div class="side-menu__icon">
                  <Lucide :icon="menu.icon" />
                </div>
                <div class="side-menu__title">
                  {{ t(menu.title) }}
                  <div v-if="menu.subMenu" :class="[
                    'side-menu__sub-icon',
                    { 'transform rotate-180': menu.activeDropdown },
                  ]">
                    <Lucide icon="ChevronDown" />
                  </div>
                </div>
              </Tippy>
              <Transition @enter="enter" @leave="leave">
                <ul v-if="menu.subMenu && menu.activeDropdown" :class="{ 'side-menu__sub-open': menu.activeDropdown }">
                  <li v-for="(subMenu, subMenuKey) in menu.subMenu" :key="subMenuKey">
                    <Tippy as="a" :content="t(subMenu.title)" :options="{ placement: 'right', }" :href="subMenu.subMenu
                      ? '#'
                      : ((pageName: string | undefined) => {
                        try {
                          return router.resolve({
                            name: pageName,
                          }).fullPath;
                        } catch (err) {
                          return '';
                        }
                      })(subMenu.pageName)"
                      :class="[subMenu.active ? 'side-menu side-menu--active' : 'side-menu', { [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${(subMenuKey + 1) * 10}`]: !subMenu.active, },]"
                      @click="(event: MouseEvent) => { event.preventDefault(); linkTo(subMenu, router); setFormattedMenu([...formattedMenu]); }">
                      <div class="side-menu__icon">
                        <Lucide :icon="subMenu.icon" />
                      </div>
                      <div class="side-menu__title">
                        {{ t(subMenu.title) }}
                        <div v-if="subMenu.subMenu" :class="[
                          'side-menu__sub-icon',
                          {
                            'transform rotate-180': subMenu.activeDropdown,
                          },
                        ]">
                          <Lucide icon="ChevronDown" />
                        </div>
                      </div>
                    </Tippy>
                    <Transition @enter="enter" @leave="leave" v-if="subMenu.subMenu">
                      <ul v-if="subMenu.subMenu && subMenu.activeDropdown" :class="{
                        'side-menu__sub-open': subMenu.activeDropdown,
                      }">
                        <li v-for="(
                            lastSubMenu, lastSubMenuKey
                          ) in subMenu.subMenu" :key="lastSubMenuKey">
                          <Tippy as="a" :content="t(lastSubMenu.title)" :options="{ placement: 'right', }" :href="lastSubMenu.subMenu
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
                            :class="[lastSubMenu.active ? 'side-menu side-menu--active' : 'side-menu', { [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${(lastSubMenuKey + 1) * 10}`]: !lastSubMenu.active, },]"
                            @click="(event: MouseEvent) => {
                              event.preventDefault();
                              linkTo(lastSubMenu, router);
                              setFormattedMenu([...formattedMenu]);
                            }">
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

      <div :class="[
        'max-w-full md:max-w-none rounded-[30px] md:rounded-none px-4 md:px-[22px] min-w-0 min-h-screen bg-slate-100 flex-1 md:pt-20 pb-10 mt-5 md:mt-1 relative dark:bg-darkmode-700',
        'before:content-[\'\'] before:w-full before:h-px before:block',
      ]">
        <EmailVerificationAlert />
        <RouterView />
        <br v-for="i in 3" :key="i" />
        <ScrollToTop :visible="showBackToTop" />
      </div>
    </div>
    <NotificationWidget />
  </div>
</template>
