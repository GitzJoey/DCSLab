<template>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ t('views.profile.title') }}
        </h2>
    </div>
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
                    <a @click.prevent="changeTab('personal_info')" class="flex items-center text-theme-25 dark:text-theme-22 font-medium" href="">
                        <ActivityIcon class="w-4 h-4 mr-2" /> {{ t('views.profile.menu.personal_information') }}
                    </a>
                    <a @click.prevent="changeTab('account_settings')" class="flex items-center mt-5" href="">
                        <BoxIcon class="w-4 h-4 mr-2" /> {{ t('views.profile.menu.account_settings') }}
                    </a>
                    <a @click.prevent="changeTab('change_password')" class="flex items-center mt-5" href="">
                        <LockIcon class="w-4 h-4 mr-2" /> {{ t('views.profile.menu.change_password') }}
                    </a>
                    <a @click.prevent="changeTab('user_settings')" class="flex items-center mt-5" href="">
                        <SettingsIcon class="w-4 h-4 mr-2" /> {{ t('views.profile.menu.user_settings') }}
                    </a>
                    <a @click.prevent="changeTab('email_settings')" class="flex items-center mt-5" href="">
                        <ActivityIcon class="w-4 h-4 mr-2" /> {{ t('views.profile.menu.email_settings') }}
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
                        <vee-form id="profileForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
                            <div class="mb-3">
                                <div class="form-inline">
                                    <label class="form-label w-40 px-3" for="name">{{ t('views.profile.fields.name') }}</label>
                                    <vee-field as="input" :class="{'form-control':true, 'border-theme-21':errors['name']}" :label="t('views.profile.fields.name')" id="name" name="name" v-model="userContext.name"/>
                                </div>
                                <ErrorMessage name="name" class="text-theme-21 sm:ml-40 sm:pl-5 mt-2" />
                            </div>
                            <div class="mb-3">
                                <div class="form-inline">
                                    <label class="form-label w-40 px-3" for="email">{{ t('views.profile.fields.email') }}</label>
                                    <input type="text" class="form-control" :label="t('views.profile.fields.email')" id="email" name="email" readonly v-model="userContext.email"/>
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
                                    <vee-field as="input" :class="{'form-control':true, 'border-theme-21':errors['tax_id']}" :label="t('views.profile.fields.tax_id')" id="tax_id" name="tax_id" v-model="userContext.profile.tax_id"/>
                                </div>
                                <ErrorMessage name="tax_id" class="text-theme-21 sm:ml-40 sm:pl-5 mt-2" />
                            </div>
                            <div class="mb-3">
                                <div class="form-inline">
                                    <label class="form-label w-40 px-3" for="ic_num">{{ t('views.profile.fields.ic_num') }}</label>
                                    <vee-field as="input" :class="{'form-control':true, 'border-theme-21':errors['ic_num']}" :label="t('views.profile.fields.ic_num')" id="ic_num" name="ic_num" v-model="userContext.profile.ic_num"/>
                                </div>
                                <ErrorMessage name="ic_num" class="text-theme-21 sm:ml-40 sm:pl-5 mt-2" />
                            </div>
                            <div class="mb-3">
                                <div class="form-inline">
                                    <label class="form-label w-40 px-3" for="remarks">{{ t('views.profile.fields.remarks') }}</label>
                                    <textarea type="text" rows="3" class="form-control" id="remarks" name="remarks" v-model="userContext.profile.remarks"/>
                                </div>
                            </div>
                        </vee-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject, onMounted, ref } from "vue";
import mainMixins from '../../mixins';
import { useStore } from "../../store";

const store = useStore();
const { t, assetPath, isEmptyObject } = mainMixins();

const schema = {
    name: 'required',
    email: 'required|email',
    tax_id: 'required',
    ic_num: 'required',
};

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
}

onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);
});
</script>
