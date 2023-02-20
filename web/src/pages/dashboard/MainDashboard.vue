<script setup lang="ts">
import { onMounted } from 'vue';
import { useDashboardStore } from '../../stores/dashboard';
import { useUserContextStore } from '../../stores/user-context';
import { useSideMenuStore, Menu } from '../../stores/side-menu';
import { useZiggyRouteStore } from '../../stores/ziggy-route';
import { UserProfileType } from '../../types/UserProfileType';
import DashboardService from '../../services/DashboardService';
import { Config } from "ziggy-js";

const dashboardStore = useDashboardStore();
const userContextStore = useUserContextStore();
const sideMenuStore = useSideMenuStore();
const ziggyRouteStore = useZiggyRouteStore();
const dashboardService = new DashboardService();

onMounted(async () => {
  let userprofile = await dashboardService.readProfile();
  userContextStore.setUserContext(userprofile.data as UserProfileType);

  let menu = await dashboardService.readUserMenu();
  sideMenuStore.setUserMenu(menu as Array<Menu>);

  let api = await dashboardService.readUserApi();
  ziggyRouteStore.setZiggy(api as Config)

});

</script>

<template>
  <div class="flex items-center mt-8 intro-y">
    <h2 class="mr-auto text-lg font-medium">Welcome, </h2>
  </div>
  <div class="p-5 mt-5 intro-y box">Example page 1</div>
</template>
