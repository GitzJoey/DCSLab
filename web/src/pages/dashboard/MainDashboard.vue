<script setup lang="ts">
import { computed } from 'vue';
import { useUserContextStore } from '../../stores/user-context';

const userContextStore = useUserContextStore();
const userContext = computed(() => userContextStore.userContext);

const welcomeName = computed(() => userContext.value.profile.first_name);
</script>

<template>
  <div class="flex items-center mt-8 intro-y">
    <h2 class="mr-auto text-lg font-medium">Welcome, {{ welcomeName }}</h2>
  </div>
</template>
