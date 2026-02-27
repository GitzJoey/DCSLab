<script setup lang="ts">
import { ref, computed } from "vue";
import logoUrl from "@/assets/images/logo.svg";

interface LanguageSwitcherProps {
  visible: boolean;
  theme?: "rubick" | "icewall" | "enigma" | "tinker";
  layout?: "side-menu" | "simple-menu" | "top-menu";
}

const props = withDefaults(defineProps<LanguageSwitcherProps>(), {
  visible: true,
  theme: "rubick",
  layout: "side-menu",
});

const appName = import.meta.env.VITE_APP_NAME;

const toUrl = ref<string>("side-menu-dashboard-maindashboard");
</script>

<template>
  <RouterLink
    :to="{ name: toUrl }"
    :class="[
      '-intro-x hidden md:flex',
      props.layout == 'side-menu' && 'xl:w-[180px]',
      props.layout == 'simple-menu' && 'xl:w-auto',
      props.layout == 'top-menu' && 'w-auto',
    ]"
  >
    <img alt="DCSLab" class="w-6" :src="logoUrl" />
    <span :class="['ml-3 text-lg text-white', props.layout == 'side-menu' && 'hidden xl:block', props.layout == 'simple-menu' && 'hidden']">
      {{ appName }}
    </span>
  </RouterLink>
</template>
