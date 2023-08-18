<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import axios from "../../axios";
import Lucide from "../../base-components/Lucide";
import logoUrl from "../../assets/images/logo.svg";
import { Menu, Slideover } from "../../base-components/Headless";
import _ from "lodash";
import DashboardService from "../../services/DashboardService";
import { useDashboardStore } from "../../stores/dashboard";
import { useSideMenuStore, Menu as sMenu } from "../../stores/side-menu";
import { useZiggyRouteStore } from "../../stores/ziggy-route";
import { useUserContextStore } from "../../stores/user-context";
import { UserProfile } from "../../types/models/UserProfile";
import { Config } from "ziggy-js";
import Button from "../../base-components/Button";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import defUserUrl from "../../assets/images/def-user.png";
import UserLocation from "../../base-components/UserLocation";

const dashboardService = new DashboardService();

const dashboardStore = useDashboardStore();
const sideMenuStore = useSideMenuStore();
const ziggyRouteStore = useZiggyRouteStore();
const userContextStore = useUserContextStore();

const { t } = useI18n();
const router = useRouter();

const appName = import.meta.env.VITE_APP_NAME;
const userContext = computed(() => userContextStore.getUserContext);

const fullName = computed(() => {
  return userContext.value.profile.first_name + ' ' + userContext.value.profile.last_name;
});

const props = defineProps<{
  layout?: "side-menu" | "simple-menu";
}>();

const logout = () => {
  dashboardStore.toggleScreenMaskValue();

  axios.post('/logout').then(() => {
    sessionStorage.clear();
    window.location.href = '/';
  });
}

const toggleMenu = () => {
  dashboardStore.toggleLayoutValue();
}

const switchLanguage = (lang: "en" | "id"): void => {

}

const loading = ref<boolean>(false);

const showSlideover = ref(false);
const toggleSlideover = (value: boolean) => {
  showSlideover.value = value;
};

onMounted(async () => {
  loading.value = true;

  let userprofile = await dashboardService.readProfile();
  userContextStore.setUserContext(userprofile.data as UserProfile);

  let menuResult = await dashboardService.readUserMenu();
  sideMenuStore.setUserMenu(menuResult.data as Array<sMenu>);

  let apiResult = await dashboardService.readUserApi();
  ziggyRouteStore.setZiggy(apiResult.data as Config);

  loading.value = false;
})
</script>

