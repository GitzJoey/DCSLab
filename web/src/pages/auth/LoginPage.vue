<script setup lang="ts">
import { ref } from "vue";
import DarkModeSwitcher from "../../components/DarkModeSwitcher";
import MainColorSwitcher from "../../components/MainColorSwitcher";
import logoUrl from "../../assets/images/logo.svg";
import illustrationUrl from "../../assets/images/illustration.svg";
import { FormInput, FormCheck } from "../../base-components/Form";
import Button from "../../base-components/Button";
import { useI18n } from "vue-i18n";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import AuthService from "../../services/AuthServices";
import { useRouter } from "vue-router";
import { LoginRequest } from "../../types/requests/AuthRequests";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { UserProfile } from "../../types/models/UserProfile";
import { FormActions } from "vee-validate";

const { t } = useI18n();
const router = useRouter();

const authService = new AuthService();

const appName = import.meta.env.VITE_APP_NAME;
const loading = ref<boolean>(false);

const loginForm = ref<LoginRequest>({
  email: "",
  password: "",
  remember: false
});

const onSubmit = async (values: LoginRequest, actions: FormActions<LoginRequest>) => {
  loading.value = true;

  let result: ServiceResponse<UserProfile | null> = await authService.doLogin(values);

  if (result.success) {
    router.push({ name: "side-menu-dashboard-maindashboard" });
  } else {
    actions.setErrors({ email: 'Invalid email and password' });
  }

  loading.value = false;
};
</script>

<template>
  <div :class="[
    '-m-3 sm:-mx-8 p-3 sm:px-8 relative h-screen lg:overflow-hidden bg-primary xl:bg-white dark:bg-darkmode-800 xl:dark:bg-darkmode-600',
    'before:hidden before:xl:block before:content-[\'\'] before:w-[57%] before:-mt-[28%] before:-mb-[16%] before:-ml-[13%] before:absolute before:inset-y-0 before:left-0 before:transform before:rotate-[-4.5deg] before:bg-primary/20 before:rounded-[100%] before:dark:bg-darkmode-400',
    'after:hidden after:xl:block after:content-[\'\'] after:w-[57%] after:-mt-[20%] after:-mb-[13%] after:-ml-[13%] after:absolute after:inset-y-0 after:left-0 after:transform after:rotate-[-4.5deg] after:bg-primary after:rounded-[100%] after:dark:bg-darkmode-700',
  ]">
    <DarkModeSwitcher />
    <MainColorSwitcher />
    <div class="container relative z-10 sm:px-10">
      <div class="block grid-cols-2 gap-4 xl:grid">
        <div class="flex-col hidden min-h-screen xl:flex">
          <a href="" class="flex items-center pt-5 -intro-x">
            <img alt="DCSLab" class="w-6" :src="logoUrl" />
            <span class="ml-3 text-lg text-white"> {{ appName }} </span>
          </a>
          <div class="my-auto">
            <img alt="DCSLab" class="w-1/2 -mt-16 -intro-x" :src="illustrationUrl" />
            <div class="mt-10 text-4xl font-medium leading-tight text-white -intro-x">
              <span class="hidden">&nbsp;</span><br />
              <span class="hidden">&nbsp;</span>
            </div>
            <div class="mt-5 text-lg text-white -intro-x text-opacity-70 dark:text-slate-400">
              <span class="hidden">&nbsp;</span>
            </div>
          </div>
        </div>
        <div class="flex h-screen py-5 my-10 xl:h-auto xl:py-0 xl:my-0">
          <div
            class="w-full px-5 py-8 mx-auto my-auto bg-white rounded-md shadow-md xl:ml-20 dark:bg-darkmode-600 xl:bg-transparent sm:px-8 xl:p-0 xl:shadow-none sm:w-3/4 lg:w-2/4 xl:w-auto">
            <LoadingOverlay :visible="loading" :transparent="true">
              <h2 class="text-2xl font-bold text-center intro-x xl:text-3xl xl:text-left">
                {{ t("views.login.title") }}
              </h2>
              <div class="mt-2 text-center intro-x text-slate-400 xl:hidden">
                &nbsp;
              </div>
              <VeeForm id="loginForm" v-slot="{ errors }" @submit="onSubmit">
                <div class="mt-8 intro-x">
                  <VeeField v-slot="{ field }" name="email" rules="required|email" :label="t('views.login.fields.email')">
                    <FormInput v-model="loginForm.email" type="text" name="email"
                      class="block px-4 py-3 intro-x login__input min-w-full xl:min-w-[350px]"
                      :class="{ 'border-danger': errors['email'] }" :placeholder="t('views.login.fields.email')"
                      v-bind="field" />
                  </VeeField>
                  <VeeErrorMessage name="email" as="span" class="text-danger" />
                  <VeeField v-slot="{ field }" name="password" rules="required" :label="t('views.login.fields.password')">
                    <FormInput v-model="loginForm.password" type="password" name="password"
                      class="block px-4 py-3 mt-4 intro-x login__input min-w-full xl:min-w-[350px]"
                      :class="{ 'border-danger': errors['password'] }" :placeholder="t('views.login.fields.password')"
                      v-bind="field" />
                  </VeeField>
                  <VeeErrorMessage name="password" as="span" class="text-danger" />
                </div>
                <div class="flex mt-4 text-xs intro-x text-slate-600 dark:text-slate-500 sm:text-sm">
                  <div class="flex items-center mr-auto">
                    <VeeField v-slot="{ field }" name="remember">
                      <FormCheck.Input id="remember-me" v-model="loginForm.remember" name="remember-me" type="checkbox"
                        class="mr-2 border" v-bind="field" />
                      <label class="cursor-pointer select-none" htmlFor="remember-me">
                        {{ t("views.login.fields.remember_me") }}
                      </label>
                    </VeeField>
                  </div>
                  <RouterLink to="/auth/reset-password">{{
                    t("views.login.fields.forgot_pass")
                  }}</RouterLink>
                </div>
                <div class="mt-5 text-center intro-x xl:mt-8 xl:text-left">
                  <Button variant="primary" class="w-full px-4 py-3 align-top xl:w-32 xl:mr-3">
                    {{ t("components.buttons.login") }}
                  </Button>
                  <Button variant="outline-secondary" class="w-full px-4 py-3 mt-3 align-top xl:w-32 xl:mt-0"
                    @click="router.push({ name: 'register' })">
                    {{ t("components.buttons.register") }}
                  </Button>
                </div>
              </VeeForm>
              <div class="mt-10 text-center intro-x xl:mt-24 text-slate-600 dark:text-slate-500 xl:text-left">
                By signin up, you agree to our
                <a class="text-primary dark:text-slate-200" href="">
                  {{ t("views.login.fields.terms_and_cond") }}
                </a>
                &
                <a class="text-primary dark:text-slate-200" href="">
                  {{ t("views.login.fields.privacy_policy") }}
                </a>
              </div>
            </LoadingOverlay>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>