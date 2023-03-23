<script setup lang="ts">
import { onMounted, computed, ref } from "vue";
import Lucide from "../../base-components/Lucide";
import logoUrl from "../../assets/images/logo.svg";
import Breadcrumb from "../../base-components/Breadcrumb";
import { FormInput } from "../../base-components/Form";
import { Menu, Popover } from "../../base-components/Headless";
import defUserUrl from "../../assets/images/def-user.png";
import _ from "lodash";
import { TransitionRoot } from "@headlessui/vue";
import { useI18n } from "vue-i18n";
import axios from "../../axios";
import { useDashboardStore } from "../../stores/dashboard";
import { useUserContextStore } from "../../stores/user-context";
import { useRouter } from "vue-router";
import Button from "../../base-components/Button";
import DashboardService from '../../services/DashboardService';
import { UserProfileType } from '../../types/UserProfileType';
import LoadingIcon from "../../base-components/LoadingIcon";

const { t } = useI18n();
const router = useRouter();

const dashboardService = new DashboardService();

const dashboardStore = useDashboardStore();
const userContextStore = useUserContextStore();

const userContext = computed(() => userContextStore.getUserContext);

const props = defineProps<{
  layout?: "side-menu" | "simple-menu" | "top-menu";
}>();

const appName = import.meta.env.VITE_APP_NAME;

const logout = () => {
  dashboardStore.toggleScreenMaskValue();

  axios.post('/logout').then(response => {
    sessionStorage.clear();
    window.location.href = '/';
  }).catch((e) => {
  }).finally(() => {
    dashboardStore.toggleScreenMaskValue();
  });
}

const loading = ref(false);

onMounted(async () => {
  loading.value = true;

  let userprofile = await dashboardService.readProfile();
  userContextStore.setUserContext(userprofile.data as UserProfileType);

  loading.value = false;
})
</script>

<template>
  <div
    :class="[
      'h-[70px] md:h-[65px] z-[51] border-b border-white/[0.08] mt-12 md:mt-0 -mx-3 sm:-mx-8 md:-mx-0 px-3 md:border-b-0 relative md:fixed md:inset-x-0 md:top-0 sm:px-8 md:px-10 md:pt-10 md:bg-gradient-to-b md:from-slate-100 md:to-transparent dark:md:from-darkmode-700',
      props.layout == 'top-menu' && 'dark:md:from-darkmode-800',
      'before:content-[\'\'] before:absolute before:h-[65px] before:inset-0 before:top-0 before:mx-7 before:bg-primary/30 before:mt-3 before:rounded-xl before:hidden before:md:block before:dark:bg-darkmode-600/30',
      'after:content-[\'\'] after:absolute after:inset-0 after:h-[65px] after:mx-3 after:bg-primary after:mt-5 after:rounded-xl after:shadow-md after:hidden after:md:block after:dark:bg-darkmode-600',
    ]"
  >
    <div class="flex items-center h-full">
      <RouterLink
        :to="{ name: 'side-menu-dashboard-maindashboard' }"
        :class="[
          '-intro-x hidden md:flex',
          props.layout == 'side-menu' && 'xl:w-[180px]',
          props.layout == 'simple-menu' && 'xl:w-auto',
          props.layout == 'top-menu' && 'w-auto',
        ]"
      >
        <img
          alt="DCSLab"
          class="w-6"
          :src="logoUrl"
        />
        <span
          :class="[
            'ml-3 text-lg text-white',
            props.layout == 'side-menu' && 'hidden xl:block',
            props.layout == 'simple-menu' && 'hidden',
          ]"
        >
          {{ appName }}
        </span>
      </RouterLink>

      <Breadcrumb
        light
        :class="[
          'h-[45px] md:ml-10 md:border-l border-white/[0.08] dark:border-white/[0.08] mr-auto -intro-x',
          props.layout != 'top-menu' && 'md:pl-6',
          props.layout == 'top-menu' && 'md:pl-10',
        ]"
      >
        <Breadcrumb.Text>
          <Menu>
            <Menu.Button :as="Button" variant="primary">
              <Lucide icon="Umbrella" />
            </Menu.Button>
            <Menu.Items class="w-96 h-96 overflow-y-auto" placement="bottom-start">
              <Menu.Item><span class="text-primary">PT ABC - Cabang Pusat</span></Menu.Item>
            </Menu.Items>
          </Menu>
        </Breadcrumb.Text>
        <Breadcrumb.Text>PT ABC - Cabang Pusat</Breadcrumb.Text>
      </Breadcrumb>

      <Menu>
        <Menu.Button
          class="block w-8 h-8 overflow-hidden rounded-full shadow-lg image-fit zoom-in intro-x"
        >
          <img
            alt="Midone Tailwind HTML Admin Template"
            :src="defUserUrl"
            v-if="!loading"
          />
          <LoadingIcon icon="puff" v-else />
        </Menu.Button>
        <Menu.Items
          class="w-56 mt-px relative bg-primary/80 before:block before:absolute before:bg-black before:inset-0 before:rounded-md before:z-[-1] text-white"
        >
          <Menu.Header class="font-normal">
            <div class="font-medium">{{ userContext.profile.fullName }}</div>
            <div class="text-xs text-white/70 mt-0.5 dark:text-slate-500">
              {{ '' }}
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