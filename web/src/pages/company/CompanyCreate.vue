<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import CompanyService from "../../services/CompanyService";
import DashboardService from "../../services/DashboardService";
import CacheService from "../../services/CacheService";
import { DropDownOption } from "../../types/models/DropDownOption";
import { TwoColumnsLayout } from "../../base-components/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormTextarea,
    FormSelect,
    FormInputCode,
    FormSwitch,
    FormErrorMessages
} from "../../base-components/Form";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "../../types/enums/CardState";
import Button from "../../base-components/Button";
import { ViewMode } from "../../types/enums/ViewMode";
import { debounce } from "lodash";
import Lucide from "../../base-components/Lucide";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();

const companyServices = new CompanyService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();
// #endregion

// #region Props, Emits
const emit = defineEmits(['mode-state', 'loading-state']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'Company Information', state: CardState.Expanded, },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const statusDDL = ref<Array<DropDownOption> | null>(null);

const companyForm = companyServices.useCompanyCreateForm();
// #endregion

// #region Computed
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emit('mode-state', ViewMode.FORM_CREATE);
    getDDL();
});
// #endregion

// #region Methods
const getDDL = (): void => {
    dashboardServices.getStatusDDL().then((result: Array<DropDownOption> | null) => {
        statusDDL.value = result;
    });
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
};

const resetForm = () => {
    companyForm.reset();
}
// #endregion

// #region Watchers
watch(
    companyForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('COMPANY_CREATE', newValue.data())
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
                        <FormLabel html-for="code" :class="{ 'text-danger': companyForm.invalid('code') }">
                            {{ t('views.company.fields.code') }}
                        </FormLabel>
                        <FormInputCode id="code" v-model="companyForm.code" name="code"
                            :class="{ 'border-danger': companyForm.invalid('code') }"
                            :placeholder="t('views.company.fields.code')" @change="companyForm.validate('code')" />
                        <FormErrorMessages :messages="companyForm.errors.code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="name" :class="{ 'text-danger': companyForm.invalid('name') }">
                            {{ t('views.company.fields.name') }}
                        </FormLabel>
                        <FormInput id="name" v-model="companyForm.name" name="name" type="text"
                            :class="{ 'border-danger': companyForm.invalid('name') }"
                            :placeholder="t('views.company.fields.name')" @change="companyForm.validate('name')" />
                        <FormErrorMessages :messages="companyForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="address">
                            {{ t('views.company.fields.address') }}
                        </FormLabel>
                        <FormTextarea id="address" v-model="companyForm.address" name="address" type="text"
                            :placeholder="t('views.company.fields.address')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="default" :class="{ 'text-danger': companyForm.invalid('default') }">
                            {{ t('views.company.fields.default') }}
                        </FormLabel>
                        <FormSwitch class="mt-2">
                            <FormSwitch.Input id="default" v-model="companyForm.default" name="default" type="checkbox"
                                :class="{ 'border-danger': companyForm.invalid('default') }"
                                :placeholder="t('views.company.fields.default')" />
                        </FormSwitch>
                        <FormErrorMessages :messages="companyForm.errors.default" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="status" :class="{ 'text-danger': companyForm.invalid('status') }">
                            {{ t('views.company.fields.status') }}
                        </FormLabel>
                        <FormSelect id="status" v-model="companyForm.status" name="status"
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