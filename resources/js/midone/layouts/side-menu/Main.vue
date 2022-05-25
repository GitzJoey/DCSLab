<template>
  <div class="py-5 md:py-0 -mx-3 px-3 sm:-mx-8 sm:px-8 bg-black/[0.15]">
    <DarkModeSwitcher :visible="showTheme" />
    <MainColorSwitcher :visible="showTheme" />
    <BackToTop :visible="showBackToTop" @easter-click="easterClick" />
    <MobileMenu />
    <div class="flex mt-[4.7rem] md:mt-0 overflow-hidden">
      <nav class="side-nav side-nav--simple" v-if="menuMode === 'simple'">
        <a href="" class="intro-x flex items-center pl-5 pt-4" @click.prevent="switchMenu">
          <img alt="" class="w-6" :src="assetPath('logo.svg')" />
        </a>
        <div class="side-nav__devider my-6"></div>
        <ul>
          <template v-for="(menu, menuKey) in formattedMenu">
            <li v-if="menu == 'devider'" :key="menu + menuKey" class="side-nav__devider my-6"></li>
            <li v-else :key="menu + menuKey">
              <Tippy tag="a" :content="t(menu.title)" :options="{ placement: 'left', }" :href="menu.subMenu ? 'javascript:;' : router.resolve({ name: menu.pageName }).path" class="side-menu" :class="{ 'side-menu--active': menu.active, 'side-menu--open': menu.activeDropdown, }" @click="linkTo(menu, router, $event)">
                <div class="side-menu__icon">
                  <component :is="menu.icon" />
                </div>
                <div class="side-menu__title">
                  {{ t(menu.title) }}
                  <ChevronDownIcon v-if="$h.isset(menu.subMenu)" class="side-menu__sub-icon" :class="{ 'transform rotate-180': menu.activeDropdown }" />
                </div>
              </Tippy>
              <transition @enter="enter" @leave="leave">
                <ul v-if="$h.isset(menu.subMenu) && menu.activeDropdown">
                  <li v-for="(subMenu, subMenuKey) in menu.subMenu" :key="subMenuKey">
                    <Tippy tag="a" :content="t(subMenu.title)" :options="{ placement: 'left', }" :href="subMenu.subMenu ? 'javascript:;' : router.resolve({ name: subMenu.pageName }).path" class="side-menu" :class="{ 'side-menu--active': subMenu.active }" @click="linkTo(subMenu, router, $event)">
                      <div class="side-menu__icon">
                        <ChevronRightIcon />
                      </div>
                      <div class="side-menu__title">
                        {{ t(subMenu.title) }}
                        <ChevronDownIcon v-if="$h.isset(subMenu.subMenu)" class="side-menu__sub-icon" :class="{ 'transform rotate-180': subMenu.activeDropdown, }" />
                      </div>
                    </Tippy>
                    <transition @enter="enter" @leave="leave">
                      <ul v-if="$h.isset(subMenu.subMenu) && subMenu.activeDropdown">
                        <li v-for="(lastSubMenu, lastSubMenuKey) in subMenu.subMenu" :key="lastSubMenuKey">
                          <Tippy tag="a" :content="t(lastSubMenu.title)" :options="{ placement: 'left', }" :href="lastSubMenu.subMenu ? 'javascript:;' : router.resolve({ name: lastSubMenu.pageName }).path" class="side-menu" :class="{ 'side-menu--active': lastSubMenu.active }" @click="linkTo(lastSubMenu, router, $event)">
                            <div class="side-menu__icon">
                              <ChevronsRightIcon />
                            </div>
                            <div class="side-menu__title">
                              {{ t(lastSubMenu.title) }}
                            </div>
                          </Tippy>
                        </li>
                      </ul>
                    </transition>
                  </li>
                </ul>
              </transition>
            </li>
          </template>
        </ul>
      </nav>

      <nav class="side-nav" v-if="menuMode === 'side'">
        <router-link :to="{ name: 'side-menu-dashboard-maindashboard' }" tag="a" class="intro-x flex items-center pl-5 pt-4 mt-3" event="" @click.native.prevent="switchMenu">
          <img alt="" class="w-6" :src="assetPath('logo.svg')" />
          <span class="hidden xl:block text-white text-lg ml-3">DCS<span class="font-medium">Lab</span></span>
        </router-link>
        <div class="side-nav__devider my-6"></div>
        <ul>
          <template v-if="formattedMenu.length === 0">
            <li>
              <LoadingIcon icon="puff" />
            </li>
          </template>
          <template v-for="(menu, menuKey) in formattedMenu">
            <li v-if="menu == 'devider'" :key="menu + menuKey" class="side-nav__devider my-6"></li>
            <li v-else :key="menu + menuKey">
              <SideMenuTooltip tag="a" :content="t(menu.title)" :href="menu.subMenu ? 'javascript:;' : router.resolve({ name: menu.pageName }).path" class="side-menu" :class="{ 'side-menu--active': menu.active, 'side-menu--open': menu.activeDropdown, }" @click="linkTo(menu, router, $event)">
                <div class="side-menu__icon">
                  <component :is="menu.icon" />
                </div>
                <div class="side-menu__title">
                  {{ t(menu.title) }}
                  <div v-if="menu.subMenu" class="side-menu__sub-icon" :class="{ 'transform rotate-180': menu.activeDropdown }">
                    <ChevronDownIcon />
                  </div>
                </div>
              </SideMenuTooltip>
              <transition @enter="enter" @leave="leave">
                <ul v-if="menu.subMenu && menu.activeDropdown">
                  <li v-for="(subMenu, subMenuKey) in menu.subMenu" :key="subMenuKey">
                    <SideMenuTooltip tag="a" :content="t(subMenu.title)" :href="subMenu.subMenu ? 'javascript:;' : router.resolve({ name: subMenu.pageName }).path" class="side-menu" :class="{ 'side-menu--active': subMenu.active }" @click="linkTo(subMenu, router, $event)">
                      <div class="side-menu__icon">
                        <ChevronRightIcon />
                      </div>
                      <div class="side-menu__title">
                        {{ t(subMenu.title) }}
                        <div v-if="subMenu.subMenu" class="side-menu__sub-icon" :class="{ 'transform rotate-180': subMenu.activeDropdown, }">
                          <ChevronDownIcon />
                        </div>
                      </div>
                    </SideMenuTooltip>
                    <transition @enter="enter" @leave="leave">
                      <ul v-if="subMenu.subMenu && subMenu.activeDropdown">
                        <li v-for="(lastSubMenu, lastSubMenuKey) in subMenu.subMenu" :key="lastSubMenuKey">
                          <SideMenuTooltip tag="a" :content="t(lastSubMenu.title)" :href="lastSubMenu.subMenu ? 'javascript:;' : router.resolve({ name: lastSubMenu.pageName }).path" class="side-menu" :class="{ 'side-menu--active': lastSubMenu.active }" @click="linkTo(lastSubMenu, router, $event)">
                            <div class="side-menu__icon">
                              <ChevronsDownIcon />
                            </div>
                            <div class="side-menu__title">
                              {{ t(lastSubMenu.title) }}
                            </div>
                          </SideMenuTooltip>
                        </li>
                      </ul>
                    </transition>
                  </li>
                </ul>
              </transition>
            </li>
          </template>
        </ul>
      </nav>

      <div class="content">
        <TopBar />
        <router-view />
      </div>

      <Notification refKey="popNotification" class="flex flex-col sm:flex-row" :options="{ duration: 3000 }">
        <div class="w-24 font-medium">{{ popNotificationMessage }} </div>
      </Notification>

      <Pusher />
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch, provide } from "vue";
import { useRoute, useRouter } from "vue-router";
import { helper as $h } from "@/utils/helper";
import { getLang, switchLang } from "@/lang";
import { useI18n } from "vue-i18n";
import { assetPath } from "@/mixins";
import { useSideMenuStore } from "@/stores/side-menu";
import { useUserContextStore } from "@/stores/user-context";
import TopBar from "@/components/top-bar/Main.vue";
import MobileMenu from "@/components/mobile-menu/Main.vue";
import DarkModeSwitcher from "@/components/dark-mode-switcher/Main.vue";
import MainColorSwitcher from "@/components/main-color-switcher/Main.vue";
import SideMenuTooltip from "@/components/side-menu-tooltip/Main.vue";
import BackToTop from "@/components/back-to-top/Main.vue";
import Pusher from "@/components/pusher/Main.vue";
import { linkTo, nestedMenu, enter, leave } from "./index";
import dom from "@left4code/tw-starter/dist/js/dom";

