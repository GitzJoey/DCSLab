<template>
  <div>
    <DarkModeSwitcher />
    <div class="container">
      <div class="error-page flex flex-col lg:flex-row items-center justify-center h-screen text-center lg:text-left">
        <div class="-intro-x lg:mr-20">
          <img alt="DCSLab" class="h-48 lg:h-auto" src="@/assets/images/error-illustration.svg" />
        </div>
        <div class="text-white mt-10 lg:mt-0">
          <div class="intro-x text-8xl font-medium">{{ title }}</div>
          <div class="intro-x text-xl lg:text-3xl font-medium mt-5">
            {{ description }}
          </div>
          <div class="intro-x text-lg mt-3">
            {{ resolution }}
          </div>
          <button @click="router.push({ name: 'login' })" class="intro-x btn py-3 px-4 text-white border-white dark:border-darkmode-400 dark:text-slate-200 mt-10">
            Home
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import router from "@/router";
import DarkModeSwitcher from "@/components/dark-mode-switcher/Main.vue";
import dom from "@left4code/tw-starter/dist/js/dom";

const { t } = useI18n();

const title = ref('Error');
const description = ref('');
const resolution = ref('');

onMounted(() => {
  dom("body").removeClass("main").removeClass("login").addClass("error-page");

  let code = router.currentRoute.value.params.code;
console.log(router.currentRoute.value);
  switch (code) {
    case '401':
      title.value = 'Unauthorized';
      description.value = 'Invalid Credentials';
      break;
    case '404':
    default:
      title.value = 'Error';
      description.value = 'Not Found';
      break;
  }
});
</script>
