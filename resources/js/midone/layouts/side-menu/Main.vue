<template>
    <DarkModeSwitcher />
    <MobileMenu :dashboard-layout="dashboardLayout" layout="side-menu" />
    <div>
        <div class="flex overflow-hidden">
            <nav class="intro-x side-nav side-nav--simple" v-if="menuMode === 'simple'">
                <a href="" class="intro-x flex items-center pl-5 pt-4" @click.prevent="switchMenu">
                    <img alt="" class="w-6" :src="assetPath('logo.svg')" />
                </a>
                <div class="side-nav__devider my-6"></div>
                <ul>
                    <template v-for="(menu, menuKey) in formattedMenu">
                        <li v-if="menu == 'devider'" :key="menu + menuKey" class="side-nav__devider my-6"></li>
                        <li v-else :key="menu + menuKey">
                            <Tippy tag="a" :content="menu.title" :options="{ placement: 'left' }" :href="menu.subMenu ? 'javascript:;' : router.resolve({ name: menu.pageName }).path" class="side-menu" :class="{ 'side-menu--active': menu.active, 'side-menu--open': menu.activeDropdown }" @click="linkTo(menu, router, $event)">
                                <div class="side-menu__icon">
                                    <component :is="menu.icon" />
                                </div>
                                <div class="side-menu__title">
                                    {{ menu.title }}
                                    <ChevronDownIcon v-if="$h.isset(menu.subMenu)" class="side-menu__sub-icon" :class="{ 'transform rotate-180': menu.activeDropdown }" />
                                </div>
                            </Tippy>
                            <transition @enter="enter" @leave="leave">
                                <ul v-if="menu.subMenu && menu.activeDropdown">
                                    <li v-for="(subMenu, subMenuKey) in menu.subMenu" :key="subMenuKey">
                                        <Tippy tag="a" :content="subMenu.title" :options="{ placement: 'left' }" :href="subMenu.subMenu ? 'javascript:;' : router.resolve({ name: subMenu.pageName }).path" class="side-menu" :class="{ 'side-menu--active': subMenu.active }" @click="linkTo(subMenu, router, $event)">
                                            <div class="side-menu__icon">
                                                <ChevronRightIcon />
                                            </div>
                                            <div class="side-menu__title">
                                                {{ subMenu.title }}
                                                <ChevronDownIcon v-if="$h.isset(subMenu.subMenu)" class="side-menu__sub-icon" :class="{ 'transform rotate-180': subMenu.activeDropdown }" />
                                            </div>
                                        </Tippy>
                                        <transition @enter="enter" @leave="leave">
                                            <ul v-if="subMenu.subMenu && subMenu.activeDropdown">
                                                <li v-for="(lastSubMenu, lastSubMenuKey) in subMenu.subMenu" :key="lastSubMenuKey">
                                                    <Tippy tag="a" :content="lastSubMenu.title" :options="{ placement: 'left' }" :href="lastSubMenu.subMenu ? 'javascript:;' : router.resolve({ name: lastSubMenu.pageName }).path" class="side-menu" :class="{ 'side-menu--active': lastSubMenu.active }" @click="linkTo(lastSubMenu, router, $event)">
                                                        <div class="side-menu__icon">
                                                            <ChevronsRightIcon />
                                                        </div>
                                                        <div class="side-menu__title">
                                                            {{ lastSubMenu.title }}
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

            <nav class="intro-y side-nav" v-if="menuMode === 'side'">
                <a href="" class="intro-x flex items-center pl-5 pt-4 mt-3" @click.prevent="switchMenu">
                    <img alt="Logo" class="w-6" :src="assetPath('logo.svg')" />
                    <span class="hidden xl:block text-white text-lg ml-3">DCS<span class="font-medium">Lab</span></span>
                </a>
                <div class="side-nav__devider my-6"></div>
                <ul>
                    <template v-if="formattedMenu.length === 0">
                        <li>
                            <LoadingIcon icon="puff" />
                        </li>
                    </template>
                    <template v-if="formattedMenu.length !== 0" v-for="(menu, menuKey) in formattedMenu">
                        <li v-if="menu === 'devider'" :key="menu + menuKey" class="side-nav__devider my-6"> </li>
                        <li v-else :key="menu + menuKey">
                            <SideMenuTooltip tag="a" :content="menu.title" :href=" menu.subMenu ? 'javascript:;' : router.resolve({ name: menu.pageName }).path" class="side-menu" :class="{ 'side-menu--active': menu.active, 'side-menu--open': menu.activeDropdown }" @click="linkTo(menu, router, $event)">
                                <div class="side-menu__icon">
                                    <component :is="menu.icon" />
                                </div>
                                <div class="side-menu__title">
                                    {{ menu.title }}
                                    <div v-if="menu.subMenu" class="side-menu__sub-icon" :class="{ 'transform rotate-180': menu.activeDropdown }">
                                        <ChevronDownIcon />
                                    </div>
                                </div>
                            </SideMenuTooltip>
                            <transition @enter="enter" @leave="leave">
                                <ul v-if="menu.subMenu && menu.activeDropdown">
                                    <li v-for="(subMenu, subMenuKey) in menu.subMenu" :key="subMenuKey">
                                        <SideMenuTooltip tag="a" :content="subMenu.title" :href="subMenu.subMenu ? 'javascript:;' : router.resolve({ name: subMenu.pageName }).path" class="side-menu" :class="{ 'side-menu--active': subMenu.active }" @click="linkTo(subMenu, router, $event)">
                                            <div class="side-menu__icon">
                                                <ChevronRightIcon />
                                            </div>
                                            <div class="side-menu__title">
                                                {{ subMenu.title }}
                                                <div v-if="subMenu.subMenu" class="side-menu__sub-icon" :class="{ 'transform rotate-180': subMenu.activeDropdown }">
                                                    <ChevronDownIcon />
                                                </div>
                                            </div>
                                        </SideMenuTooltip>
                                        <transition @enter="enter" @leave="leave">
                                            <ul v-if="subMenu.subMenu && subMenu.activeDropdown">
                                                <li v-for="(lastSubMenu, lastSubMenuKey) in subMenu.subMenu" :key="lastSubMenuKey">
                                                    <SideMenuTooltip tag="a" :content="lastSubMenu.title" :href="lastSubMenu.subMenu ? 'javascript:;' : router.resolve({ name: lastSubMenu.pageName }).path" class="side-menu" :class="{ 'side-menu--active': lastSubMenu.active }" @click="linkTo(lastSubMenu, router, $event)">
                                                        <div class="side-menu__icon">
                                                            <ChevronsRightIcon />
                                                        </div>
                                                        <div class="side-menu__title">
                                                            {{ lastSubMenu.title }}
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

            <div :class="{ 'content--dashboard': dashboardLayout, 'content':true }">
                <TopBar />
                <router-view />
                <template v-if="!dashboardLayout">
                    <br/>
                    <br/>
                    <br/>
                </template>
                <back-to-top :visible="!dashboardLayout"/>
            </div>
        </div>
    </div>
