<script setup lang="ts">
import { onMounted, ref, computed } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { Role } from "../../types/models/Role";
import UserService from "../../services/UserService";
import RoleService from "../../services/RoleService";
import DashboardService from "../../services/DashboardService";
import CacheService from "../../services/CacheService";
import { DropDownOption } from "../../types/models/DropDownOption";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import {
    TitleLayout, TwoColumnsLayout
} from "../../base-components/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormTextarea,
    FormSelect,
    FormInputCode,
    FormFileUpload,
} from "../../base-components/Form";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "../../types/enums/CardState";
import { client, useForm } from "laravel-precognition-vue";

const { t } = useI18n();
const router = useRouter();
const userServices = new UserService();
const roleServices = new RoleService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();

const loading = ref<boolean>(false);
const titleView = computed((): string => { return t('views.user.actions.create'); });
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'User Information', state: CardState.Expanded, },
    { title: 'User Profile', state: CardState.Expanded },
    { title: 'Roles', state: CardState.Expanded },
    { title: 'Settings', state: CardState.Expanded },
    { title: 'Token Managements', state: CardState.Expanded },
    { title: 'Password Managements', state: CardState.Expanded },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const userForm = useForm('post', '', {
    name: '',
    email: '',

    first_name: '',
    last_name: '',
    address: '',
    city: '',
    postal_code: '',
    country: '',
    img_path: '',
    tax_id: 0,
    ic_num: 0,
    status: '',
    remarks: '',

    roles: [],

    theme: '',
    date_format: '',
    time_format: '',
});

const rolesDDL = ref<Array<Role> | null>(null);
const statusDDL = ref<Array<DropDownOption> | null>(null);
const countriesDDL = ref<Array<DropDownOption> | null>(null);

const handleExpandCard = (index: number) => {
    if (cards.value[index].state === CardState.Collapsed) {
        cards.value[index].state = CardState.Expanded
    } else if (cards.value[index].state === CardState.Expanded) {
        cards.value[index].state = CardState.Collapsed
    }
}

//#region onMounted
onMounted(async () => {
    getDDL();
});
//#endregion

const getDDL = (): void => {
    roleServices.readAny().then((result: ServiceResponse<Resource<Array<Role>> | null>) => {
        if (result.success && result.data) {
            rolesDDL.value = result.data.data as Array<Role>;
        }
    });

    dashboardServices.getCountriesDDL().then((result: Array<DropDownOption> | null) => {
        countriesDDL.value = result;
    });

    dashboardServices.getStatusDDL().then((result: Array<DropDownOption> | null) => {
        statusDDL.value = result;
    });
}

const backToList = async () => {
    cacheServices.removeLastEntity('User');

    router.push({ name: 'side-menu-administrator-user' });
}

const onSubmit = async () => {
    loading.value = true;

    loading.value = false;
};

</script>

