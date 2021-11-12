<template>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ t('views.profile.title') }}
        </h2>
    </div>
    <AlertPlaceholder :messages="alertErrors" :alertType="alertType" :title="alertTitle"/>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 lg:col-span-3 2xl:col-span-3 flex lg:block flex-col-reverse">
            <div class="intro-y box mt-5 lg:mt-0">
                <div class="relative flex items-center p-5">
                    <div class="w-12 h-12 image-fit">
                        <img alt="" class="rounded-full" :src="assetPath('def-user.png')">
                    </div>
                    <div class="ml-4 mr-auto">
                        <div class="font-medium text-base">{{ userContext.name }}</div>
                        <div class="text-gray-600">{{ userContext.email }}</div>
                    </div>
                </div>
                <div class="p-5 border-t border-gray-200 dark:border-dark-5">
                    <a @click.prevent="changeTab('personal_info')" :class="{'flex items-center':true, 'text-theme-25 dark:text-theme-22 font-medium':mode === 'personal_info'}" href="">
                        <ActivityIcon class="w-4 h-4 mr-2" /> {{ t('views.profile.menu.personal_information') }}
                    </a>
                    <a @click.prevent="changeTab('account_settings')" :class="{'flex items-center mt-5':true, 'text-theme-25 dark:text-theme-22 font-medium':mode === 'account_settings'}" href="">
                        <BoxIcon class="w-4 h-4 mr-2" /> {{ t('views.profile.menu.account_settings') }}
                    </a>
                    <a @click.prevent="changeTab('roles')" :class="{'flex items-center mt-5':true, 'text-theme-25 dark:text-theme-22 font-medium':mode === 'roles'}" href="">
                        <ServerIcon class="w-4 h-4 mr-2" /> {{ t('views.profile.menu.roles') }}
                    </a>
                    <a @click.prevent="changeTab('change_password')" :class="{'flex items-center mt-5':true, 'text-theme-25 dark:text-theme-22 font-medium':mode === 'change_password'}" href="">
                        <LockIcon class="w-4 h-4 mr-2" /> {{ t('views.profile.menu.change_password') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-9 2xl:col-span-9">
            <div class="grid grid-cols-12 gap-6">
                <div class="intro-y box col-span-12 2xl:col-span-6" v-if="mode === 'personal_info'">
                    <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                        <h2 class="font-medium text-base mr-auto">{{ t('views.profile.menu.personal_information') }}</h2>
                    </div>
                    <div class="intro-x flex justify-center" v-if="isEmptyObject(userContext)">
                        <LoadingIcon icon="puff" />
                    </div>
                    <div class="intro-x" v-if="!isEmptyObject(userContext)">
                        <VeeForm id="profileForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
                            <div class="pt-5 pr-5">
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="name">{{ t('views.profile.fields.name') }}</label>
                                        <VeeField as="input" :class="{'form-control':true, 'border-theme-21':errors['name']}" :label="t('views.profile.fields.name')" id="name" name="name" v-model="userContext.name"/>
                                    </div>
                                    <ErrorMessage name="name" class="text-theme-21 sm:ml-40 sm:pl-5 mt-2" />
                                </div>
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="email">{{ t('views.profile.fields.email') }}</label>
                                        <input type="text" class="form-control" :label="t('views.profile.fields.email')" id="email" name="email" readonly v-model="userContext.email"/>
                                        <div class="px-5" v-if="userContext.emailVerified"><a class="tooltip" href="javascript:;" :title="t('views.profile.tooltip.email_verified')"><ThumbsUpIcon /></a></div>
                                    </div>
                                    <div class="sm:ml-40 sm:pl-5 mt-2" v-if="!userContext.emailVerified">
                                        <button class="btn btn-sm">Send Verification Email</button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="inputImg"></label>
                                        <div class="flex-1">
                                            <img alt="" class="my-1" :src="retrieveImage">
                                            <input type="file" class="h-full w-full" id="inputImg" name="img_path" data-toggle="custom-file-input" v-on:change="handleUpload"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="firstName">{{ t('views.profile.fields.first_name') }}</label>
                                        <input type="text" class="form-control" id="firstName" name="first_name" v-model="userContext.profile.first_name"/>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="lastName">{{ t('views.profile.fields.last_name') }}</label>
                                        <input type="text" class="form-control" id="lastName" name="last_name" v-model="userContext.profile.last_name"/>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="address">{{ t('views.profile.fields.address') }}</label>
                                        <input type="text" class="form-control" id="address" name="address" v-model="userContext.profile.address"/>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="city">{{ t('views.profile.fields.city') }}</label>
                                        <input type="text" class="form-control" id="city" name="city" v-model="userContext.profile.city"/>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="postal_code">{{ t('views.profile.fields.postal_code') }}</label>
                                        <input type="text" class="form-control" id="postal_code" name="postal_code" v-model="userContext.profile.postal_code"/>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="country">{{ t('views.profile.fields.country') }}</label>
                                        <input type="text" class="form-control" id="country" name="country" readonly v-model="userContext.profile.country"/>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="tax_id">{{ t('views.profile.fields.tax_id') }}</label>
                                        <VeeField as="input" :class="{'form-control':true, 'border-theme-21':errors['tax_id']}" :label="t('views.profile.fields.tax_id')" id="tax_id" name="tax_id" v-model="userContext.profile.tax_id"/>
                                    </div>
                                    <ErrorMessage name="tax_id" class="text-theme-21 sm:ml-40 sm:pl-5 mt-2" />
                                </div>
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="ic_num">{{ t('views.profile.fields.ic_num') }}</label>
                                        <VeeField as="input" :class="{'form-control':true, 'border-theme-21':errors['ic_num']}" :label="t('views.profile.fields.ic_num')" id="ic_num" name="ic_num" v-model="userContext.profile.ic_num"/>
                                    </div>
                                    <ErrorMessage name="ic_num" class="text-theme-21 sm:ml-40 sm:pl-5 mt-2" />
                                </div>
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="remarks">{{ t('views.profile.fields.remarks') }}</label>
                                        <textarea type="text" rows="3" class="form-control" id="remarks" name="remarks" v-model="userContext.profile.remarks"/>
                                    </div>
                                </div>
                            </div>
                        </VeeForm>
                    </div>
                </div>
                <div class="intro-y box col-span-12 2xl:col-span-6" v-if="mode === 'account_settings'">
                    <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                        <h2 class="font-medium text-base mr-auto">{{ t('views.profile.menu.account_settings') }}</h2>
                    </div>
                    <div class="intro-x flex justify-center" v-if="isEmptyObject(userContext)">
                        <LoadingIcon icon="puff" />
                    </div>
                    <div class="intro-x" v-if="!isEmptyObject(userContext)">
                        <div class="pt-5 pr-5">
                            <div class="mb-3">
                                <div class="form-inline">
                                    <label class="form-label w-40 px-3" for="inputTheme">{{ t('views.profile.fields.settings.theme') }}</label>
                                    <select class="form-control form-select" id="inputTheme" name="theme" v-model="userContext.selectedSettings.theme">
                                        <option value="side-menu-light-full">Menu Light</option>
                                        <option value="side-menu-light-mini">Mini Menu Light</option>
                                        <option value="side-menu-dark-full">Menu Dark</option>
                                        <option value="side-menu-dark-mini">Mini Menu Dark</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-inline">
                                    <label class="form-label w-40 px-3" for="selectDate">{{ t('views.profile.fields.settings.dateFormat') }}</label>
                                    <select id="selectDate" class="form-control form-select" name="dateFormat" v-model="userContext.selectedSettings.dateFormat">
                                        <option value="yyyy_MM_dd">{{ helper.formatDate(new Date(), 'YYYY-MM-DD') }}</option>
                                        <option value="dd_MMM_yyyy">{{ helper.formatDate(new Date(), 'DD-MMM-YYYY') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-inline">
                                    <label class="form-label w-40 px-3" for="selectTime">{{ t('views.profile.fields.settings.timeFormat') }}</label>
                                    <select id="selectTime" class="form-control form-select" name="timeFormat" v-model="userContext.selectedSettings.timeFormat">
                                        <option value="hh_mm_ss">{{ helper.formatDate(new Date(), 'HH:mm:ss') }}</option>
                                        <option value="h_m_A">{{ helper.formatDate(new Date(), 'H:m A') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-inline">
                                    <label class="form-label w-40 px-3" for="api_token">{{ t('views.profile.fields.settings.api_token') }}</label>
                                    <button id="api_token" class="btn">{{ t('components.buttons.reset') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="intro-y box col-span-12 2xl:col-span-6" v-if="mode === 'roles'">
                    <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                        <h2 class="font-medium text-base mr-auto">{{ t('views.profile.menu.roles') }}</h2>
                    </div>
                    <div class="intro-x flex justify-center" v-if="isEmptyObject(userContext)">
                        <LoadingIcon icon="puff" />
                    </div>
                    <div class="intro-x" v-if="!isEmptyObject(userContext)">
                        <div class="pt-5 pr-5">
                            <div class="mb-3">
                                <div class="grid grid-cols-2 gap-2 place-items-center">
                                    <div class="text-center">
                                        <img alt="" :src="assetPath('pos_system.png')" width="100" height="100" />
                                        <button class="btn btn-sm btn-secondary hover:btn-primary">{{ t('components.buttons.activate') }}</button>
                                    </div>
                                    <div class="text-center">
                                        <img alt="" :src="assetPath('warehouse_system.png')" width="100" height="100" />
                                        <button class="btn btn-sm btn-secondary hover:btn-primary">{{ t('components.buttons.activate') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="intro-y box col-span-12 2xl:col-span-6" v-if="mode === 'change_password'">
                    <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                        <h2 class="font-medium text-base mr-auto">{{ t('views.profile.menu.change_password') }}</h2>
                    </div>
                    <div class="intro-x flex justify-center" v-if="isEmptyObject(userContext)">
                        <LoadingIcon icon="puff" />
                    </div>
                    <div class="intro-x" v-if="!isEmptyObject(userContext)">
                        <VeeForm id="changePasswordForm" @submit="onSubmit_ChangePassword" @invalid-submit="invalidSubmit_ChangePassword" :validation-schema="schema_changePassword" v-slot="{ handleReset, errors }">
                            <div class="pt-5 pr-5">
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="current_pwd">{{ t('views.profile.fields.change_password.current_password') }}</label>
                                        <VeeField id="current_pwd" v-bind="field" name="current_password" as="input" type="password" class="form-control" :label="t('views.profile.fields.change_password.current_password')"/>
                                    </div>
                                    <ErrorMessage name="current_password" class="text-theme-21 sm:ml-40 sm:pl-5 mt-2" />
                                </div>
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="new_pwd">{{ t('views.profile.fields.change_password.new_password') }}</label>
                                        <VeeField id="new_pwd" name="password" as="input" type="password" class="form-control" :label="t('views.profile.fields.change_password.new_password')" />
                                    </div>
                                    <ErrorMessage name="password" class="text-theme-21 sm:ml-40 sm:pl-5 mt-2" />
                                </div>
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <label class="form-label w-40 px-3" for="confirm_pwd">{{ t('views.profile.fields.change_password.confirm_password') }}</label>
                                        <VeeField id="confirm_pwd" name="password_confirmation" as="input" type="password" class="form-control" :label="t('views.profile.fields.change_password.confirm_password')" />
                                    </div>
                                    <ErrorMessage name="password_confirmation" class="text-theme-21 sm:ml-40 sm:pl-5 mt-2" />
                                </div>
                                <div class="mb-3">
                                    <div class="form-inline">
                                        <div class="ml-40 sm:ml-40 sm:pl-5 mt-2">
                                            <button type="submit" class="btn btn-primary mt-5 mr-3">{{ t('components.buttons.save') }}</button>
                                            <button type="button" class="btn btn-secondary" @click="handleReset(); resetAlertErrors()">{{ t('components.buttons.reset') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </VeeForm>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script setup>
// Vue Import
import { computed, inject, onMounted, ref } from "vue";
// Helper Import
import mainMixins from '../../mixins';
import { helper } from '../../utils/helper';
// Core Components Import
import { useStore } from "../../store";
// Components Import
import AlertPlaceholder from '../../global-components/alert-placeholder/Main'

const store = useStore();
const { t, assetPath, isEmptyObject, route } = mainMixins();

const schema = {
    name: 'required',
    email: 'required|email',
    tax_id: 'required',
    ic_num: 'required',
};

const schema_changePassword = {
    current_password: 'required',
    password: 'required',
    password_confirmation: 'required',
};

const alertErrors = ref({});
const alertType = ref('');
const alertTitle = ref('');

const userContext = computed(() => store.state.main.userContext );

const retrieveImage = computed(() => {
    if (userContext.value.profile.img_path && userContext.value.profile.img_path !== '') {
        if (userContext.value.profile.img_path.includes('data:image')) {
            return userContext.value.profile.img_path;
        } else {
            return '/storage/' + userContext.value.profile.img_path;
        }
    } else {
        return '/images/def-user.png';
    }
});

const mode = ref('personal_info')

function changeTab(name) {
    mode.value = name;
    resetAlertErrors();
}

onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);
});

function onSubmit_ChangePassword(values, actions) {
    axios.post(route('api.post.db.core.profile.change_password'), new FormData(cash('#changePasswordForm')[0])).then(response => {
        createChangePasswordSuccess();
    }).catch(e => {
        handleError(e, actions);
    }).finally(() => {

    });
}

function handleError(e, actions) {
    //Laravel Validations
    if (e.response.data.errors !== undefined && Object.keys(e.response.data.errors).length > 0) {
        for (var key in e.response.data.errors) {
            for (var i = 0; i < e.response.data.errors[key].length; i++) {
                actions.setFieldError(key, e.response.data.errors[key][i]);
            }
        }
        alertErrors.value = e.response.data.errors;
    } else {
        //Catch From Controller
        alertErrors.value = {
            controller: e.response.status + ' ' + e.response.statusText +': ' + e.response.data.message
        };
    }
}

function invalidSubmit_ChangePassword(e) {
    alertErrors.value = e.errors;
}

function resetAlertErrors() {
    alertErrors.value = [];
    alertType.value = '';
    alertTitle.value = '';
}

function createChangePasswordSuccess() {
    alertErrors.value = {
        password: 'Password Changed Successfully!'
    };
    alertType.value = 'success';
    alertTitle.value = 'Success';
}
</script>
