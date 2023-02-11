<script setup lang="ts">
import { useDarkModeStore } from "../../stores/dark-mode";
import { useColorSchemeStore, ColorSchemes } from "../../stores/color-scheme";
import { toRef, computed } from "vue";

const colorScheme = computed(() => useColorSchemeStore().colorScheme);
const darkMode = computed(() => useDarkModeStore().darkMode);

const setColorSchemeClass = () => {
  const el = document.querySelectorAll("html")[0];
  el.setAttribute("class", colorScheme.value);
  darkMode.value && el.classList.add("dark");
};

const switchColorScheme = (colorScheme: ColorSchemes) => {
  useColorSchemeStore().setColorScheme(colorScheme);
  setColorSchemeClass();
};

const props = defineProps({
    visible: {type: Boolean, default: false},
});

const visible = toRef(props, 'visible');

setColorSchemeClass();
</script>

<template>
  <!-- BEGIN: Main Color Switcher -->
  <div
    class="fixed bottom-0 right-0 z-50 flex items-center justify-center h-12 px-5 mb-10 border rounded-full shadow-md box mr-52"
    v-if="visible"
  >
    <div class="hidden mr-4 sm:block text-slate-600 dark:text-slate-200">
      Color Scheme
    </div>
    <a
      @click="switchColorScheme('default')"
      :class="[
        'block w-8 h-8 cursor-pointer bg-cyan-900 rounded-full border-4 mr-1 hover:border-slate-200',
        {
          'border-slate-300 dark:border-darkmode-800/80':
            colorScheme == 'default',
        },
        { 'border-white dark:border-darkmode-600': colorScheme != 'default' },
      ]"
    ></a>
    <a
      @click="switchColorScheme('theme-1')"
      :class="[
        'block w-8 h-8 cursor-pointer bg-blue-800 rounded-full border-4 mr-1 hover:border-slate-200',
        {
          'border-slate-300 dark:border-darkmode-800/80':
            colorScheme == 'theme-1',
        },
        { 'border-white dark:border-darkmode-600': colorScheme != 'theme-1' },
      ]"
    ></a>
    <a
      @click="switchColorScheme('theme-2')"
      :class="[
        'block w-8 h-8 cursor-pointer bg-blue-900 rounded-full border-4 mr-1 hover:border-slate-200',
        {
          'border-slate-300 dark:border-darkmode-800/80':
            colorScheme == 'theme-2',
        },
        { 'border-white dark:border-darkmode-600': colorScheme != 'theme-2' },
      ]"
    ></a>
    <a
      @click="switchColorScheme('theme-3')"
      :class="[
        'block w-8 h-8 cursor-pointer bg-emerald-900 rounded-full border-4 mr-1 hover:border-slate-200',
        {
          'border-slate-300 dark:border-darkmode-800/80':
            colorScheme == 'theme-3',
        },
        { 'border-white dark:border-darkmode-600': colorScheme != 'theme-3' },
      ]"
    ></a>
    <a
      @click="switchColorScheme('theme-4')"
      :class="[
        'block w-8 h-8 cursor-pointer bg-indigo-900 rounded-full border-4 hover:border-slate-200',
        {
          'border-slate-300 dark:border-darkmode-800/80':
            colorScheme == 'theme-4',
        },
        { 'border-white dark:border-darkmode-600': colorScheme != 'theme-4' },
      ]"
    ></a>
  </div>
  <!-- END: Main Color Switcher -->
</template>
