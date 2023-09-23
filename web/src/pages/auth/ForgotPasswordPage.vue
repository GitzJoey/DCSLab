<script setup lang="ts">
import { ref } from "vue";
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
import { ForgotPasswordFormFieldValues } from "../../types/forms/AuthFormFieldValues";
import { ForgotPassword } from "../../types/models/ForgotPassword";
import { FormActions } from "vee-validate";
import Alert from "../../base-components/Alert";
import Lucide from "../../base-components/Lucide";

const { t } = useI18n();
const router = useRouter();
const authService = new AuthService();

const appName = import.meta.env.VITE_APP_NAME;
const loading = ref<boolean>(false);
const link_sent = ref<boolean>(false);

const forgotPasswordForm = ref<ForgotPasswordFormFieldValues>({
  email: ''
});

const submitForm = async (values: ForgotPasswordFormFieldValues, actions: FormActions<ForgotPasswordFormFieldValues>) => {
  loading.value = true;

  let result: ServiceResponse<ForgotPassword | null> = await authService.requestResetPassword(values);

  if (result.success) {
    link_sent.value = true;
  } else {
    actions.setErrors(result.errors as Partial<Record<string, string>>);
  }

  loading.value = false;
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
            <Alert v-if="link_sent" variant="soft-success" class="flex items-center mb-2">
              <Lucide icon="AlertTriangle" class="w-6 h-6 mr-2" />
              {{ t('views.forgot_password.alert.successfully_send_link') }}
            </Alert>
            <LoadingOverlay :visible="loading" :transparent="true">
              <h2 class="text-2xl font-bold text-center intro-x xl:text-3xl xl:text-left">
                {{ t("views.forgot_password.title") }}
              </h2>
              <div class="mt-2 text-center intro-x text-slate-400 xl:hidden">
                &nbsp;
              </div>
              <VeeForm v-slot="{ errors }" @submit="submitForm">
                <div class="mt-8 intro-x">
                  <VeeField v-slot="{ field }" v-model="forgotPasswordForm.email" name="email" rules="required|email"
                    :label="t('views.forgot_password.fields.email')">
                    <FormInput v-bind="field" type="text" name="email"
                      class="block px-4 py-3 intro-x min-w-full xl:min-w-[350px]"
                      :class="{ 'border-danger': errors['email'] }"
                      :placeholder="t('views.forgot_password.fields.email')" />
                  </VeeField>
                  <VeeErrorMessage name="email" class="mt-2 text-danger" />
                </div>
                <div class="mt-5 text-center intro-x xl:mt-8 xl:text-left">
                  <Button type="submit" variant="primary" class="w-full px-4 py-3 align-top xl:w-32 xl:mr-3"
                    :disabled="link_sent">
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