<template>
  <div :class="[
    'h-[70px] md:h-[65px] z-[51] border-b border-white/[0.08] mt-12 md:mt-0 -mx-3 sm:-mx-8 md:-mx-0 px-3 md:border-b-0 relative md:fixed md:inset-x-0 md:top-0 sm:px-8 md:px-10 md:pt-10 md:bg-gradient-to-b md:from-slate-100 md:to-transparent dark:md:from-darkmode-700',
    'before:content-[\'\'] before:absolute before:h-[65px] before:inset-0 before:top-0 before:mx-7 before:bg-primary/30 before:mt-3 before:rounded-xl before:hidden before:md:block before:dark:bg-darkmode-600/30',
    'after:content-[\'\'] after:absolute after:inset-0 after:h-[65px] after:mx-3 after:bg-primary after:mt-5 after:rounded-xl after:shadow-md after:hidden after:md:block after:dark:bg-darkmode-600',
  ]">
    <div class="flex items-center h-full">
      <div :class="[
        '-intro-x hidden md:flex',
        props.layout == 'side-menu' && 'xl:w-[180px]',
        props.layout == 'simple-menu' && 'xl:w-auto',
      ]">
        <img alt="DCSLab" class="w-6" :src="logoUrl" @click="toggleMenu" />
        <span :class="[
          'ml-3 text-lg text-white',
          props.layout == 'side-menu' && 'hidden xl:block',
          props.layout == 'simple-menu' && 'hidden',
        ]">
          <RouterLink :to="{ name: 'side-menu-dashboard-maindashboard' }">
            {{ appName }}
          </RouterLink>
        </span>
      </div>

      <UserLocation />

      <Button as="a" class="mr-2 intro-x sm:mr-4" href="#" variant="primary"
        @click="(event: MouseEvent) => { event.preventDefault(); toggleSlideover(true); }">
        <Lucide icon="Archive" />
      </Button>

      <Slideover :open="showSlideover" @close="() => { toggleSlideover(false); }">
        <Slideover.Panel>
          <Slideover.Title class="p-5">
            <h2 class="mr-auto text-base font-medium">
              &nbsp;
            </h2>
          </Slideover.Title>
          <Slideover.Description>
            &nbsp;
          </Slideover.Description>
          <Slideover.Footer>
            <strong>{{ t('components.top-bar.slide_over.footer.copyright') }} &copy; {{ (new Date()).getFullYear() }}
              <a href="https://www.github.com/GitzJoey">{{ t('components.top-bar.slide_over.footer.copyright_name') }}</a>
              &nbsp;&amp;&nbsp;
              <a href="https://github.com/GitzJoey/DCSLab/graphs/contributors">{{
                t('components.top-bar.slide_over.footer.contributors') }}</a>.
            </strong>
            {{ t('components.top-bar.slide_over.footer.rights') }} <br /> {{
              t('components.top-bar.slide_over.footer.powered_by') }}
          </Slideover.Footer>
        </Slideover.Panel>
      </Slideover>

      <Menu class="mr-4 intro-x sm:mr-6">
        <Menu.Button :as="Button" variant="primary">
          <Lucide icon="Globe" />
        </Menu.Button>
        <Menu.Items class="w-48 h-24 overflow-y-auto" placement="bottom-end">
          <Menu.Item @click="switchLanguage('en')"><span class="text-primary">{{ t('components.top-bar.language.english')
          }}</span></Menu.Item>
          <Menu.Item @click="switchLanguage('id')"><span class="text-primary">{{
            t('components.top-bar.language.indonesia') }}</span></Menu.Item>
        </Menu.Items>
      </Menu>

      <Menu>
        <Menu.Button class="block w-8 h-8 overflow-hidden rounded-full shadow-lg image-fit zoom-in intro-x">
          <img v-if="!loading" alt="DCSLab" :src="defUserUrl" />
          <LoadingIcon v-else icon="puff" />
        </Menu.Button>
        <Menu.Items
          class="w-56 mt-px relative bg-primary/80 before:block before:absolute before:bg-black before:inset-0 before:rounded-md before:z-[-1] text-white">
          <Menu.Header class="font-normal">
            <div class="font-medium">{{ fullName }}</div>
            <div class="text-xs text-white/70 mt-0.5 dark:text-slate-500">
              {{ userContext.email }}
            </div>
          </Menu.Header>
          <Menu.Divider class="bg-white/[0.08]" />
          <Menu.Item class="hover:bg-white/5" @click="router.push({ name: 'side-menu-dashboard-profile' })">
            <Lucide icon="User" class="w-4 h-4 mr-2" /> {{ t('components.top-bar.profile_ddl.profile') }}
          </Menu.Item>
          <Menu.Item class="hover:bg-white/5">
            <Lucide icon="Mail" class="w-4 h-4 mr-2" /> {{ t('components.top-bar.profile_ddl.inbox') }}
          </Menu.Item>
          <Menu.Item class="hover:bg-white/5">
            <Lucide icon="Activity" class="w-4 h-4 mr-2" /> {{ t('components.top-bar.profile_ddl.activity') }}
          </Menu.Item>
          <Menu.Divider class="bg-white/[0.08]" />
          <Menu.Item class="hover:bg-white/5" @click="logout">
            <Lucide icon="ToggleRight" class="w-4 h-4 mr-2" /> {{ t('components.top-bar.profile_ddl.logout') }}
          </Menu.Item>
        </Menu.Items>
      </Menu>

    </div>
  </div>
</template>
