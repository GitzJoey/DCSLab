<template>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ t('views.profile.title') }}
        </h2>
    </div>
    <AlertPlaceholder :messages="alertErrors" :alertType="alertType" :title="alertTitle" />
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 lg:col-span-3 2xl:col-span-3 flex lg:block flex-col-reverse">
            <div class="intro-y box mt-5 lg:mt-0">
                <div class="relative flex items-center p-5">
                    <div class="w-12 h-12 image-fit">
                        <img alt="" class="rounded-full" :src="assetPath('def-user.png')">
                    </div>
                    <div class="ml-4 mr-auto">
                        <div class="font-medium text-base">{{ userContext.name }}</div>
                        <div class="text-slate-600">{{ userContext.email }}</div>
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
                        <div class="loader-container">
                            <VeeForm id="profileForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                                <div class="p-5">
                                    <div class="mb-3">
                                        <label class="form-label" for="name">{{ t('views.profile.fields.name') }}</label>
                                        <VeeField type="text" :class="{'form-control':true, 'border-danger':errors['name']}" :label="t('views.profile.fields.name')" id="name" name="name" rules="required" v-model="userContext.name" />
                                        <ErrorMessage name="name" class="text-danger" />
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="email">{{ t('views.profile.fields.email') }}</label>
                                        <div class="flex items-center">
                                            <input type="text" class="form-control" :label="t('views.profile.fields.email')" id="email" name="email" readonly v-model="userContext.email" />
                                            <a class="tooltip px-2" href="javascript:;" :title="t('views.profile.tooltip.email_verified')" v-if="userContext.emailVerified"><ThumbsUpIcon /></a>
                                        </div>
                                        <div class="mt-2" v-if="!userContext.emailVerified">
                                            <button type="button" class="btn btn-sm" @click.prevent="sendVerificationLink">{{ t('components.buttons.send_verification_email') }}</button>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="inputImg"></label>
                                        <div class="form-inline">                                            
                                            <div class="flex-1">
                                                <img alt="" class="my-1" :src="retrieveImage">
                                                <input type="file" class="h-full w-full" id="inputImg" name="img_path" data-toggle="custom-file-input" v-on:change="handleUpload" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="firstName">{{ t('views.profile.fields.first_name') }}</label>
                                        <input type="text" class="form-control" id="firstName" name="first_name" v-model="userContext.profile.first_name" />
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="lastName">{{ t('views.profile.fields.last_name') }}</label>
                                        <input type="text" class="form-control" id="lastName" name="last_name" v-model="userContext.profile.last_name" />
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="address">{{ t('views.profile.fields.address') }}</label>
                                        <input type="text" class="form-control" id="address" name="address" v-model="userContext.profile.address" />
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="city">{{ t('views.profile.fields.city') }}</label>
                                        <input type="text" class="form-control" id="city" name="city" v-model="userContext.profile.city" />
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="postal_code">{{ t('views.profile.fields.postal_code') }}</label>
                                        <input type="text" class="form-control" id="postal_code" name="postal_code" v-model="userContext.profile.postal_code" />
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="country">{{ t('views.profile.fields.country') }}</label>
                                        <input type="text" class="form-control" id="country" name="country" readonly v-model="userContext.profile.country" />
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="tax_id">{{ t('views.profile.fields.tax_id') }}</label>
                                        <VeeField type="text" :class="{'form-control':true, 'border-danger':errors['tax_id']}" :label="t('views.profile.fields.tax_id')" id="tax_id" name="tax_id" rules="required" v-model="userContext.profile.tax_id" />
                                        <ErrorMessage name="tax_id" class="text-danger" />
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="ic_num">{{ t('views.profile.fields.ic_num') }}</label>
                                        <VeeField type="text" :class="{'form-control':true, 'border-danger':errors['ic_num']}" :label="t('views.profile.fields.ic_num')" id="ic_num" name="ic_num" rules="required" v-model="userContext.profile.ic_num" />
                                        <ErrorMessage name="ic_num" class="text-danger" />
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="remarks">{{ t('views.profile.fields.remarks') }}</label>
                                        <textarea type="text" rows="3" class="form-control" id="remarks" name="remarks" v-model="userContext.profile.remarks" />
                                    </div>
                                </div>
                                <div class="pl-5">
                                    <div class="form-inline">
                                        <button type="submit" class="btn btn-primary w-24 mr-3">{{ t('components.buttons.save') }}</button>
                                        <button type="button" class="btn btn-secondary" @click="handleReset(); resetAlertErrors()">{{ t('components.buttons.reset') }}</button>
                                    </div>
                                </div>
                            </VeeForm>
                            <div class="loader-overlay" v-if="loading"></div>
                        </div>
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
                        <div class="loader-container">
                            <VeeForm id="profileSettingsForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                                <div class="p-5">
                                    <div class="mb-3">
                                        <label class="form-label" for="inputTheme">{{ t('views.profile.fields.settings.theme') }}</label>
                                        <select class="form-control form-select" id="inputTheme" name="theme" v-model="userContext.selected_settings.theme">
                                            <option value="side-menu-light-full">Menu Light</option>
                                            <option value="side-menu-light-mini">Mini Menu Light</option>
                                            <option value="side-menu-dark-full">Menu Dark</option>
                                            <option value="side-menu-dark-mini">Mini Menu Dark</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="selectDate">{{ t('views.profile.fields.settings.dateFormat') }}</label>
                                        <select id="selectDate" class="form-control form-select" name="dateFormat" v-model="userContext.selected_settings.dateFormat">
                                            <option value="yyyy_MM_dd">{{ helper.formatDate(new Date(), 'YYYY-MM-DD') }}</option>
                                            <option value="dd_MMM_yyyy">{{ helper.formatDate(new Date(), 'DD-MMM-YYYY') }}</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="selectTime">{{ t('views.profile.fields.settings.timeFormat') }}</label>
                                        <select id="selectTime" class="form-control form-select" name="timeFormat" v-model="userContext.selected_settings.timeFormat">
                                            <option value="hh_mm_ss">{{ helper.formatDate(new Date(), 'HH:mm:ss') }}</option>
                                            <option value="h_m_A">{{ helper.formatDate(new Date(), 'H:m A') }}</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="api_token">{{ t('views.profile.fields.settings.api_token') }}</label>
                                        <div class="form-check pr-5">
                                            <input id="apiToken" name="apiToken" class="form-check-input" type="checkbox" value="" />
                                            <label class="form-check-label" for="apiToken">{{ t('components.buttons.reset') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="pl-5">
                                    <div class="form-inline">
                                        <button type="submit" class="btn btn-primary w-24 mr-3">{{ t('components.buttons.save') }}</button>
                                        <button type="button" class="btn btn-secondary" @click="handleReset(); resetAlertErrors()">{{ t('components.buttons.reset') }}</button>
                                    </div>
                                </div>
                            </VeeForm>
                            <div class="loader-overlay" v-if="loading"></div>
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
                        <div class="loader-container">
                            <div class="p-5">
                                <div class="mb-3">
                                    <div class="grid grid-cols-2 gap-2 place-items-center">
                                        <div class="flex flex-col">
                                            <img alt="" :src="assetPath('pos_system.png')" width="100" height="100" />
                                            <div class="grid grid-cols-1 place-items-center" v-if="hasRolePOSOwner"><CheckIcon class="text-success" /></div>
                                            <button v-else class="btn btn-sm btn-secondary hover:btn-primary" @click="updateRoles('pos')">{{ t('components.buttons.activate') }}</button>
                                        </div>
                                        <div class="text-center">
                                            <img alt="" :src="assetPath('warehouse_system.png')" width="100" height="100" />
                                            <div class="grid grid-cols-1 place-items-center" v-if="hasRoleWHOwner"><CheckIcon class="text-success" /></div>
                                            <button v-else class="btn btn-sm btn-secondary hover:btn-primary" @click="updateRoles('wh')">{{ t('components.buttons.activate') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="loader-overlay" v-if="loading"></div>
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
                        <VeeForm id="changePasswordForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                            <div class="p-5">
                                <div class="mb-3">
                                    <label class="form-label" for="current_pwd">{{ t('views.profile.fields.change_password.current_password') }}</label>
                                    <VeeField id="current_pwd" v-bind="field" name="current_password" type="password" class="form-control" :label="t('views.profile.fields.change_password.current_password')" rules="required" />
                                    <ErrorMessage name="current_password" class="text-danger sm:ml-40 sm:pl-5 mt-2" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="new_pwd">{{ t('views.profile.fields.change_password.new_password') }}</label>
                                    <VeeField id="new_pwd" name="password" type="password" class="form-control" :label="t('views.profile.fields.change_password.new_password')" rules="required" />
                                    <ErrorMessage name="password" class="text-danger sm:ml-40 sm:pl-5 mt-2" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="confirm_pwd">{{ t('views.profile.fields.change_password.confirm_password') }}</label>
                                    <VeeField id="confirm_pwd" name="password_confirmation" type="password" class="form-control" :label="t('views.profile.fields.change_password.confirm_password')" rules="required" />
                                    <ErrorMessage name="password_confirmation" class="text-danger sm:ml-40 sm:pl-5 mt-2" />
                                </div>
                            </div>
                            <div class="pl-5">
                                <div class="form-inline">
                                    <button type="submit" class="btn btn-primary w-24 mr-3">{{ t('components.buttons.save') }}</button>
                                    <button type="button" class="btn btn-secondary" @click="handleReset(); resetAlertErrors()">{{ t('components.buttons.reset') }}</button>
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
//#region Imports
import { computed, inject, onMounted, ref } from "vue";
import axios from "@/axios";
import { useI18n } from "vue-i18n";
import { assetPath } from "@/mixins";
import { helper } from "@/utils/helper";
import { route } from "@/ziggy";
import dom from "@left4code/tw-starter/dist/js/dom";
import { useUserContextStore } from "@/stores/user-context";
import { useSideMenuStore } from "../../stores/side-menu";
import AlertPlaceholder from '@/global-components/alert-placeholder/Main'
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - Pinia
const userContextStore = useUserContextStore();
const userContext = computed(() => userContextStore.userContext );
const sideMenuStore = useSideMenuStore();
//#endregion

//#region Data - UI
const alertErrors = ref({});
const alertType = ref('');
const alertTitle = ref('');
const loading = ref(false);
const mode = ref('personal_info')
//#endregion

//#region Data - Views
const rolesList = ref([]);
//#endregion

//#region Computed
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
//#endregion

//#region onMounted
onMounted(() => {
    getRoles();
});
//#endregion

//#region Methods
const changeTab = (name) => {
    mode.value = name;
    resetAlertErrors();
}

const getRoles = () => {
    axios.get(route('api.get.db.admin.users.roles.read')).then(response => {
        rolesList.value = response.data;
    })
}

const updateRoles = async (role) => {
    loading.value = true;

    await axios.post(route('api.post.db.core.profile.update.roles'), { 'roles': role });
    await userContextStore.fetchUserContext();
    await sideMenuStore.refreshMenu();

    createSuccessAlert('changeRoles');
    loading.value = false;    
}

const onSubmit = (values, actions) => {
    if (mode.value === 'personal_info') {
        axios.post(route('api.post.db.core.profile.update.profile'), new FormData(dom('#profileForm')[0])).then(response => {
            createSuccessAlert('changeProfile');
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {

        });
    } else if (mode.value === 'account_settings') {
        axios.post(route('api.post.db.core.profile.update.settings'), new FormData(dom('#profileSettingsForm')[0])).then(response => {
            createSuccessAlert('changeSettings');
            userContextStore.fetchUserContext();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {

        });
    } else if (mode.value === 'roles') {

    } else {
        axios.post(route('api.post.db.core.profile.change_password'), new FormData(dom('#changePasswordForm')[0])).then(response => {
            createSuccessAlert('changePassword');
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {

        });
    }
}

const handleError = (e, actions) => {
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

const invalidSubmit = (e) => {
    alertErrors.value = e.errors;
    if (dom('.border-danger').length !== 0) dom('.border-danger')[0].scrollIntoView({ behavior: "smooth" });
}

const sendVerificationLink = () => {
    axios.post(route('api.post.db.core.profile.send_email_verification')).then(response => {
        createSuccessAlert('sendVerificationLink');
    }).catch(e => {
        handleError(e, actions);
    }).finally(() => {

    });
}

const resetAlertErrors = () => {
    alertErrors.value = [];
    alertType.value = '';
    alertTitle.value = '';
}

const isEmptyObject = (obj) => {
    return _.isEmpty(obj);
}

const createSuccessAlert = (type) => {
    if (type === 'changePassword') {
        alertErrors.value = {
            password: t('components.alert-placeholder.success_alert.password_changed_successfully')
        };
    } else if (type === 'changeProfile') {
        alertErrors.value = {
            profile: t('components.alert-placeholder.success_alert.profile_changed_successfully')
        };
    } else if (type === 'changeRoles') {
        alertErrors.value = {
            roles: t('components.alert-placeholder.success_alert.roles_changed_successfully')
        };
    } else if (type === 'changeSettings') {
        alertErrors.value = {
            settings: t('components.alert-placeholder.success_alert.settings_changed_successfully')
        };
    } else if (type === 'sendVerificationLink') {
        alertErrors.value = {
            profile: t('components.alert-placeholder.success_alert.verification_link_sent_successfully')
        };
    } else {

    }
    
    alertType.value = 'success';
    alertTitle.value = 'Success';
}
//#endregion

//#region Computed
const hasRolePOSOwner = computed(() => {
    return userContext.value.roles_description.includes('POS-owner');
});

const hasRoleWHOwner = computed(() => {
    return false;
});
//#endregion
</script>
