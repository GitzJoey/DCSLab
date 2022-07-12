<template>
  <div class="mobile-menu md:hidden" :class="{'mobile-menu--active': activeMobileMenu,}">
    <div class="mobile-menu-bar">
      <a href="" class="flex mr-auto">
        <img alt="" class="w-6" src="@/assets/images/logo.svg" />
      </a>
      <a href="javascript:;" class="mobile-menu-toggler">
        <BarChart2Icon class="w-8 h-8 text-white transform -rotate-90" @click="toggleMobileMenu" />
      </a>
    </div>
    <div class="scrollable">
      <a href="javascript:;" class="mobile-menu-toggler">
        <XCircleIcon class="w-8 h-8 text-white transform -rotate-90" @click="toggleMobileMenu" />
      </a>
      <ul class="scrollable__content py-2">
        <template v-for="(menu, menuKey) in formattedMenu">
          <li v-if="menu == 'devider'" :key="menu + menuKey" class="menu__devider my-6"></li>
          <li v-else :key="menu + menuKey">
            <a href="javascript:;" class="menu" :class="{ 'menu--active': menu.active, 'menu--open': menu.activeDropdown, }" @click="linkTo(menu, router)">
              <div class="menu__icon">
                <component :is="menu.icon" />
              </div>
              <div class="menu__title">
                {{ t(menu.title) }}
                <div v-if="menu.subMenu" class="menu__sub-icon" :class="{ 'transform rotate-180': menu.activeDropdown }">
                  <ChevronDownIcon />
                </div>
              </div>
            </a>
            <transition @enter="enter" @leave="leave">
              <ul v-if="menu.subMenu && menu.activeDropdown">
                <li v-for="(subMenu, subMenuKey) in menu.subMenu" :key="subMenuKey">
                  <a href="javascript:;" class="menu" :class="{ 'menu--active': subMenu.active }" @click="linkTo(subMenu, router)">
                    <div class="menu__icon">
                      <ChevronRightIcon />
                    </div>
                    <div class="menu__title">
                      {{ t(subMenu.title) }}
                      <div v-if="subMenu.subMenu" class="menu__sub-icon" :class="{ 'transform rotate-180': subMenu.activeDropdown, }">
                        <ChevronDownIcon />
                      </div>
                    </div>
                  </a>
                  <transition @enter="enter" @leave="leave">
                    <ul v-if="subMenu.subMenu && subMenu.activeDropdown">
                      <li v-for="(lastSubMenu, lastSubMenuKey) in subMenu.subMenu" :key="lastSubMenuKey">
                        <a href="javascript:;" class="menu" :class="{ 'menu--active': lastSubMenu.active }" @click="linkTo(lastSubMenu, router)">
                          <div class="menu__icon">
                            <ChevronsRightIcon />
                          </div>
                          <div class="menu__title">
                            {{ t(lastSubMenu.title) }}
                          </div>
                        </a>
                      </li>
                    </ul>
                  </transition>
                </li>
              </ul>
            </transition>
          </li>
        </template>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";
import { helper as $h } from "@/utils/helper";
import { useSideMenuStore } from "@/stores/side-menu";
import {
  activeMobileMenu,
  toggleMobileMenu,
  linkTo,
  enter,
  leave,
} from "./index";
import { nestedMenu } from "@/layouts/side-menu";
import dom from "@left4code/tw-starter/dist/js/dom";
import SimpleBar from "simplebar";

const { t } = useI18n();

const route = useRoute();
const router = useRouter();
const formattedMenu = ref([]);
const sideMenuStore = useSideMenuStore();
const mobileMenu = computed(() => sideMenuStore.menu );

watch(
  computed(() => route.path),
  () => {
    formattedMenu.value = $h.toRaw(nestedMenu(mobileMenu.value, route));
  }
);

watch(
  mobileMenu, () => {
    formattedMenu.value = $h.toRaw(nestedMenu(mobileMenu.value, route));
});

onMounted(() => {
  if (dom(".mobile-menu .scrollable").length) {
    const bar = new SimpleBar(dom(".mobile-menu .scrollable")[0]);
  }
  formattedMenu.value = $h.toRaw(mobileMenu.value);
});
</script>
