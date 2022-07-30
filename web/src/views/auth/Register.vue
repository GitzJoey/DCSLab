<template>
  <div>
    <DarkModeSwitcher />
    <div class="container sm:px-10">
      <div class="block xl:grid grid-cols-2 gap-4">
        <div class="hidden xl:flex flex-col min-h-screen">
          <a href="/" class="-intro-x flex items-center pt-5">
            <img alt="DCSLab" class="w-10" src="@/assets/images/stealth-bomber-color.svg" />
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
            <div class="intro-x box" v-show="loading">
              <LoadingIcon icon="puff" />
            </div>
            <div v-show="!loading">
              <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">
                {{ t('views.register.title') }}
              </h2>
              <div class="intro-x mt-2 text-slate-400 dark:text-slate-400 xl:hidden text-center">
              </div>
              <VeeForm id="registerForm" @submit="onSubmit" v-slot="{ errors }">
                <div class="intro-x mt-8">
                  <VeeField id="name" type="text" name="name" class="intro-x login__input form-control py-3 px-4 block" rules="required|alpha_num|min:2" :label="t('views.register.fields.name')" :placeholder="t('views.register.fields.name')" />
                  <ErrorMessage name="name" class="text-danger" />
                  <VeeField id="email" type="text" name="email" class="intro-x login__input form-control py-3 px-4 block mt-4" rules="required|email" :label="t('views.register.fields.email')" :placeholder="t('views.register.fields.email')" />
                  <ErrorMessage name="email" class="text-danger" />
                  <VeeField id="password" type="password" name="password" class="intro-x login__input form-control py-3 px-4 block mt-4" rules="required" :label="t('views.register.fields.password')" :placeholder="t('views.register.fields.password')" />
                  <ErrorMessage name="password" class="text-danger" />
                  <VeeField id="password_confirmation" type="password" name="password_confirmation" class="intro-x login__input form-control py-3 px-4 block mt-4" rules="required|confirmed:@password" :label="t('views.register.fields.password_confirmation')" :placeholder="t('views.register.fields.password_confirmation')" />
                  <ErrorMessage name="password_confirmation" class="text-danger" />
                </div>
                <div class="intro-x flex items-center text-slate-600 dark:text-slate-500 mt-4 text-xs sm:text-sm">
                  <VeeField id="terms" type="checkbox" name="terms" class="form-check-input border mr-2" value="on" rules="required" :label="t('views.register.fields.terms')" />
                  <label class="cursor-pointer select-none" for="terms">{{ t('views.register.fields.agree_1') }} {{ t('views.register.fields.agree_2') }}</label>
                </div>
                <ErrorMessage name="terms" class="text-danger" />
                <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                  <button type="submit" class="btn btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3 align-top">
                    {{ t('components.buttons.register') }}
                  </button>
                  <button type="button" class="btn btn-outline-secondary py-3 px-4 w-full xl:w-32 mt-3 xl:mt-0 align-top" @click="router.push({ name: 'login' })">
                    {{ t('components.buttons.login') }}
                  </button>
                </div>
              </VeeForm>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
//#region Vue Import
import { onMounted, ref } from "vue";
import DarkModeSwitcher from "@/components/dark-mode-switcher/Main.vue";
import dom from "@left4code/tw-starter/dist/js/dom";
import { useI18n } from "vue-i18n";
import router from "@/router";
import { authAxiosInstance } from "@/axios";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - UI
const appName = import.meta.env.VITE_APP_NAME;
const loading = ref(false);
//#endregion

//#region onMounted
onMounted(() => {
  dom("body").removeClass("main").removeClass("error-page").addClass("login");
});
//#endregion

//#region Methods
const onSubmit = async (values, actions) => {
  loading.value = true;
  var formData = new FormData(dom('#registerForm')[0]);

  await authAxiosInstance.get('/sanctum/csrf-cookie');

  authAxiosInstance.post('register', formData).then(response => {
    router.push({ name: 'side-menu-dashboard-maindashboard' });
  }).catch(e => {
    handleError(e, actions);
  }).finally(() => {
    loading.value = false;
  });
}

const handleError = (e, actions) => {
    //Laravel Validations
    if (e.response.data.errors !== undefined && Object.keys(e.response.data.errors).length > 0) {
        for (var key in e.response.data.errors) {
            for (var i = 0; i < e.response.data.errors[key].length; i++) {
                actions.setFieldError(key, e.response.data.errors[key][i]);
            }
        }
    } else {
      actions.setFieldError('email', e.response.status + ' ' + e.response.statusText +': ' + e.response.data.message);
    }
}
//#endregion
</script>
