<script setup lang="ts">
// #region Imports
import { onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { convertErrorTypeToAlertListType } from "@/utils/helper";
import { useRoute, useRouter } from "vue-router";
import SupplierService from "@/services/SupplierService";
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
    FormCheck,
} from "@/components/Base/Form";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "@/types/enums/CardState";
import { DropDownOption } from "@/types/models/DropDownOption";
import { ServiceResponse } from "@/types/services/ServiceResponse";
import { ViewMode } from "@/types/enums/ViewMode";
import Button from "@/components/Base/Button";
import { debounce } from "lodash";
import Lucide from "@/components/Base/Lucide";
import { Supplier } from "@/types/models/Supplier";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
import { storeToRefs } from "pinia";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { ErrorCode } from "@/types/enums/ErrorCode";
// #endregion

// #region Declarations
const { t } = useI18n();
const route = useRoute();
const router = useRouter();

const supplierServices = new SupplierService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();

const selectedUserLocationStore = useSelectedUserLocationStore();
const { isUserLocationSelected } = storeToRefs(selectedUserLocationStore);

const form = supplierServices.useSupplierEditForm(route.params.ulid as string);
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alert-placeholder']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    {
        title: "views.supplier.field_groups.general",
        state: CardState.Expanded,
        id: "general",
    },
    {
        title: "views.supplier.field_groups.finance",
        state: CardState.Expanded,
        id: "finance",
    },
    {
        title: "",
        state: CardState.Hidden,
        id: "button",
    },
]);

const statusDDL = ref<Array<DropDownOption> | null>(null);
const paymentTermTypeDDL = ref<Array<DropDownOption> | null>(null);
const isDDLLoading = ref<boolean>(false);
// #endregion

// #region Computed
// #endregion

// #region Methods
const loadData = async (ulid: string) => {
    emits('loading-state', true);
    let result: ServiceResponse<Supplier | null> = await supplierServices.read(ulid);

    if (result.success && result.data) {
        form.setData({
            company_id: result.data.company.id,
            code: result.data.code,
            name: result.data.name,
            address: result.data.address,
            city: result.data.city,
            payment_term_type: result.data.payment_term_type,
            payment_term: result.data.payment_term,
            taxable_enterprise: result.data.taxable_enterprise,
            tax_id: result.data.tax_id,
            status: result.data.status,
            remarks: result.data.remarks,
        });
    } else {
        router.push({ name: 'side-menu-supplier' });
    }
    emits('loading-state', false);
};

const getDDL = async (): Promise<void> => {
    isDDLLoading.value = true;
    try {
        await Promise.all([
            (async () => {
                const result = await dashboardServices.getStatusDDL(false);
                statusDDL.value = result;
            })(),
            (async () => {
                const result = await dashboardServices.getPaymentTermTypesDDL();
                paymentTermTypeDDL.value = result;
            })(),
        ]);
    } catch (error) {
        console.error("Error loading DDLs:", error);
    } finally {
        isDDLLoading.value = false;
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
    if (form.hasErrors) {
        scrollToError(Object.keys(form.errors)[0]);
    }

    emits('loading-state', true);
    await form.submit().then(() => {
        showAlertPlaceholder('hidden', '', null);
        emits('update-profile');
        router.push({ name: 'side-menu-supplier' });
    }).catch(error => {
        let errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error);
        showAlertPlaceholder('danger', '', errorList);
    }).finally(() => {
        emits('loading-state', false);
    });
};

const resetForm = async () => {
    form.reset();
    form.setErrors({});
    await loadData(route.params.ulid as string);
};

const setCode = () => {
    form.forgetError('code');
    if (form.code == '_AUTO_') {
        form.setData({ code: '' });
    } else {
        form.setData({ code: '_AUTO_' });
    }
};

const showAlertPlaceholder = (pAlertType: 'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark', pTitle: string, pAlertList: Record<string, Array<string>> | null) => {
    let ap: AlertPlaceholderProps = {
        alertType: pAlertType,
        title: pTitle,
        alertList: pAlertList,
    };

    emits('show-alert-placeholder', ap);
};
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    if (!isUserLocationSelected.value) {
        router.push({ name: 'side-menu-error-code', params: { code: ErrorCode.USERLOCATION_REQUIRED } });
        return;
    }

    emits('mode-state', ViewMode.FORM_EDIT);
    await getDDL();

    await loadData(route.params.ulid as string);
});
// #endregion

// #region Watchers
watch(
    form,
    debounce((newValue): void => {
        cacheServices.setLastEntity('SUPPLIER_EDIT', newValue.data())
    }, 500),
    { deep: true }
);
// #endregion

</script>

