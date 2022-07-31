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
                {{ t('views.reset_password.email.title') }}
              </h2>
              <div class="intro-x mt-2 text-slate-400 xl:hidden text-center">
              </div>
              <template v-if="mode === 'email'">
                <VeeForm id="resetPasswordForm" @submit="onSubmit" v-slot="{ errors }">
                  <div class="intro-x mt-8">
                    <VeeField id="email" type="text" name="email" :class="{'intro-x login__input form-control py-3 px-4 block':true, 'border-danger':errors['email']}" rules="required|email" :label="t('views.reset_password.email.fields.email')" :placeholder="t('views.reset_password.email.fields.email')" autofocus />
                    <ErrorMessage name="email" class="text-danger" />
                  </div>
                  <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                    <button type="submit" class="btn btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3 align-top">
                      {{ t('components.buttons.submit') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary py-3 px-4 w-full xl:w-32 mt-3 xl:mt-0 align-top" @click="router.push({ name: 'login' })">
                      {{ t('components.buttons.login') }}
                    </button>
                  </div>
                </VeeForm>
              </template>
              <template v-else-if="mode === 'email_confirmation'">
                <div class="intro-x mt-8">Please check you email</div>
                <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                  <button type="button" class="btn btn-outline-secondary py-3 px-4 w-full xl:w-32 mt-3 xl:mt-0 align-top" @click="router.push({ name: 'login' })">
                    {{ t('components.buttons.login') }}
                  </button>
                </div>
              </template>
              <template v-else>
                <VeeForm id="updatePasswordForm" @submit="onSubmit" v-slot="{ errors }">
                  <div class="intro-x mt-8">
                    <VeeField id="password" type="password" name="password" :class="{'intro-x login__input form-control py-3 px-4 block mt-4':true, 'border-danger':errors['password']}" rules="required" :label="t('views.login.fields.password')" :placeholder="t('views.login.fields.password')"/>
                    <ErrorMessage name="password" class="text-danger" />
                    <VeeField id="password_confirmation" type="password" name="password_confirmation" class="intro-x login__input form-control py-3 px-4 block mt-4" rules="required|confirmed:@password" :label="t('views.register.fields.password_confirmation')" :placeholder="t('views.register.fields.password_confirmation')" />
                    <ErrorMessage name="password_confirmation" class="text-danger" />
                  </div>
                  <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                    <button type="submit" class="btn btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3 align-top">
                      {{ t('components.buttons.reset') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary py-3 px-4 w-full xl:w-32 mt-3 xl:mt-0 align-top" @click="router.push({ name: 'login' })">
                      {{ t('components.buttons.login') }}
                    </button>
                  </div>
                </VeeForm>
              </template>
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
import { authAxiosInstance } from "@/axios";
import { useI18n } from "vue-i18n";
import router from "@/router";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - UI
const appName = import.meta.env.VITE_APP_NAME;
const loading = ref(false);
const mode = ref('email');
const reset = ref({
  email: '',
  token: ''
})
//#endregion

//#region onMounted
onMounted( async () => {
  dom("body").removeClass("main").removeClass("error-page").addClass("login");

  let token = router.currentRoute.value.params.token;
  let email = router.currentRoute.value.query.email;

  if (token.length !== 0) {
    mode.value = 'reset';
    reset.value.email = email;
    reset.value.token = token;
  }
});
//#endregion

//#region Methods
const onSubmit = async (values, actions) => {
  loading.value = true;
  var formData = new FormData(dom('#resetPasswordForm')[0]);

  await authAxiosInstance.get('/sanctum/csrf-cookie');

  authAxiosInstance.post('http://localhost:8000/forgot-password', formData).then(response => {
    mode.value = 'email_confirmation';    
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