</template>

<script>
import {defineComponent, computed, provide, onMounted, ref, watch, onBeforeMount} from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useStore } from '../../store/index';
import { switchLang, getLang } from '../../lang';
import { helper as $h } from '../../utils/helper';
import { linkTo, nestedMenu, enter, leave } from './index';
import mainMixins from '../../mixins/index';

import TopBar from '../../components/top-bar/Main';
import MobileMenu from '../../components/mobile-menu/Main';
import SideMenuTooltip from '../../components/side-menu-tooltip/Main.vue';
import DarkModeSwitcher from '../../components/dark-mode-switcher/Main.vue';
import BackToTop from '../../components/back-to-top/Main.vue';

export default {
    name: "SideMenu",
    components: {
        TopBar,
        MobileMenu,
        SideMenuTooltip,
        DarkModeSwitcher,
        BackToTop
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const store = useStore();

        const dashboardLayout = ref(true);
        const formattedMenu = ref([]);

        const menuContext = computed(() => store.state.sideMenu.menu );

        const menuMode = ref('side');

        const { assetPath } = mainMixins();

        provide('setDashboardLayout', newVal => {
            dashboardLayout.value = newVal
        });

        created

        onMounted(() => {
            cash('body')
                .removeClass('error-page')
                .addClass('main');

            store.dispatch('main/fetchUserContext');
            store.dispatch('sideMenu/fetchMenuContext');

            dashboardLayout.value = true;

            localeSetup();
            goToLastRoute();
        });

        function localeSetup() {
            if (localStorage.getItem('DCSLAB_LANG') === null) {
                localStorage.setItem('DCSLAB_LANG', getLang());
            }

            if (localStorage.getItem('DCSLAB_LANG') !== getLang()) {
                switchLang(localStorage.getItem('DCSLAB_LANG'));
            }
        }

        function goToLastRoute() {
            if (localStorage.getItem('DCSLAB_LAST_ROUTE') !== null) {
                router.push({ name: localStorage.getItem('DCSLAB_LAST_ROUTE') });
            }
        }

        function switchMenu() {
            if (menuMode.value === 'simple') {
                menuMode.value = 'side'
            } else {
                menuMode.value = 'simple'
            }
        }

        watch(
            computed(() => route.path),() => {
                formattedMenu.value = nestedMenu($h.toRaw(menuContext.value), route);
        });

        watch(
            menuContext,() => {
                formattedMenu.value = nestedMenu(store.state.sideMenu.menu, route);
        });

        return {
            dashboardLayout,
            formattedMenu,
            router,
            assetPath,
            linkTo,
            enter,
            leave,
            menuMode,
            switchMenu,
        }
    }
}
</script>
