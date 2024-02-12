<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import WarehouseService from "../../services/WarehouseService";
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
    FormErrorMessages
} from "../../base-components/Form";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "../../types/enums/CardState";
import Button from "../../base-components/Button";
import { ViewMode } from "../../types/enums/ViewMode";
import { debounce } from "lodash";
import Lucide from "../../base-components/Lucide";
import { useSelectedUserLocationStore } from "../../stores/user-location";
import { useRouter } from "vue-router";
import { ErrorCode } from "../../types/enums/ErrorCode";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const warehouseServices = new WarehouseService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();

const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

//#region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'views.warehouse.field_groups.company_info', state: CardState.Expanded },
    { title: 'views.warehouse.field_groups.branch_info', state: CardState.Expanded },
    { title: 'views.warehouse.field_groups.warehouse_data', state: CardState.Expanded },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const statusDDL = ref<Array<DropDownOption> | null>(null);

const warehouseForm = warehouseServices.useWarehouseCreateForm();
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emits('mode-state', ViewMode.FORM_CREATE);

    if (!isUserLocationSelected.value) {
        router.push({ name: 'side-menu-error-code', params: { code: ErrorCode.USERLOCATION_REQUIRED } });
    }

    getDDL();

    setCompanyIdData();
    setBranchIdData();
    loadFromCache();
});
// #endregion

// #region Methods
const setCompanyIdData = () => {
    warehouseForm.setData({
        company_id: selectedUserLocation.value.company.id,
    });
};

const setBranchIdData = () => {
    warehouseForm.setData({
        branch_id: selectedUserLocation.value.branch.id,
    });
};

const loadFromCache = () => {
    let data = cacheServices.getLastEntity('WAREHOUSE_CREATE') as Record<string, unknown>;
    if (!data) return;
    warehouseForm.setData(data);
};

const getDDL = (): void => {
    dashboardServices.getStatusDDL().then((result: Array<DropDownOption> | null) => {
        statusDDL.value = result;
    });
};

const handleExpandCard = (index: number) => {
    if (cards.value[index].state === CardState.Collapsed) {
        cards.value[index].state = CardState.Expanded;
    } else if (cards.value[index].state === CardState.Expanded) {
        cards.value[index].state = CardState.Collapsed;
    }
};

const scrollToError = (id: string): void => {
    let el = document.getElementById(id);

    if (!el) return;

    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const onSubmit = async () => {
    if (warehouseForm.hasErrors) {
        scrollToError(Object.keys(warehouseForm.errors)[0]);
    }

    emits('loading-state', true);
    await warehouseForm.submit().then(() => {
        resetForm();
        router.push({ name: 'side-menu-company-warehouse-list' });
    }).catch(error => {
        console.error(error);
    }).finally(() => {
        emits('loading-state', false);
    });
};

const resetForm = () => {
    warehouseForm.reset();
    warehouseForm.setErrors({});
};
// #endregion

// #region Watchers
watch(
    warehouseForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('WAREHOUSE_CREATE', newValue.data());
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="warehouseForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
                <div class="p-5">
                    <FormLabel>
                        {{ selectedUserLocation.company.code }}
                        <br />
                        {{ selectedUserLocation.company.name }}
                    </FormLabel>
                    <FormInput type="hidden" v-model="warehouseForm.company_id" />
                </div>
            </template>
            <template #card-items-1>
                <div class="p-5">
                    <FormLabel>
                        {{ selectedUserLocation.branch.code }}
                        <br />
                        {{ selectedUserLocation.branch.name }}
                    </FormLabel>
                    <FormInput type="hidden" v-model="warehouseForm.branch_id" />
                </div>
            </template>
            <template #card-items-2>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel html-for="code" :class="{ 'text-danger': warehouseForm.invalid('code') }">
                            {{ t('views.warehouse.fields.code') }}
                        </FormLabel>
                        <FormInputCode id="code" v-model="warehouseForm.code" name="code" type="text"
                            :class="{ 'border-danger': warehouseForm.invalid('code') }"
                            :placeholder="t('views.warehouse.fields.code')" @change="warehouseForm.validate('code')" />
                        <FormErrorMessages :messages="warehouseForm.errors.code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="name" :class="{ 'text-danger': warehouseForm.invalid('name') }">
                            {{ t('views.warehouse.fields.name') }}
                        </FormLabel>
                        <FormInput id="name" v-model="warehouseForm.name" name="name" type="text"
                            :class="{ 'border-danger': warehouseForm.invalid('name') }"
                            :placeholder="t('views.warehouse.fields.name')" @change="warehouseForm.validate('name')" />
                        <FormErrorMessages :messages="warehouseForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="address">
                            {{ t('views.warehouse.fields.address') }}
                        </FormLabel>
                        <FormTextarea id="address" v-model="warehouseForm.address" name="address" type="text"
                            :placeholder="t('views.warehouse.fields.address')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="city">
                            {{ t('views.warehouse.fields.city') }}
                        </FormLabel>
                        <FormInput id="city" v-model="warehouseForm.city" name="city" type="text"
                            :placeholder="t('views.warehouse.fields.city')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="contact">
                            {{ t('views.warehouse.fields.contact') }}
                        </FormLabel>
                        <FormInput id="contact" v-model="warehouseForm.contact" name="contact" type="text"
                            :placeholder="t('views.warehouse.fields.contact')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="remarks">
                            {{ t('views.warehouse.fields.remarks') }}
                        </FormLabel>
                        <FormTextarea id="remarks" v-model="warehouseForm.remarks" name="remarks" type="text"
                            :placeholder="t('views.warehouse.fields.remarks')" rows="3" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="status" :class="{ 'text-danger': warehouseForm.invalid('status') }">
                            {{ t('views.warehouse.fields.status') }}
                        </FormLabel>
                        <FormSelect id="status" v-model="warehouseForm.status" name="status"
                            :class="{ 'border-danger': warehouseForm.invalid('status') }"
                            @change="warehouseForm.validate('status')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="warehouseForm.errors.status" />
                    </div>
                </div>
            </template>
            <template #card-items-button>
                <div class="flex gap-4">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
                        :disabled="warehouseForm.validating || warehouseForm.hasErrors">
                        <Lucide v-if="warehouseForm.validating" icon="Loader" class="animate-spin" />
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
