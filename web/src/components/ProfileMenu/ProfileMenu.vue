<script setup lang="ts">
  import { toRef, onMounted, computed } from 'vue';
  import { Menu } from '../Base/Headless';
  import { useUserContextStore } from '../../stores/user-context';
  import defaultProfilePic from '@/assets/images/def-user.png';
  import ProfileService from '@/services/ProfileService';
  import { UserProfile } from '@/types/models/UserProfile';
  import { useI18n } from 'vue-i18n';
  import Lucide from '@/components/Base/Lucide';
  import { useDashboardStore } from '../../stores/dashboard';
  import axios from '../../axios';
  import { useRouter } from 'vue-router';

  const { t } = useI18n();
  const router = useRouter();

  interface ProfileMenuProps {
    visible: boolean;
    theme?: 'rubick' | 'icewall' | 'enigma' | 'tinker';
    layout?: 'side-menu' | 'simple-menu' | 'top-menu';
  }

  const props = withDefaults(defineProps<ProfileMenuProps>(), {
    visible: true,
    theme: 'rubick',
    layout: 'side-menu',
  });

  const profileServices = new ProfileService();

  const visible = toRef(props, 'visible');

  const userContextStore = useUserContextStore();
  const dashboardStore = useDashboardStore();

  const userContext = computed(() => userContextStore.userContext);

  const profilePicture = computed(() => {
    let defaultPic = defaultProfilePic;

    return defaultPic;
  });

  const profilePictureAlt = computed(() => {
    let defaultPicAlt = 'DCSLab';

    return defaultPicAlt;
  });

  onMounted(async () => {
    let userprofile = await profileServices.readProfile();
    if (userprofile.success) {
      userContextStore.setUserContext(userprofile.data as UserProfile);
    }
  });

  const logout = () => {
    dashboardStore.toggleScreenMaskValue();

    axios.post('/logout').then(() => {
      sessionStorage.clear();
      localStorage.removeItem('selectedUserLocation');
      window.location.href = '/';
    });
  };
</script>

<template>
  <Menu v-if="visible">
    <Menu.Button class="block w-8 h-8 overflow-hidden rounded-full shadow-lg image-fit zoom-in intro-x">
      <img :alt="profilePictureAlt" :src="profilePicture" />
    </Menu.Button>
    <Menu.Items
      class="w-56 mt-px relative bg-primary/80 before:block before:absolute before:bg-black before:inset-0 before:rounded-md before:z-[-1] text-white"
    >
      <Menu.Header class="font-normal">
        <div class="font-medium">{{ userContext.name }}</div>
        <div class="text-xs text-white/70 mt-0.5 dark:text-slate-500">
          {{ userContext.email }}
        </div>
      </Menu.Header>
      <Menu.Divider class="bg-white/[0.08]" />
      <Menu.Item class="hover:bg-white/5" @click="router.push({ name: 'side-menu-dashboard-profile' })">
        <Lucide icon="User" class="w-4 h-4 mr-2" />
        {{ t('components.profile-menu.profile_ddl.profile') }}
      </Menu.Item>
      <Menu.Item class="hover:bg-white/5">
        <Lucide icon="Mail" class="w-4 h-4 mr-2" />
        {{ t('components.profile-menu.profile_ddl.inbox') }}
      </Menu.Item>
      <Menu.Item class="hover:bg-white/5">
        <Lucide icon="Activity" class="w-4 h-4 mr-2" />
        {{ t('components.profile-menu.profile_ddl.activity') }}
      </Menu.Item>
      <Menu.Divider class="bg-white/[0.08]" />
      <Menu.Item class="hover:bg-white/5" @click="logout">
        <Lucide icon="ToggleRight" class="w-4 h-4 mr-2" />
        {{ t('components.profile-menu.profile_ddl.logout') }}
      </Menu.Item>
    </Menu.Items>
  </Menu>
</template>