<template>
    <div class="mt-8">
        <LoadingOverlay :visible="loading">
            <TitleLayout>
                <template #title>
                    {{ titleView }}
                </template>
                <template #optional>
                    <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
                        <Button as="a" href="#" variant="primary" class="shadow-md" @click="backToList">
                            <Lucide icon="Back" class="w-4 h-4" />&nbsp;{{ t("components.buttons.back") }}
                        </Button>
                    </div>
                </template>
            </TitleLayout>

            <form id="userForm" @submit.prevent="onSubmit">
                <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
                    <template #card-items-0>
                        <div class="p-5">
                            <div class="pb-4">
                                <FormLabel html-for="name" :class="{ 'text-danger': false }">
                                    {{ t('views.user.fields.name') }}
                                </FormLabel>
                                <FormInput v-model="userForm.name" id="name" name="name" type="text"
                                    :class="{ 'border-danger': false }" :placeholder="t('views.user.fields.name')" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="email" :class="{ 'text-danger': false }">
                                    {{ t('views.user.fields.email') }}
                                </FormLabel>
                                <FormInput v-model="userForm.email" id="email" name="email" type="text"
                                    :class="{ 'border-danger': false }" :placeholder="t('views.user.fields.email')" />
                            </div>
                        </div>
                    </template>
                    <template #card-items-1>
                        <div class="p-5">
                            <div class="pb-4">
                                <FormLabel html-for="first_name">{{ t('views.user.fields.first_name') }}</FormLabel>
                                <FormInput v-model="userForm.email" id="first_name" name="first_name" type="text"
                                    :class="{ 'border-danger': false }" :placeholder="t('views.user.fields.name')"
                                    @change="userForm.validate('email')" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="last_name">{{ t('views.user.fields.last_name') }}</FormLabel>
                                <FormInput v-model="userForm.last_name" id="last_name" name="last_name" type="text"
                                    :placeholder="t('views.user.fields.last_name')" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="address" class="form-label">{{ t('views.user.fields.address') }}
                                </FormLabel>
                                <FormInput v-model="userForm.address" id="address" name="address" type="text"
                                    :placeholder="t('views.user.fields.address')" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="city">{{ t('views.user.fields.city') }}</FormLabel>
                                <FormInput v-model="userForm.city" id="city" name="city" type="text" class="form-control"
                                    :placeholder="t('views.user.fields.city')" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="postal_code">{{ t('views.user.fields.postal_code') }}
                                </FormLabel>
                                <FormInput v-model="userForm.postal_code" id="postal_code" name="postal_code" type="text"
                                    :placeholder="t('views.user.fields.postal_code')" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="country" :class="{ 'text-danger': false }">
                                    {{ t('views.user.fields.country') }}
                                </FormLabel>
                                <FormSelect v-model="userForm.country" id="country" name="country"
                                    :class="{ 'border-danger': false }" :placeholder="t('views.user.fields.country')">
                                    <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                    <option v-for="c in countriesDDL" :key="c.name" :value="c.name">{{ c.name }}
                                    </option>
                                </FormSelect>
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="img_path" :class="{ 'text-danger': false }">
                                    {{ t('views.user.fields.picture') }}
                                </FormLabel>
                                <FormFileUpload id="img_path" v-model="userForm.img_path" name="img_path" type="text"
                                    :class="{ 'border-danger': false }" :placeholder="t('views.user.fields.picture')" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="tax_id" :class="{ 'text-danger': false }">
                                    {{ t('views.user.fields.tax_id') }}
                                </FormLabel>
                                <FormInput v-model="userForm.tax_id" id="tax_id" name="tax_id" type="text"
                                    :class="{ 'border-danger': false }" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="ic_num" :class="{ 'text-danger': false }">
                                    {{ t('views.user.fields.ic_num') }}
                                </FormLabel>
                                <FormInput id="ic_num" v-model="userForm.ic_num" name="ic_num" type="text"
                                    :class="{ 'border-danger': false }" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="status" :class="{ 'text-danger': false }">
                                    {{ t('views.user.fields.status') }}
                                </FormLabel>
                                <FormSelect id="status" v-model="userForm.status" name="status"
                                    :class="{ 'border-danger': false }">
                                    <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                    <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}
                                    </option>
                                </FormSelect>
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="remarks" :class="{ 'text-danger': false }">
                                    {{ t('views.user.fields.remarks') }}
                                </FormLabel>
                                <FormTextarea id="remarks" v-model="userForm.remarks" name="remarks" type="text"
                                    :placeholder="t('views.user.fields.remarks')" rows="3" />
                            </div>
                        </div>
                    </template>
                    <template #card-items-2>
                        <div class="p-5">
                            <div class="pb-4">
                                <FormLabel html-for="roles" :class="{ 'text-danger': false }">
                                    {{ t('views.user.fields.roles') }}
                                </FormLabel>
                                <FormSelect id="roles" v-model="userForm.roles" multiple size="6"
                                    :class="{ 'border-danger': false }">
                                    <option v-for="r in rolesDDL" :key="r.id" :value="r">
                                        {{ r.display_name }}
                                    </option>
                                </FormSelect>
                            </div>
                        </div>
                    </template>
                    <template #card-items-3>
                        <div class="p-5">
                            <div class="pb-4">
                                <FormLabel html-for="theme">
                                    {{ t('views.user.fields.settings.theme') }}
                                </FormLabel>
                                <FormSelect v-model="userForm.theme" id="theme" name="theme">
                                    <option value="side-menu-light-full">Menu Light</option>
                                    <option value="side-menu-light-mini">Mini Menu Light</option>
                                    <option value="side-menu-dark-full">Menu Dark</option>
                                    <option value="side-menu-dark-mini">Mini Menu Dark</option>
                                </FormSelect>
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="date_format">
                                    {{ t('views.user.fields.settings.date_format') }}
                                </FormLabel>
                                <FormSelect v-model="userForm.date_format" id="date_format" name="date_format">
                                    <option value="yyyy_MM_dd">{{ 'YYYY-MM-DD' }}</option>
                                    <option value="dd_MMM_yyyy">{{ 'DD-MMM-YYYY' }}</option>
                                </FormSelect>
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="time_format">
                                    {{ t('views.user.fields.settings.time_format') }}
                                </FormLabel>
                                <FormSelect v-model="userForm.time_format" id="time_format" name="time_format">
                                    <option value="hh_mm_ss">{{ 'HH:mm:ss' }}</option>
                                    <option value="h_m_A">{{ 'H:m A' }}</option>
                                </FormSelect>
                            </div>
                        </div>
                    </template>
                    <template #card-items-4>
                        <div class="p-5">
                            <div class="pb-4">
                                <FormLabel html-for="tokens_reset">
                                    {{ t('views.user.fields.tokens.reset') }}
                                </FormLabel>

                            </div>
                        </div>
                    </template>
                    <template #card-items-5>
                        <div class="p-5">
                            <div class="pb-4">

                            </div>
                        </div>
                    </template>
                    <template #card-items-button>
                        <div class="flex gap-4">
                            <Button type="submit" href="#" variant="primary" class="w-28 shadow-md">
                                {{ t("components.buttons.submit") }}
                            </Button>
                            <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md">
                                {{ t("components.buttons.reset") }}
                            </Button>
                        </div>
                    </template>
                </TwoColumnsLayout>
            </form>
        </LoadingOverlay>
    </div>
</template>