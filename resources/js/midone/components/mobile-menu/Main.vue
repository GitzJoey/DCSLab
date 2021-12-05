<template>
    <div :class="{ 'mobile-menu--dashboard': dashboardLayout, 'mobile-menu--light': layout == 'top-menu' }" class="mobile-menu md:hidden">
        <div class="mobile-menu-bar">
            <a href="" class="flex mr-auto">
                <img alt="" class="w-6" src="/images/logo.svg" />
            </a>
            <a href="javascript:;">
                <BarChart2Icon class="w-8 h-8 text-gray-600 dark:text-white transform -rotate-90" @click="toggleMobileMenu" />
            </a>
        </div>
        <transition @enter="enter" @leave="leave">
            <ul v-if="activeMobileMenu" class="mobile-menu-box py-5 hidden">
                <template v-for="(menu, menuKey) in formattedMenu">
                    <li v-if="menu === 'devider'" :key="menu + menuKey" class="menu__devider my-6"></li>
                    <li v-else :key="menu + menuKey">
                        <a href="javascript:;" class="menu" :class="{ 'menu--active': menu.active, 'menu--open': menu.activeDropdown }" @click="linkTo(menu, router)">
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
                                            <div v-if="subMenu.subMenu" class="menu__sub-icon" :class="{ 'transform rotate-180': subMenu.activeDropdown }">
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
        </transition>
    </div>
</template>

<script>
import { defineComponent, computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useStore } from '../../store';
import { helper as $h } from '../../utils/helper';
import {
    activeMobileMenu,
    toggleMobileMenu,
    linkTo,
    enter,
    leave
} from './index';
import { nestedMenu } from '../../layouts/side-menu';
import mainMixins from '../../mixins/index';

export default defineComponent({
    props: {
        layout: {
            type: String,
            default: 'side-menu'
        },
        dashboardLayout: {
            type: Boolean,
            default: false
        }
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const store = useStore();
        const formattedMenu = ref([]);

        const { t } = mainMixins();

        const menuContext = computed(() => store.state.sideMenu.menu );

        const mobileMenu = computed(() =>
            nestedMenu(store.state.sideMenu.menu, route)
        );

        watch(
            computed(() => route.path),
          () => {
            formattedMenu.value = $h.toRaw(mobileMenu.value)
          }
        );

        watch(
            menuContext,() => {
                formattedMenu.value = nestedMenu(store.state.sideMenu.menu, route);
        });

        onMounted(() => {
            formattedMenu.value = $h.toRaw(mobileMenu.value)
        });

        return {
            activeMobileMenu,
            toggleMobileMenu,
            formattedMenu,
            router,
            linkTo,
            enter,
            leave,
            t
        }
    }
})
</script>
