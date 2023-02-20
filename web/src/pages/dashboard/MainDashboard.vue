<script setup lang="ts">
import { onMounted } from 'vue';
import { useDashboardStore } from '../../stores/dashboard';
import { useUserContextStore } from '../../stores/user-context';
import { UserProfileType } from '../../types/UserProfileType';
import DashboardService from '../../services/DashboardService';

const dashboardStore = useDashboardStore();
const userContextStore = useUserContextStore();

const dashboardService = new DashboardService();

onMounted(async () => {
  let profile = await dashboardService.readProfile();
  let menu = await dashboardService.readUserMenu();
  let api = await dashboardService.readUserApi();


});

</script>

<template>
  <div class="flex items-center mt-8 intro-y">
    <h2 class="mr-auto text-lg font-medium">Welcome, </h2>
  </div>
  <div class="p-5 mt-5 intro-y box">Example page 1</div>
</template>
