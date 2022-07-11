<template>
  <div>
    <DarkModeSwitcher />
    <div class="container sm:px-10">
      <div class="block xl:grid grid-cols-2 gap-4">
        <div class="hidden xl:flex flex-col min-h-screen">
          <a href="" class="-intro-x flex items-center pt-5">
            <img alt="DCSLab" class="w-6" src="@/assets/images/logo.svg" />
            <span class="text-white text-lg ml-3"> {{ appName }} </span>
          </a>
          <div class="my-auto">
            <img alt="" class="-intro-x w-1/2 -mt-16" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVQIW2NgAAIAAAUAAR4f7BQAAAAASUVORK5CYII=" />
            <div class="-intro-x text-white font-medium text-4xl leading-tight mt-10">
            </div>
            <div class="-intro-x mt-5 text-lg text-white text-opacity-70 dark:text-slate-400">
            </div>
          </div>
        </div>
        <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
          <div class="my-auto mx-auto xl:ml-20 bg-white dark:bg-darkmode-600 xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
            <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">
              {{ t('views.register.title') }}
            </h2>
            <div class="intro-x mt-2 text-slate-400 dark:text-slate-400 xl:hidden text-center">
            </div>
            <VeeForm id="registerForm" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
              <div class="intro-x mt-8">
                <input type="text" class="intro-x login__input form-control py-3 px-4 block" placeholder="Name" />
                <input type="text" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Email" />
                <input type="text" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Password" />
                <input type="text" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Password Confirmation" />
              </div>
              <div class="intro-x flex items-center text-slate-600 dark:text-slate-500 mt-4 text-xs sm:text-sm">
                <input id="remember-me" type="checkbox" class="form-check-input border mr-2" />
                <label class="cursor-pointer select-none" for="terms">{{ t('views.register.fields.agree_1') }} {{ t('views.register.fields.agree_2') }}</label>
              </div>
              <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                <button class="btn btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3 align-top">
                  {{ t('components.buttons.register') }}
                </button>
                <button class="btn btn-outline-secondary py-3 px-4 w-full xl:w-32 mt-3 xl:mt-0 align-top" @click="router.push({ name: 'login' })">
                  {{ t('components.buttons.login') }}
                </button>
              </div>
            </VeeForm>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
//#region Vue Import
import { onMounted } from "vue";
import DarkModeSwitcher from "@/components/dark-mode-switcher/Main.vue";
import dom from "@left4code/tw-starter/dist/js/dom";
import { useI18n } from "vue-i18n";
import router from "@/router";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - UI
const appName = import.meta.env.VITE_APP_NAME;
//#endregion

onMounted(() => {
  dom("body").removeClass("main").removeClass("error-page").addClass("login");
});
</script>
