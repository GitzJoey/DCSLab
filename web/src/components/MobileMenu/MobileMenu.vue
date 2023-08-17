<script setup lang="ts">
import { useRoute, useRouter } from "vue-router";
import { twMerge } from "tailwind-merge";
import logoUrl from "../../assets/images/logo.svg";
import Lucide from "../../base-components/Lucide";
import { useSideMenuStore } from "../../stores/side-menu";
import { FormattedMenu, nestedMenu } from "../../layouts/SideMenu/side-menu";
import { linkTo, enter, leave } from "./mobile-menu";
import { watch, reactive, computed, onMounted, ref } from "vue";
import SimpleBar from "simplebar";

const route = useRoute();
const router = useRouter();
let formattedMenu = reactive<Array<FormattedMenu | "divider">>([]);
const setFormattedMenu = (
  computedFormattedMenu: Array<FormattedMenu | "divider">
) => {
  Object.assign(formattedMenu, computedFormattedMenu);
};
const sideMenuStore = useSideMenuStore();
const sideMenu = computed(() => nestedMenu(sideMenuStore.menu, route));

const activeMobileMenu = ref(false);
const setActiveMobileMenu = (active: boolean) => {
  activeMobileMenu.value = active;
};

const scrollableRef = ref<HTMLDivElement>();

watch(sideMenu, () => {
  setFormattedMenu(sideMenu.value);
});

onMounted(() => {
  if (scrollableRef.value) {
    new SimpleBar(scrollableRef.value);
  }

  setFormattedMenu(sideMenu.value);
});
</script>

<template>
  <!-- BEGIN: Mobile Menu -->
  <div
    :class="[
      'mobile-menu w-full fixed bg-primary/90 z-[60] border-b border-white/[0.08] -mt-5 -mx-3 sm:-mx-8 mb-6 dark:bg-darkmode-800/90 md:hidden',
      'before:content-[\'\'] before:w-full before:h-screen before:z-10 before:fixed before:inset-x-0 before:bg-black/90 before:transition-opacity before:duration-200 before:ease-in-out',
      !activeMobileMenu && 'before:invisible before:opacity-0',
      activeMobileMenu && 'before:visible before:opacity-100',
    ]"
  >
    <div class="h-[70px] px-3 sm:px-8 flex items-center">
      <a href="" class="flex mr-auto">
        <img
          alt="Midone Tailwind HTML Admin Template"
          class="w-6"
          :src="logoUrl"
        />
      </a>
      <a href="#" @click="(e) => e.preventDefault()">
        <Lucide
          icon="BarChart2"
          class="w-8 h-8 text-white transform -rotate-90"
          @click="
            () => {
              setActiveMobileMenu(!activeMobileMenu);
            }
          "
        />
      </a>
    </div>
    <div
      ref="scrollableRef"
      :class="
        twMerge([
          'h-screen z-20 top-0 left-0 w-[270px] -ml-[100%] bg-primary transition-all duration-300 ease-in-out dark:bg-darkmode-800',
          '[&[data-simplebar]]:fixed [&_.simplebar-scrollbar]:before:bg-black/50',
          activeMobileMenu && 'ml-0',
        ])
      "
    >
      <a
        href="#"
        @click="(e) => e.preventDefault()"
        :class="[
          'fixed top-0 right-0 mt-4 mr-4 transition-opacity duration-200 ease-in-out',
          !activeMobileMenu && 'invisible opacity-0',
          activeMobileMenu && 'visible opacity-100',
        ]"
      >
        <Lucide
          icon="XCircle"
          class="w-8 h-8 text-white transform -rotate-90"
          @click="
            () => {
              setActiveMobileMenu(!activeMobileMenu);
            }
          "
        />
      </a>
      <ul class="py-2">
        <!-- BEGIN: First Child -->
        <template v-for="(menu, menuKey) in formattedMenu">
          <li v-if="menu == 'divider'" class="my-6 menu__divider"></li>
          <li v-else>
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
              @click="
                (event) => {
                  event.preventDefault();
                  linkTo(menu, router, setActiveMobileMenu);
                  setFormattedMenu([...formattedMenu]);
                }
              "
              :class="[menu.active ? 'menu menu--active' : 'menu']"
            >
              <div class="menu__icon">
                <Lucide :icon="menu.icon" />
              </div>
              <div class="menu__title">
                {{ menu.title }}
                <div
                  v-if="menu.subMenu"
                  :class="[
                    'menu__sub-icon',
                    menu.activeDropdown && 'transform rotate-180',
                  ]"
                >
                  <Lucide icon="ChevronDown" />
                </div>
              </div>
            </a>
            <Transition @enter="enter" @leave="leave">
              <ul
                v-if="menu.subMenu && menu.activeDropdown"
                :class="{ 'menu__sub-open': menu.activeDropdown }"
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
                    @click="
                      (event) => {
                        event.preventDefault();
                        linkTo(subMenu, router, setActiveMobileMenu);
                        setFormattedMenu([...formattedMenu]);
                      }
                    "
                    :class="[subMenu.active ? 'menu menu--active' : 'menu']"
                  >
                    <div class="menu__icon">
                      <Lucide :icon="subMenu.icon" />
                    </div>
                    <div class="menu__title">
                      {{ subMenu.title }}
                      <div
                        v-if="subMenu.subMenu"
                        :class="[
                          'menu__sub-icon',
                          subMenu.activeDropdown && 'transform rotate-180',
                        ]"
                      >
                        <Lucide icon="ChevronDown" />
                      </div>
                    </div>
                  </a>
                  <Transition @enter="enter" @leave="leave">
                    <ul
                      v-if="subMenu.subMenu && subMenu.activeDropdown"
                      :class="{ 'menu__sub-open': subMenu.activeDropdown }"
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
                          @click="
                            (event) => {
                              event.preventDefault();
                              linkTo(lastSubMenu, router, setActiveMobileMenu);
                              setFormattedMenu([...formattedMenu]);
                            }
                          "
                          :class="[
                            lastSubMenu.active ? 'menu menu--active' : 'menu',
                          ]"
                        >
                          <div class="menu__icon">
                            <Lucide :icon="lastSubMenu.icon" />
                          </div>
                          <div class="menu__title">{{ lastSubMenu.title }}</div>
                        </a>
                      </li>
                    </ul>
                  </Transition>
                </li>
              </ul>
            </Transition>
          </li>
        </template>
        <!-- END: First Child -->
      </ul>
    </div>
  </div>
  <!-- END: Mobile Menu -->
</template>
