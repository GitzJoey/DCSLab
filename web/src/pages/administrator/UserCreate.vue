<script setup lang="ts">
// #region Imports
import { onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { isAxiosError, AxiosError } from "axios";
import { ServiceResponse } from "@/types/services/ServiceResponse";
import { Resource } from "@/types/resources/Resource";
import { Role } from "@/types/models/Role";
import UserService from "@/services/UserService";
import RoleService from "@/services/RoleService";
import DashboardService from "@/services/DashboardService";
import CacheService from "@/services/CacheService";
import { DropDownOption } from "@/types/models/DropDownOption";
import { TwoColumnsLayout } from "@/components/Base/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormTextarea,
    FormSelect,
    FormFileUpload,
    FormErrorMessages
} from "@/components/Base/Form";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "@/types/enums/CardState";
import Button from "@/components/Base/Button";
import { ViewMode } from "@/types/enums/ViewMode";
import { debounce } from "lodash";
import Lucide from "@/components/Base/Lucide";
import { useRouter } from "vue-router";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();

const userServices = new UserService();
const roleServices = new RoleService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state', 'show-alertplaceholder']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'views.user.field_groups.user_info', state: CardState.Expanded, },
    { title: 'views.user.field_groups.user_profile', state: CardState.Expanded },
    { title: 'views.user.field_groups.roles', state: CardState.Expanded },
    { title: 'views.user.field_groups.settings', state: CardState.Expanded },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const rolesDDL = ref<Array<Role> | null>(null);
const statusDDL = ref<Array<DropDownOption> | null>(null);
const countriesDDL = ref<Array<DropDownOption> | null>(null);

const userForm = userServices.useUserCreateForm();
// #endregion

// #region Computed
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emits('mode-state', ViewMode.FORM_CREATE);
    getDDL();

    loadFromCache();
});
// #endregion

// #region Methods
const loadFromCache = () => {
    let data = cacheServices.getLastEntity('USER_CREATE') as Record<string, unknown>;
    if (!data) return;
    userForm.setData(data);
};

const getDDL = async (): Promise<void> => {
    try {
        const [roleResult, countriesResult, statusResult] = await Promise.all([
            roleServices.readAny(),
            dashboardServices.getCountriesDDL(),
            dashboardServices.getStatusDDL()
        ]);

        if (roleResult.success && roleResult.data) {
            rolesDDL.value = roleResult.data.data as Array<Role>;
        }

        countriesDDL.value = countriesResult;
        statusDDL.value = statusResult;
    } catch (error) {
        console.error(error);
    }
};

const handleExpandCard = (index: number) => {
    if (cards.value[index].state === CardState.Collapsed) {
        cards.value[index].state = CardState.Expanded
    } else if (cards.value[index].state === CardState.Expanded) {
        cards.value[index].state = CardState.Collapsed
    }
};

const scrollToError = (id: string): void => {
    let el = document.getElementById(id);

    if (!el) return;

    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const onSubmit = async () => {
    if (userForm.hasErrors) {
        scrollToError(Object.keys(userForm.errors)[0]);
    }

    emits('loading-state', true);
    await userForm.submit().then(() => {
        resetForm();
        router.push({ name: 'side-menu-administrator-user-list' });
    }).catch(error => {
        let errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error);
        showAlertPlaceholder('danger', '', errorList);
    }).finally(() => {
        emits('loading-state', false);
    });
};

const resetForm = () => {
    userForm.reset();
    userForm.setErrors({});
};

const showAlertPlaceholder = (pAlertType: 'hidden'|'danger'|'success'|'warning'|'pending'|'dark', pTitle: string, pAlertList: Record<string, Array<string>>|null) => {
  let ap: AlertPlaceholderProps = {
    alertType: pAlertType,
    title: pTitle,
    alertList: pAlertList,
  };

  emits('show-alertplaceholder', ap);
};

