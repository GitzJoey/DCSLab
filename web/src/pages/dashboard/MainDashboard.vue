<script setup lang="ts">
import { onMounted } from 'vue';
import { useDashboardStore } from '../../stores/dashboard';
import { useUserContextStore } from '../../stores/user-context';

const dashboardStore = useDashboardStore();
const userContextStore = useUserContextStore();

onMounted(() => {

  
});

</script>

<template>
  <div class="flex items-center mt-8 intro-y">
    <h2 class="mr-auto text-lg font-medium">Welcome, </h2>
  </div>
  <div class="p-5 mt-5 intro-y box">Example page 1</div>
</template>
