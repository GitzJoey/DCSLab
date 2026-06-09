<script lang="ts" setup>
import { Box } from '@/components/ui/box'
import { Button } from '@/components/ui/button'
import { CheckboxRoot, CheckboxControl, CheckboxLabel } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import logoUrl from '@/assets/images/logo.png'
import illustrationUrl from '@/assets/images/illustration.svg'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import AuthService from '@/services/AuthService'
import LoadingOverlay from '@/components/loading-overlay/LoadingOverlay.vue'
import { onMounted, ref } from "vue";

const { t } = useI18n()
const router = useRouter()

const authService = new AuthService()
const appName = import.meta.env.VITE_APP_NAME;

const loading = ref<boolean>(false);
const status = ref<'onLoad' | 'success' | 'error'>('onLoad');

onMounted(async () => {
    authService.ensureCSRF();
});


</script>

<template>
    <LoadingOverlay :visible="loading" :transparent="false">
        <div :class="[
            'relative h-screen lg:overflow-hidden bg-primary bg-noise xl:bg-background xl:bg-none',
            'before:hidden before:xl:block before:content-[\'\'] before:w-[57%] before:mt-[-28%] before:mb-[-16%] before:ml-[-12%] before:absolute before:inset-y-0 before:left-0 before:transform before:rotate-6 before:bg-primary/95 before:bg-noise before:rounded-[35%]',
            'after:hidden after:xl:block after:content-[\'\'] after:w-[57%] after:mt-[-28%] after:mb-[-16%] after:ml-[-12%] after:absolute after:inset-y-0 after:left-0 after:transform after:rotate-6 after:border after:bg-accent after:bg-cover after:blur-xl after:rounded-[35%] after:border-primary',
        ]">
            <div :class="[
                'p-3 sm:px-8 relative h-full',
                'before:hidden before:xl:block before:w-[57%] before:mt-[-20%] before:mb-[-13%] before:ml-[-12%] before:absolute before:inset-y-0 before:left-0 before:transform before:rotate-6 before:bg-primary/40 before:bg-noise before:border before:border-primary/50 before:opacity-60 before:rounded-[20%]',
            ]">
                <div class="container relative z-10 mx-auto sm:px-20">
                    <div class="block grid-cols-2 gap-4 xl:grid">
                        <div class="hidden min-h-screen flex-col xl:flex">
                            <a class="flex items-center pt-10" href="">
                                <img class="w-6" :src="logoUrl" alt="DCSLab" />
                                <span class="ml-3 text-xl font-medium text-white">
                                    {{ appName }} <span class="font-light opacity-70"></span>
                                </span>
                            </a>
                            <div class="my-auto">
                                <img class="-mt-16 w-1/2" :src="illustrationUrl" alt="&nbsp;" />
                                <div class="mt-10 text-4xl font-medium leading-tight text-white">
                                </div>
                                <div class="mt-5 text-lg text-white opacity-60">
                                </div>
                            </div>
                        </div>
                        <div class="my-10 flex h-screen py-5 xl:my-0 xl:h-auto xl:py-0">
                            <Box raised="double"
                                class="mx-auto my-auto w-full px-5 py-8 sm:w-3/4 sm:px-8 lg:w-2/4 xl:ml-24 xl:w-auto xl:p-0 xl:before:hidden xl:after:hidden xl:shadow-none xl:border-none xl:bg-none">
                                <h2 class="text-center text-2xl font-semibold xl:text-left xl:text-3xl">{{
                                    t("views.login.title") }}
                                </h2>
                                <div class="mt-2 text-center opacity-70 xl:hidden">
                                    &nbsp;
                                </div>
                                <div class="mt-8 flex flex-col gap-5">
                                    <Input class="box block min-w-full px-5 py-6 xl:min-w-md" type="text"
                                        :placeholder="t('views.login.fields.email')" />
                                    <Input class="box block min-w-full px-5 py-6 xl:min-w-md" type="password"
                                        :placeholder="t('views.login.fields.password')" />
                                    <div class="flex text-xs sm:text-sm">
                                        <div class="mr-auto flex-row items-center">
                                            <CheckboxRoot>
                                                <CheckboxControl />
                                                <CheckboxLabel>{{ t("views.login.fields.remember_me") }}</CheckboxLabel>
                                            </CheckboxRoot>
                                        </div>
                                        <a class="opacity-70" href="">{{ t("views.login.fields.forgot_pass") }}</a>
                                    </div>
                                </div>
                                <div class="mt-5 text-center xl:mt-10 xl:text-left">
                                    <Button class="login-button box w-full px-4 py-5" variant="primary">{{
                                        t("components.buttons.login") }}</Button>
                                    <Button class="box mt-4 w-full px-4 py-5" look="outline"> {{
                                        t("components.buttons.register") }} </Button>
                                </div>
                                <div class="mt-10 text-center opacity-70 xl:mt-24 xl:text-left">
                                </div>
                            </Box>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </LoadingOverlay>
</template>