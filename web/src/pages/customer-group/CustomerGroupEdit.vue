<script setup lang="ts">
// #region Imports
import { onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { convertErrorTypeToAlertListType } from "@/utils/helper";
import { useRoute, useRouter } from "vue-router";
import CustomerGroupService from "@/services/CustomerGroupService";
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
import { CustomerGroup } from "@/types/models/CustomerGroup";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const route = useRoute();
const router = useRouter();

const customerGroupServices = new CustomerGroupService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'views.customer_group.field_groups.general_information', state: CardState.Expanded },
    { title: 'views.customer_group.field_groups.credit_limit', state: CardState.Expanded },
    { title: 'views.customer_group.field_groups.pricing_and_points', state: CardState.Expanded },
    { title: 'views.customer_group.field_groups.rounding_rules', state: CardState.Expanded },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const statusDDL = ref<Array<DropDownOption> | null>(null);
const paymentTermTypeDDL = ref<Array<DropDownOption> | null>(null);
const roundOnDDL = ref<Array<DropDownOption> | null>(null);

const customerGroupForm = customerGroupServices.useCustomerGroupEditForm(route.params.ulid as string);
// #endregion

// #region Computed
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emits('mode-state', ViewMode.FORM_EDIT);
    await Promise.all([
        getDDL(),
        loadData(route.params.ulid as string)
    ]);
});
// #endregion

// #region Methods
const loadData = async (ulid: string) => {
    emits('loading-state', true);
    let response: ServiceResponse<CustomerGroup | null> = await customerGroupServices.read(ulid);

    if (response && response.data) {
        customerGroupForm.setData({
            company_id: response.data.company.id,
            code: response.data.code,
            name: response.data.name,
            remarks: response.data.remarks,
            max_open_invoice: response.data.max_open_invoice,
            max_outstanding_invoice: response.data.max_outstanding_invoice,
            max_invoice_age: response.data.max_invoice_age,
            payment_term_type: response.data.payment_term_type,
            payment_term: response.data.payment_term,
            selling_point: response.data.selling_point,
            selling_point_multiple: response.data.selling_point_multiple,
            sell_at_cost: response.data.sell_at_cost,
            price_markup_percent: response.data.price_markup_percent,
            price_markup_nominal: response.data.price_markup_nominal,
            price_markdown_percent: response.data.price_markdown_percent,
            price_markdown_nominal: response.data.price_markdown_nominal,
            rounding_type: response.data.rounding_type,
            rounding_digit: response.data.rounding_digit,
        });
    }
    emits('loading-state', false);
}

const getDDL = async (): Promise<void> => {
    const [status, paymentTerm, rounding] = await Promise.all([
        dashboardServices.getStatusDDL(),
        dashboardServices.getPaymentTermTypesDDL(),
        dashboardServices.getRoundingTypesDDL()
    ]);
    statusDDL.value = status;
    paymentTermTypeDDL.value = paymentTerm;
    roundOnDDL.value = rounding;
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
    if (customerGroupForm.hasErrors) {
        scrollToError(Object.keys(customerGroupForm.errors)[0]);
    }

    emits('loading-state', true);
    await customerGroupForm.submit().then(() => {
        emits('update-profile');
        router.push({ name: 'side-menu-customer-group-list' });
    }).catch(error => {
        let errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error as Error);
        showAlertPlaceholder('danger', '', errorList);
    }).finally(() => {
        emits('loading-state', false);
    });
};

const resetForm = async () => {
    customerGroupForm.reset();
    customerGroupForm.setErrors({});
    await loadData(route.params.ulid as string);
};