<template>
    <form id="supplierForm" @submit.prevent="onSubmit">
        <!-- Kita panggil Layout 2 Kolom -->
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <!-- Slot untuk Card General -->
            <template #card-items-general>
                <div class="p-5">
                    <!-- Code -->
                    <div class="mt-3">
                        <FormLabel htmlFor="code">
                            {{ t("views.supplier.fields.code") }}
                            <span class="text-danger">*</span>
                        </FormLabel>
                        <FormInputCode id="code" v-model="form.code" :placeholder="t('views.supplier.fields.code')"
                            :class="{ 'border-danger': form.invalid('code') }" @set-auto="setCode"
                            @change="form.validate('code')" />
                        <FormErrorMessages :messages="form.errors.code" />
                    </div>

                    <!-- Name -->
                    <div class="mt-3">
                        <FormLabel htmlFor="name">
                            {{ t("views.supplier.fields.name") }}
                            <span class="text-danger">*</span>
                        </FormLabel>
                        <FormInput id="name" v-model="form.name" type="text"
                            :placeholder="t('views.supplier.fields.name')"
                            :class="{ 'border-danger': form.invalid('name') }" @change="form.validate('name')" />
                        <FormErrorMessages :messages="form.errors.name" />
                    </div>

                    <!-- Address -->
                    <div class="mt-3">
                        <FormLabel htmlFor="address">
                            {{ t("views.supplier.fields.address") }}
                        </FormLabel>
                        <FormTextarea id="address" v-model="form.address"
                            :placeholder="t('views.supplier.fields.address')"
                            :class="{ 'border-danger': form.invalid('address') }" @change="form.validate('address')" />
                        <FormErrorMessages :messages="form.errors.address" />
                    </div>

                    <!-- City -->
                    <div class="mt-3">
                        <FormLabel htmlFor="city">
                            {{ t("views.supplier.fields.city") }}
                        </FormLabel>
                        <FormInput id="city" v-model="form.city" type="text"
                            :placeholder="t('views.supplier.fields.city')"
                            :class="{ 'border-danger': form.invalid('city') }" @change="form.validate('city')" />
                        <FormErrorMessages :messages="form.errors.city" />
                    </div>

                    <!-- Status -->
                    <div class="mt-3">
                        <FormLabel htmlFor="status">
                            {{ t("views.supplier.fields.status") }}
                            <span class="text-danger">*</span>
                        </FormLabel>
                        <FormSelect id="status" v-model="form.status"
                            :class="{ 'border-danger': form.invalid('status') }" @change="form.validate('status')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="form.errors.status" />
                    </div>

                    <!-- Remarks -->
                    <div class="mt-3">
                        <FormLabel htmlFor="remarks">
                            {{ t("views.supplier.fields.remarks") }}
                        </FormLabel>
                        <FormTextarea id="remarks" v-model="form.remarks"
                            :placeholder="t('views.supplier.fields.remarks')"
                            :class="{ 'border-danger': form.invalid('remarks') }" @change="form.validate('remarks')" />
                        <FormErrorMessages :messages="form.errors.remarks" />
                    </div>
                </div>
            </template>

            <!-- Slot untuk Card Finance -->
            <template #card-items-finance>
                <div class="p-5">
                    <!-- Payment Term Type -->
                    <div class="mt-3">
                        <FormLabel htmlFor="payment_term_type">
                            {{ t("views.supplier.fields.payment_term_type") }}
                        </FormLabel>
                        <FormSelect id="payment_term_type" v-model="form.payment_term_type"
                            :class="{ 'border-danger': form.invalid('payment_term_type') }"
                            @change="form.validate('payment_term_type')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in paymentTermTypeDDL" :key="c.code" :value="c.code">{{ t(c.name) }}
                            </option>
                        </FormSelect>
                        <FormErrorMessages :messages="form.errors.payment_term_type" />
                    </div>

                    <!-- Payment Term -->
                    <div class="mt-3">
                        <FormLabel htmlFor="payment_term">
                            {{ t("views.supplier.fields.payment_term") }}
                        </FormLabel>
                        <FormInput id="payment_term" v-model="form.payment_term" type="number"
                            :placeholder="t('views.supplier.fields.payment_term')"
                            :class="{ 'border-danger': form.invalid('payment_term') }"
                            @change="form.validate('payment_term')" />
                        <FormErrorMessages :messages="form.errors.payment_term" />
                    </div>

                    <!-- Taxable Enterprise -->
                    <div class="mt-3">
                        <FormLabel htmlFor="taxable_enterprise">
                            {{ t("views.supplier.fields.taxable_enterprise") }}
                        </FormLabel>
                        <FormSwitch class="mt-2">
                            <FormSwitch.Input id="taxable_enterprise" v-model="form.taxable_enterprise" type="checkbox"
                                :class="{ 'border-danger': form.invalid('taxable_enterprise') }" />
                        </FormSwitch>
                        <FormErrorMessages :messages="form.errors.taxable_enterprise" />
                    </div>

                    <!-- Tax ID (NPWP) -->
                    <div class="mt-3">
                        <FormLabel htmlFor="tax_id">
                            {{ t("views.supplier.fields.tax_id") }}
                        </FormLabel>
                        <FormInput id="tax_id" v-model="form.tax_id" type="text"
                            :placeholder="t('views.supplier.fields.tax_id')"
                            :class="{ 'border-danger': form.invalid('tax_id') }" @change="form.validate('tax_id')" />
                        <FormErrorMessages :messages="form.errors.tax_id" />
                    </div>
                </div>
            </template>

            <!-- Slot untuk Tombol -->
            <template #card-items-button>
                <div class="flex gap-4">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
                        :disabled="form.validating || form.hasErrors">
                        <Lucide v-if="form.validating" icon="Loader" class="animate-spin" />
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