const { t } = useI18n();

const route = useRoute();
const router = useRouter();
const formattedMenu = ref([]);

const sideMenuStore = useSideMenuStore();
const sideMenu = computed(() => sideMenuStore.menu);

const userContextStore = useUserContextStore();
const userContext = computed(() => userContextStore.userContext);

const menuMode = ref('side');
const showBackToTop = ref(false);
const showTheme = ref(false);
const popNotificationMessage = ref('');

const popNotification = ref();

provide("bind[popNotification]", (el) => {
  popNotification.value = el;
});

provide('triggerPopNotification', (message) => {
  popNotificationToast(message);
});

const handlescroll = () => {
  if (window.scrollY > 100) {
    showBackToTop.value = true;
  } else {
    showBackToTop.value = false;
  }
}

window.addEventListener('scroll', handlescroll);

onMounted(async () => {
  dom("body").removeClass("error-page").removeClass("login").addClass("main");
  
  await userContextStore.fetchUserContext();
  sideMenuStore.fetchMenu();
  
  localeSetup();
  goToLastRoute();
});

onUnmounted(() => {
  window.removeEventListener('scroll', handlescroll);
});

const localeSetup = () => {
  if (localStorage.getItem('DCSLAB_LANG') === null) {
    localStorage.setItem('DCSLAB_LANG', getLang());
  }

  if (localStorage.getItem('DCSLAB_LANG') !== getLang()) {
    switchLang(localStorage.getItem('DCSLAB_LANG'));
  }
}

const goToLastRoute = () => {
  if (sessionStorage.getItem('DCSLAB_LAST_ROUTE') !== null) {
    router.push({ name: sessionStorage.getItem('DCSLAB_LAST_ROUTE') });
  }
}

const switchMenu = () => {
  if (menuMode.value === 'simple') {
    menuMode.value = 'side'
  } else {
    menuMode.value = 'simple'
  }
}

const easterClick = () => {
  showTheme.value = !showTheme.value;
}

const setDashboardLayout = (settings) => {
  let theme = settings.theme;

  if (theme.indexOf('-mini') > 0) {
    menuMode.value = 'simple';
  }
}

const popNotificationToast = (message) => {
    popNotificationMessage.value = message;
    popNotification.value.showToast();
} 

watch(
  computed(() => route.path),
  () => {
    formattedMenu.value = $h.toRaw(nestedMenu(sideMenu.value, route));
  }
);
watch(
  sideMenu, () => {
    formattedMenu.value = $h.toRaw(nestedMenu(sideMenu.value, route));
});
watch(
  userContext, () => {
    setDashboardLayout(userContext.value.selected_settings);
});
</script>
