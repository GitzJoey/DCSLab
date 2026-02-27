<script setup lang="ts">
import { useThemeStore, getTheme } from "@/stores/theme";
import ThemeSwitcher from "@/components/ThemeSwitcher";
import { onMounted, computed } from "vue";

const themeStore = useThemeStore();
const Component = computed(() => getTheme(themeStore.theme).component);
</script>

<template>
  <div>
    <ThemeSwitcher />
    <Component />
  </div>
</template>