const setCode = () => {
    customerGroupForm.forgetError('code');
    if (customerGroupForm.code == '_AUTO_') {
        customerGroupForm.setData({ code: '' });
    } else {
        customerGroupForm.setData({ code: '_AUTO_' });
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
    customerGroupForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('CUSTOMER_GROUP_EDIT', newValue.data())
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="customerGroupForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': customerGroupForm.invalid('code') }">
                            {{ t('views.customer_group.fields.code') }}
                        </FormLabel>
                        <FormInputCode v-model="customerGroupForm.code"
                            :class="{ 'border-danger': customerGroupForm.invalid('code') }"
                            :placeholder="t('views.customer_group.fields.code')" @set-auto="setCode"
                            @change="customerGroupForm.validate('code')" />
                        <FormErrorMessages :messages="customerGroupForm.errors.code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': customerGroupForm.invalid('name') }">
                            {{ t('views.customer_group.fields.name') }}
                        </FormLabel>
                        <FormInput v-model="customerGroupForm.name" type="text"
                            :class="{ 'border-danger': customerGroupForm.invalid('name') }"
                            :placeholder="t('views.customer_group.fields.name')"
                            @change="customerGroupForm.validate('name')" />
                        <FormErrorMessages :messages="customerGroupForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel>{{ t('views.customer_group.fields.remarks') }}</FormLabel>
                        <FormTextarea v-model="customerGroupForm.remarks"
                            :placeholder="t('views.customer_group.fields.remarks')"
                            @change="customerGroupForm.validate('remarks')" />
                        <FormErrorMessages :messages="customerGroupForm.errors.remarks" />
                    </div>
                </div>
            </template>

            <template #card-items-1>
                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <FormLabel :class="{ 'text-danger': customerGroupForm.invalid('max_open_invoice') }">
                            {{ t('views.customer_group.fields.max_open_invoice') }}
                        </FormLabel>
                        <FormInput v-model="customerGroupForm.max_open_invoice" type="number"
                            @change="customerGroupForm.validate('max_open_invoice')" />
                        <FormErrorMessages :messages="customerGroupForm.errors.max_open_invoice" />
                    </div>
                    <div>
                        <FormLabel :class="{ 'text-danger': customerGroupForm.invalid('max_outstanding_invoice') }">
                            {{ t('views.customer_group.fields.max_outstanding_invoice') }}
                        </FormLabel>
                        <FormInput v-model="customerGroupForm.max_outstanding_invoice" type="number"
                            @change="customerGroupForm.validate('max_outstanding_invoice')" />
                        <FormErrorMessages :messages="customerGroupForm.errors.max_outstanding_invoice" />
                    </div>
                    <div>
                        <FormLabel :class="{ 'text-danger': customerGroupForm.invalid('max_invoice_age') }">
                            {{ t('views.customer_group.fields.max_invoice_age') }}
                        </FormLabel>
                        <FormInput v-model="customerGroupForm.max_invoice_age" type="number"
                            @change="customerGroupForm.validate('max_invoice_age')" />
                        <FormErrorMessages :messages="customerGroupForm.errors.max_invoice_age" />
                    </div>
                    <div>
                        <FormLabel :class="{ 'text-danger': customerGroupForm.invalid('payment_term_type') }">
                            {{ t('views.customer_group.fields.payment_term_type') }}
                        </FormLabel>
                        <FormSelect v-model="customerGroupForm.payment_term_type"
                            @change="customerGroupForm.validate('payment_term_type')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="pt in paymentTermTypeDDL" :key="pt.code" :value="pt.code">{{ t(pt.name) }}
                            </option>
                        </FormSelect>
                        <FormErrorMessages :messages="customerGroupForm.errors.payment_term_type" />
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <FormLabel :class="{ 'text-danger': customerGroupForm.invalid('payment_term') }">
                            {{ t('views.customer_group.fields.payment_term') }}
                        </FormLabel>
                        <FormInput v-model="customerGroupForm.payment_term" type="number"
                            @change="customerGroupForm.validate('payment_term')" />
                        <FormErrorMessages :messages="customerGroupForm.errors.payment_term" />
                    </div>
                </div>
            </template>

            <template #card-items-2>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel>{{ t('views.customer_group.fields.sell_at_cost') }}</FormLabel>
                        <FormSwitch class="mt-2">
                            <FormSwitch.Input v-model="customerGroupForm.sell_at_cost" type="checkbox" />
                        </FormSwitch>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <FormLabel :class="{ 'text-danger': customerGroupForm.invalid('price_markup_percent') }">
                                {{ t('views.customer_group.fields.price_markup_percent') }}
                            </FormLabel>
                            <FormInput v-model="customerGroupForm.price_markup_percent" type="number"
                                @change="customerGroupForm.validate('price_markup_percent')" />
                            <FormErrorMessages :messages="customerGroupForm.errors.price_markup_percent" />
                        </div>
                        <div>
                            <FormLabel :class="{ 'text-danger': customerGroupForm.invalid('price_markup_nominal') }">
                                {{ t('views.customer_group.fields.price_markup_nominal') }}
                            </FormLabel>
                            <FormInput v-model="customerGroupForm.price_markup_nominal" type="number"
                                @change="customerGroupForm.validate('price_markup_nominal')" />
                            <FormErrorMessages :messages="customerGroupForm.errors.price_markup_nominal" />
                        </div>
                        <div>
                            <FormLabel :class="{ 'text-danger': customerGroupForm.invalid('price_markdown_percent') }">
                                {{ t('views.customer_group.fields.price_markdown_percent') }}
                            </FormLabel>
                            <FormInput v-model="customerGroupForm.price_markdown_percent" type="number"
                                @change="customerGroupForm.validate('price_markdown_percent')" />
                            <FormErrorMessages :messages="customerGroupForm.errors.price_markdown_percent" />
                        </div>
                        <div>
                            <FormLabel :class="{ 'text-danger': customerGroupForm.invalid('price_markdown_nominal') }">
                                {{ t('views.customer_group.fields.price_markdown_nominal') }}
                            </FormLabel>
                            <FormInput v-model="customerGroupForm.price_markdown_nominal" type="number"
                                @change="customerGroupForm.validate('price_markdown_nominal')" />
                            <FormErrorMessages :messages="customerGroupForm.errors.price_markdown_nominal" />
                        </div>
                    </div>
                </div>
            </template>

            <template #card-items-3>
                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <FormLabel :class="{ 'text-danger': customerGroupForm.invalid('rounding_type') }">
                            {{ t('views.customer_group.fields.rounding_type') }}
                        </FormLabel>
                        <FormSelect v-model="customerGroupForm.rounding_type"
                            @change="customerGroupForm.validate('rounding_type')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="ro in roundOnDDL" :key="ro.code" :value="ro.code">{{ t(ro.name) }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="customerGroupForm.errors.rounding_type" />
                    </div>
                    <div>
                        <FormLabel :class="{ 'text-danger': customerGroupForm.invalid('rounding_digit') }">
                            {{ t('views.customer_group.fields.rounding_digit') }}
                        </FormLabel>
                        <FormInput v-model="customerGroupForm.rounding_digit" type="number"
                            :class="{ 'border-danger': customerGroupForm.invalid('rounding_digit') }"
                            @change="customerGroupForm.validate('rounding_digit')" />
                        <FormErrorMessages :messages="customerGroupForm.errors.rounding_digit" />
                    </div>
                </div>
            </template>

            <template #card-items-button>
                <div class="flex gap-4">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
                        :disabled="customerGroupForm.validating || customerGroupForm.hasErrors">
                        <Lucide v-if="customerGroupForm.validating" icon="Loader" class="animate-spin" />
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