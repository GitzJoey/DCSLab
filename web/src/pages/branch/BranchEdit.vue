<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";
import { convertErrorTypeToAlertListType } from "@/utils/helper";
import BranchService from "@/services/BranchService";
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
import { Branch } from "@/types/models/Branch";
import Button from "@/components/Base/Button";
import { debounce } from "lodash";
import Lucide from "@/components/Base/Lucide";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { ErrorCode } from "@/types/enums/ErrorCode";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const route = useRoute();

const branchServices = new BranchService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();

const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'views.branch.field_groups.company_info', state: CardState.Expanded, },
    { title: 'views.branch.field_groups.branch_data', state: CardState.Expanded, },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const statusDDL = ref<Array<DropDownOption> | null>(null);

const branchForm = branchServices.useBranchEditForm(route.params.ulid as string);
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emits('mode-state', ViewMode.FORM_EDIT);

    if (!isUserLocationSelected.value) {
        router.push({ name: 'side-menu-error-code', params: { code: ErrorCode.USERLOCATION_REQUIRED } });
    }

    await Promise.all([
        getDDL(),
        loadData(route.params.ulid as string)
    ]);
});
// #endregion

// #region Methods
const loadData = async (ulid: string) => {
    emits('loading-state', true);
    let result: ServiceResponse<Branch | null> = await branchServices.read(ulid);

    if (result.success && result.data) {
        branchForm.setData({
            company_id: result.data.company.id,
            code: result.data.code,
            name: result.data.name,
            address: result.data.address,
            city: result.data.city,
            contact: result.data.contact,
            is_main: result.data.is_main,
            remarks: result.data.remarks,
            status: result.data.status,
        });
    } else {
        router.push({ name: 'side-menu-company-branch-list' });
    }
    emits('loading-state', false);
};

const getDDL = async (): Promise<void> => {
    statusDDL.value = await dashboardServices.getStatusDDL();
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
    if (branchForm.hasErrors) {
        scrollToError(Object.keys(branchForm.errors)[0]);
    }

    emits('loading-state', true);
    await branchForm.submit().then(() => {
        emits('update-profile');
        router.push({ name: 'side-menu-company-branch-list' });
    }).catch(error => {
        let errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error as Error);
        showAlertPlaceholder('danger', '', errorList);
    }).finally(() => {
        emits('loading-state', false);
    });
};

const resetForm = async () => {
    branchForm.reset();
    branchForm.setErrors({});
    await loadData(route.params.ulid as string);
};

const setCode = () => {
    branchForm.forgetError('code');
    if (branchForm.code == '_AUTO_') {
        branchForm.setData({ code: '' });
    } else {
        branchForm.setData({ code: '_AUTO_' });
    }
};

const showAlertPlaceholder = (pAlertType: 'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark', pTitle: string, pAlertList: Record<string, Array<string>> | null) => {
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
    branchForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('BRANCH_EDIT', newValue.data())
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="branchForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
                <div class="p-5">
                    <FormLabel>
                        {{ selectedUserLocation.company.code }}
                        <br />
                        {{ selectedUserLocation.company.name }}
                    </FormLabel>
                    <FormInput type="hidden" v-model="branchForm.company_id" />
                </div>
            </template>
            <template #card-items-1>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': branchForm.invalid('code') }">
                            {{ t('views.branch.fields.code') }}
                        </FormLabel>
                        <FormInputCode v-model="branchForm.code" type="text"
                            :class="{ 'border-danger': branchForm.invalid('code') }"
                            :placeholder="t('views.branch.fields.code')" @set-auto="setCode"
                            @change="branchForm.validate('code')" />
                        <FormErrorMessages :messages="branchForm.errors.code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': branchForm.invalid('name') }">
                            {{ t('views.branch.fields.name') }}
                        </FormLabel>
                        <FormInput v-model="branchForm.name" type="text"
                            :class="{ 'border-danger': branchForm.invalid('name') }"
                            :placeholder="t('views.branch.fields.name')" @change="branchForm.validate('name')" />
                        <FormErrorMessages :messages="branchForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel>
                            {{ t('views.branch.fields.address') }}
                        </FormLabel>
                        <FormTextarea v-model="branchForm.address" type="text"
                            :placeholder="t('views.branch.fields.address')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel>
                            {{ t('views.branch.fields.city') }}
                        </FormLabel>
                        <FormInput v-model="branchForm.city" type="text" :placeholder="t('views.branch.fields.city')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel>
                            {{ t('views.branch.fields.contact') }}
                        </FormLabel>
                        <FormInput v-model="branchForm.contact" type="text"
                            :placeholder="t('views.branch.fields.contact')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': branchForm.invalid('is_main') }" class="pr-5">
                            {{ t('views.branch.fields.is_main') }}
                        </FormLabel>
                        <FormSwitch>
                            <FormSwitch.Input v-model="branchForm.is_main" type="checkbox"
                                :class="{ 'border-danger': branchForm.invalid('is_main') }"
                                :placeholder="t('views.branch.fields.is_main')"
                                @change="branchForm.validate('is_main')" />
                        </FormSwitch>
                        <FormErrorMessages :messages="branchForm.errors.is_main" />
                    </div>
                    <div class="pb-4">
                        <FormLabel>
                            {{ t('views.branch.fields.remarks') }}
                        </FormLabel>
                        <FormTextarea v-model="branchForm.remarks" type="text"
                            :placeholder="t('views.branch.fields.remarks')" rows="3" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': branchForm.invalid('status') }">
                            {{ t('views.branch.fields.status') }}
                        </FormLabel>
                        <FormSelect v-model="branchForm.status"
                            :class="{ 'border-danger': branchForm.invalid('status') }"
                            @change="branchForm.validate('status')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="branchForm.errors.status" />
                    </div>
                </div>
            </template>
            <template #card-items-button>
                <div class="flex gap-4">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
                        :disabled="branchForm.validating || branchForm.hasErrors">
                        <Lucide v-if="branchForm.validating" icon="Loader" class="animate-spin" />
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