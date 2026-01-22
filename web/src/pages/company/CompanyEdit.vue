<script setup lang="ts">
// #region Imports
import { onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { convertErrorTypeToAlertListType } from "@/utils/helper";
import { useRoute, useRouter } from "vue-router";
import CompanyService from "@/services/CompanyService";
import DashboardService from "@/services/DashboardService";
import CacheService from "@/services/CacheService";
import { TwoColumnsLayout } from "@/components/Base/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormTextarea,
    FormSelect,
    FormSwitch,
    FormInputCode,
    FormErrorMessages,
} from "@/components/Base/Form";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "@/types/enums/CardState";
import { DropDownOption } from "@/types/models/DropDownOption";
import { ServiceResponse } from "@/types/services/ServiceResponse";
import { ViewMode } from "@/types/enums/ViewMode";
import Button from "@/components/Base/Button";
import { debounce } from "lodash";
import Lucide from "@/components/Base/Lucide";
import { Company } from "@/types/models/Company";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const route = useRoute();
const router = useRouter();

const companyServices = new CompanyService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'views.company.field_groups.company_data', state: CardState.Expanded, },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const statusDDL = ref<Array<DropDownOption> | null>(null);

const companyForm = companyServices.useCompanyEditForm(route.params.ulid as string);
// #endregion

// #region Computed
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emits('mode-state', ViewMode.FORM_EDIT);
    getDDL();

    await loadData(route.params.ulid as string);
});
// #endregion

// #region Methods
const loadData = async (ulid: string) => {
    emits('loading-state', true);
    let result: ServiceResponse<Company | null> = await companyServices.read(ulid);

    if (result.success && result.data) {
        companyForm.setData({
            code: result.data.code,
            name: result.data.name,
            address: result.data.address,
            default: result.data.default,
            status: result.data.status,
        });
    } else {
        router.push({ name: 'side-menu-company-company-list' });
    }
    emits('loading-state', false);
}

const getDDL = async (): Promise<void> => {
    statusDDL.value = await dashboardServices.getStatusDDL();
}

const handleExpandCard = (index: number) => {
    if (cards.value[index].state === CardState.Collapsed) {
        cards.value[index].state = CardState.Expanded
    } else if (cards.value[index].state === CardState.Expanded) {
        cards.value[index].state = CardState.Collapsed
    }
}

const scrollToError = (id: string): void => {
    let el = document.getElementById(id);

    if (!el) return;

    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

const onSubmit = async () => {
    if (companyForm.hasErrors) {
        scrollToError(Object.keys(companyForm.errors)[0]);
    }

    emits('loading-state', true);
    await companyForm.submit().then(() => {
        emits('update-profile');
        router.push({ name: 'side-menu-company-company-list' });
    }).catch(error => {
        let errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error as Error);
        showAlertPlaceholder('danger', '', errorList);
    }).finally(() => {
        emits('loading-state', false);
    });
};

const resetForm = async () => {
    companyForm.reset();
    companyForm.setErrors({});
    await loadData(route.params.ulid as string);
};

const setCode = () => {
    companyForm.forgetError('code');
    if (companyForm.code == '_AUTO_') {
        companyForm.setData({ code: '' });
    } else {
        companyForm.setData({ code: '_AUTO_' });
    }
};

const showAlertPlaceholder = (pAlertType: 'hidden'|'danger'|'success'|'warning'|'pending'|'dark', pTitle: string, pAlertList: Record<string, Array<string>>|null) => {
  let ap: AlertPlaceholderProps = {
    alertType: pAlertType,
    title: pTitle,
    alertList: pAlertList,
  };

  emits('show-alertplaceholder', ap);
};

// #endregion

// #region Watchers
watch(
    companyForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('COMPANY_EDIT', newValue.data())
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="companyForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': companyForm.invalid('code') }">
                            {{ t('views.company.fields.code') }}
                        </FormLabel>
                        <FormInputCode v-model="companyForm.code" 
                            :class="{ 'border-danger': companyForm.invalid('code') }"
                            :placeholder="t('views.company.fields.code')" @set-auto="setCode" @change="companyForm.validate('code')" />
                        <FormErrorMessages :messages="companyForm.errors.code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': companyForm.invalid('name') }">
                            {{ t('views.company.fields.name') }}
                        </FormLabel>
                        <FormInput v-model="companyForm.name" type="text"
                            :class="{ 'border-danger': companyForm.invalid('name') }"
                            :placeholder="t('views.company.fields.name')" @change="companyForm.validate('name')" />
                        <FormErrorMessages :messages="companyForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel>
                            {{ t('views.company.fields.address') }}
                        </FormLabel>
                        <FormTextarea v-model="companyForm.address" type="text"
                            :placeholder="t('views.company.fields.address')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': companyForm.invalid('default') }">
                            {{ t('views.company.fields.default') }}
                        </FormLabel>
                        <FormSwitch class="mt-2">
                            <FormSwitch.Input v-model="companyForm.default" type="checkbox"
                                :class="{ 'border-danger': companyForm.invalid('default') }"
                                :placeholder="t('views.company.fields.default')" />
                        </FormSwitch>
                        <FormErrorMessages :messages="companyForm.errors.default" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': companyForm.invalid('status') }">
                            {{ t('views.company.fields.status') }}
                        </FormLabel>
                        <FormSelect v-model="companyForm.status"
                            :class="{ 'border-danger': companyForm.invalid('status') }"
                            @change="companyForm.validate('status')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="companyForm.errors.status" />
                    </div>
                </div>
            </template>
            <template #card-items-button>
                <div class="flex gap-4">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
                        :disabled="companyForm.validating || companyForm.hasErrors">
                        <Lucide v-if="companyForm.validating" icon="Loader" class="animate-spin" />
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