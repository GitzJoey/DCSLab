<script setup lang="ts">
import { onMounted, ref } from "vue";
import DarkModeSwitcher from "../../components/DarkModeSwitcher";
import MainColorSwitcher from "../../components/MainColorSwitcher";
import logoUrl from "../../assets/images/logo.svg";
import illustrationUrl from "../../assets/images/illustration.svg";
import { FormInput } from "../../base-components/Form";
import Button from "../../base-components/Button";
import { useI18n } from "vue-i18n";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import { useRouter } from "vue-router";
import AuthService from "../../services/AuthServices";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { ResetPasswordRequest } from "../../types/requests/AuthRequests";
import { ResetPassword } from "../../types/models/ResetPassword";

const { t } = useI18n();
const router = useRouter();
const authService = new AuthService();

const appName = import.meta.env.VITE_APP_NAME;
const loading = ref<boolean>(false);

const resetPasswordForm = ref<ResetPasswordRequest>({
  email: '',
  token: '',
  password: '',
  password_confirmation: '',
});

onMounted(async () => {
  const urlParams = new URLSearchParams(window.location.search);
  let email: string | null = urlParams.get('email');
  let token: string | null = urlParams.get('token');

  if (email != null) resetPasswordForm.value.email = email;
  if (token != null) resetPasswordForm.value.token = token;
});

const submitForm = async (values: ResetPasswordRequest) => {
  let result: ServiceResponse<ResetPassword | null> = await authService.resetPassword(values);

  console.log(result);
}
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
                {{ t("views.reset_password.title") }}
              </h2>
              <div class="mt-2 text-center intro-x text-slate-400 xl:hidden">
                &nbsp;
              </div>
              <VeeForm v-slot="{ errors }" @submit="submitForm">
                <input v-model="resetPasswordForm.token" type="hidden" name="token" />
                <div class="mt-8 intro-x">
                  <FormInput v-model="resetPasswordForm.email" type="text" name="email"
                    class="block px-4 py-3 intro-x min-w-full xl:min-w-[350px]"
                    :placeholder="t('views.reset_password.fields.email')" disabled />
                  <VeeErrorMessage name="email" class="mt-2 text-danger" />
                  <VeeField v-slot="{ field }" name="password" rules="required"
                    :label="t('views.reset_password.fields.password')">
                    <FormInput v-model="resetPasswordForm.password" type="password" name="password"
                      class="block px-4 py-3 mt-4 intro-x min-w-full xl:min-w-[350px]"
                      :class="{ 'border-danger': errors['password'] }"
                      :placeholder="t('views.reset_password.fields.password')" v-bind="field" />
                  </VeeField>
                  <VeeErrorMessage name="password" class="mt-2 text-danger" />
                  <VeeField v-slot="{ field }" name="password_confirmation" rules="confirmed:@password"
                    :label="t('views.reset_password.fields.password_confirmation')">
                    <FormInput v-model="resetPasswordForm.password_confirmation" name="password_confirmation"
                      type="password"
                      :class="{ 'block px-4 py-3 mt-4 intro-x min-w-full xl:min-w-[350px]': true, 'border-danger': errors['password'] }"
                      :placeholder="t('views.reset_password.fields.password_confirmation')
                        " v-bind="field" />
                  </VeeField>
                  <VeeErrorMessage name="password_confirmation" class="mt-2 text-danger" />
                </div>
                <div class="mt-5 text-center intro-x xl:mt-8 xl:text-left">
                  <Button type="submit" variant="primary" class="w-full px-4 py-3 align-top xl:w-32 xl:mr-3">
                    {{ t("components.buttons.submit") }}
                  </Button>
                  <Button type="button" variant="outline-secondary"
                    class="w-full px-4 py-3 mt-3 align-top xl:w-32 xl:mt-0" @click="router.push({ name: 'login' })">
                    {{ t("components.buttons.login") }}
                  </Button>
                </div>
              </VeeForm>
            </LoadingOverlay>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>