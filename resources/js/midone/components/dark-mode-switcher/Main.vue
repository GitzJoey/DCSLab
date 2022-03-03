<template>
  <div class="dark-mode-switcher cursor-pointer shadow-md fixed bottom-0 right-0 box border rounded-full w-40 h-12 flex items-center justify-center z-50 mb-10 mr-10" @click="switchMode" v-if="visible">
    <div class="mr-4 text-slate-600 dark:text-slate-200">Dark Mode</div>
    <div :class="{ 'dark-mode-switcher__toggle--active': darkMode }" class="dark-mode-switcher__toggle border"></div>
  </div>
</template>

<script setup>
import { toRef, computed } from "vue";
import { useDarkModeStore } from "@/stores/dark-mode";
import dom from "@left4code/tw-starter/dist/js/dom";

const darkModeStore = useDarkModeStore();
const darkMode = computed(() => darkModeStore.darkMode);

const props = defineProps({
    visible: {type: Boolean, default: false},
});

const visible = toRef(props, 'visible');

const setDarkModeClass = () => {
  darkMode.value
    ? dom("html").addClass("dark")
    : dom("html").removeClass("dark");
};

const switchMode = () => {
  darkModeStore.setDarkMode(!darkMode.value);
  setDarkModeClass();
};

setDarkModeClass();
</script>
