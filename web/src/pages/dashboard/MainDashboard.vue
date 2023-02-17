<script setup lang="ts">
import { onMounted } from 'vue';
import { useDashboardStore } from '../../stores/dashboard';
import { useUserContextStore } from '../../stores/user-context';
import DashboardService from '../../services/DashboardService';

const dashboardStore = useDashboardStore();
const userContextStore = useUserContextStore();

const dashboardService = new DashboardService();

onMounted(async () => {
  let result = await dashboardService.readProfile();
  console.log(result);
});

</script>

<template>
  <div class="flex items-center mt-8 intro-y">
    <h2 class="mr-auto text-lg font-medium">Welcome, </h2>
  </div>
  <div class="p-5 mt-5 intro-y box">Example page 1</div>
</template>
