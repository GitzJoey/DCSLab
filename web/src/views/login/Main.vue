<template>
  <div>
    <DarkModeSwitcher />
    <div class="container sm:px-10">
      <div class="block xl:grid grid-cols-2 gap-4">
        <div class="hidden xl:flex flex-col min-h-screen">
          <a href="/" class="-intro-x flex items-center pt-5">
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
              {{ t('views.login.title') }}
            </h2>
            <div class="intro-x mt-2 text-slate-400 xl:hidden text-center">
            </div>
            <VeeForm id="loginForm" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
              <div class="intro-x mt-8">
                <VeeField id="email" type="text" name="email" class="intro-x login__input form-control py-3 px-4 block" rules="required|email" :label="t('views.login.fields.email')" :placeholder="t('views.login.fields.email')" autofocus />
                <ErrorMessage name="email" class="text-danger" />
                <VeeField id="password" type="password" name="password" class="intro-x login__input form-control py-3 px-4 block mt-4" rules="required" :label="t('views.login.fields.password')" :placeholder="t('views.login.fields.password')"/>
                <ErrorMessage name="password" class="text-danger" />
              </div>
              <div class="intro-x flex text-slate-600 dark:text-slate-500 text-xs sm:text-sm mt-4">
                <div class="flex items-center mr-auto">
                  <input id="remember-me" type="checkbox" class="form-check-input border mr-2" />
                  <label class="cursor-pointer select-none" for="remember-me">{{ t('views.login.fields.remember_me') }}</label>
                </div>
                <a href="">{{ t('views.login.fields.forgot_pass') }}</a>
              </div>
              <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                <button type="submit" class="btn btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3 align-top">
                  {{ t('components.buttons.login') }}
                </button>
                <button type="button" class="btn btn-outline-secondary py-3 px-4 w-full xl:w-32 mt-3 xl:mt-0 align-top" @click="router.push({ name: 'register' })">
                  {{ t('components.buttons.register') }}
                </button>
              </div>
            </VeeForm>
            <div class="intro-x mt-10 xl:mt-24 text-slate-600 dark:text-slate-500 text-center xl:text-left">
              <a class="text-primary dark:text-slate-200" href="">{{ t('views.login.fields.terms_and_cond') }}</a>
              -
              <a class="text-primary dark:text-slate-200" href="">{{ t('views.login.fields.privacy_policy') }}</a>
            </div>
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
import { authAxiosInstance } from "@/axios";
import { useI18n } from "vue-i18n";
import router from "@/router";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - UI
const appName = import.meta.env.VITE_APP_NAME;
//#endregion

//#region onMounted
onMounted( async () => {
  dom("body").removeClass("main").removeClass("error-page").addClass("login");
});
//#endregion

//#region Methods
const onSubmit = async (values, actions) => {
  var formData = new FormData(dom('#loginForm')[0]);

  router.push({ name: 'side-menu-dashboard-maindashboard' });
  await authAxiosInstance.get('/sanctum/csrf-cookie');

  authAxiosInstance.post('login', formData).then(response => {
    router.push({ name: 'side-menu-dashboard-maindashboard' });
  }).catch(e => {
    handleError(e);
  });
}

const invalidSubmit = (e) => {
}

const handleError = (e, actions) => {
}
//#endregion
</script>