// #region Watchers
watch(
    userForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('USER_CREATE', newValue.data())
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="userForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': userForm.invalid('name') }">
                            {{ t('views.user.fields.name') }}
                        </FormLabel>
                        <FormInput v-model="userForm.name" type="text"
                            :class="{ 'border-danger': userForm.invalid('name') }"
                            :placeholder="t('views.user.fields.name')" @change="userForm.validate('name')" />
                        <FormErrorMessages :messages="userForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': userForm.invalid('email') }">
                            {{ t('views.user.fields.email') }}
                        </FormLabel>
                        <FormInput v-model="userForm.email" type="text"
                            :class="{ 'border-danger': userForm.invalid('email') }"
                            :placeholder="t('views.user.fields.email')" @change="userForm.validate('email')" />
                        <FormErrorMessages :messages="userForm.errors.email" />
                    </div>
                </div>
            </template>
            <template #card-items-1>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel>{{ t('views.user.fields.first_name') }}</FormLabel>
                        <FormInput v-model="userForm.first_name" type="text"
                            :placeholder="t('views.user.fields.name')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel>{{ t('views.user.fields.last_name') }}</FormLabel>
                        <FormInput v-model="userForm.last_name" type="text"
                            :placeholder="t('views.user.fields.last_name')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel class="form-label">{{ t('views.user.fields.address') }}</FormLabel>
                        <FormInput v-model="userForm.address" type="text"
                            :placeholder="t('views.user.fields.address')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel>{{ t('views.user.fields.city') }}</FormLabel>
                        <FormInput v-model="userForm.city" type="text" class="form-control"
                            :placeholder="t('views.user.fields.city')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel>{{ t('views.user.fields.postal_code') }}</FormLabel>
                        <FormInput v-model="userForm.postal_code" type="text"
                            :placeholder="t('views.user.fields.postal_code')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': userForm.invalid('country') }">
                            {{ t('views.user.fields.country') }}
                        </FormLabel>
                        <FormSelect v-model="userForm.country" 
                            :class="{ 'border-danger': userForm.invalid('country') }"
                            :placeholder="t('views.user.fields.country')" @change="userForm.validate('country')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in countriesDDL" :key="c.name" :value="c.name">{{ c.name }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="userForm.errors.country" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': false }">
                            {{ t('views.user.fields.picture') }}
                        </FormLabel>
                        <FormFileUpload v-model="userForm.img_path"
                            :class="{ 'border-danger': false }" :placeholder="t('views.user.fields.picture')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': userForm.invalid('tax_id') }">
                            {{ t('views.user.fields.tax_id') }}
                        </FormLabel>
                        <FormInput v-model="userForm.tax_id" type="text"
                            :class="{ 'border-danger': userForm.invalid('tax_id') }"
                            @change="userForm.validate('tax_id')" />
                        <FormErrorMessages :messages="userForm.errors.tax_id" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': userForm.invalid('ic_num') }">
                            {{ t('views.user.fields.ic_num') }}
                        </FormLabel>
                        <FormInput v-model="userForm.ic_num" type="text"
                            :class="{ 'border-danger': false }" @change="userForm.validate('ic_num')" />
                        <FormErrorMessages :messages="userForm.errors.ic_num" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': userForm.invalid('status') }">
                            {{ t('views.user.fields.status') }}
                        </FormLabel>
                        <FormSelect v-model="userForm.status" :class="{ 'border-danger': false }"
                            @change="userForm.validate('status')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="userForm.errors.status" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': false }">
                            {{ t('views.user.fields.remarks') }}
                        </FormLabel>
                        <FormTextarea v-model="userForm.remarks" type="text"
                            :placeholder="t('views.user.fields.remarks')" rows="3" />
                    </div>
                </div>
            </template>
            <template #card-items-2>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': userForm.invalid('roles') }">
                            {{ t('views.user.fields.roles') }}
                        </FormLabel>
                        <FormSelect v-model="userForm.roles" multiple size="6"
                            :class="{ 'border-danger': userForm.invalid('roles') }" @change="userForm.validate('roles')">
                            <option v-for="r in rolesDDL" :key="r.id" :value="r">
                                {{ r.display_name }}
                            </option>
                        </FormSelect>
                        <FormErrorMessages :messages="userForm.errors.roles" />
                    </div>
                </div>
            </template>
            <template #card-items-3>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel>
                            {{ t('views.user.fields.settings.theme') }}
                        </FormLabel>
                        <FormSelect v-model="userForm.theme">
                            <option value="side-menu-light-full">Menu Light</option>
                            <option value="side-menu-light-mini">Mini Menu Light</option>
                            <option value="side-menu-dark-full">Menu Dark</option>
                            <option value="side-menu-dark-mini">Mini Menu Dark</option>
                        </FormSelect>
                    </div>
                    <div class="pb-4">
                        <FormLabel>
                            {{ t('views.user.fields.settings.date_format') }}
                        </FormLabel>
                        <FormSelect v-model="userForm.date_format">
                            <option value="yyyy_MM_dd">{{ 'YYYY-MM-DD' }}</option>
                            <option value="dd_MMM_yyyy">{{ 'DD-MMM-YYYY' }}</option>
                        </FormSelect>
                    </div>
                    <div class="pb-4">
                        <FormLabel>
                            {{ t('views.user.fields.settings.time_format') }}
                        </FormLabel>
                        <FormSelect v-model="userForm.time_format">
                            <option value="hh_mm_ss">{{ 'HH:mm:ss' }}</option>
                            <option value="h_m_A">{{ 'H:m A' }}</option>
                        </FormSelect>
                    </div>
                </div>
            </template>
            <template #card-items-button>
                <div class="flex gap-4">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
                        :disabled="userForm.validating || userForm.hasErrors">
                        <Lucide v-if="userForm.validating" icon="Loader" class="animate-spin" />
                        <template v-else>
                            {{ t("components.buttons.submit") }}
                        </template>
                    </Button>
                    <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md" @click="resetForm">
                        {{ t("components.buttons.reset") }}
                    </Button>
                </div>
            </template>
        </TwoColumnsLayout>
    </form>
</template>