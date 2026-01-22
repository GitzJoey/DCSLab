<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import CustomerService from "@/services/CustomerService";
import CustomerGroupService from "@/services/CustomerGroupService";
import DashboardService from "@/services/DashboardService";
import CacheService from "@/services/CacheService";
import { DropDownOption } from "@/types/models/DropDownOption";
import { TwoColumnsLayout } from "@/components/Base/Form/FormLayout";
import {
  FormInput,
  FormLabel,
  FormTextarea,
  FormSelect,
  FormInputCode,
  FormSwitch,
  FormErrorMessages,
  FormTomSelect,
} from "@/components/Base/Form";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "@/types/enums/CardState";
import Button from "@/components/Base/Button";
import { ViewMode } from "@/types/enums/ViewMode";
import { debounce } from "lodash";
import Lucide from "@/components/Base/Lucide";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { useRouter } from "vue-router";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
import { ErrorCode } from "@/types/enums/ErrorCode";
import { CustomerGroup } from "@/types/models/CustomerGroup";
import { Collection } from "@/types/resources/Collection";
import { AxiosError, isAxiosError } from "axios";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const customerService = new CustomerService();
const customerGroupService = new CustomerGroupService();
const cacheServices = new CacheService();
const dashboardServices = new DashboardService();
// #endregion

// #region Props, Emits
const emits = defineEmits([
  "mode-state",
  "loading-state",
  "update-profile",
  "show-alertplaceholder",
]);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: "views.customer.field_groups.general_information",
    state: CardState.Expanded,
  },
  {
    title: "views.customer.field_groups.credit_limit",
    state: CardState.Expanded,
  },
  {
    title: "views.customer.field_groups.tax_information",
    state: CardState.Expanded,
  },
  { title: "", state: CardState.Hidden, id: "button" },
]);

const statusDDL = ref<Array<DropDownOption> | null>(null);
const customerGroupDDL = ref<Array<DropDownOption> | null>(null);
const paymentTermTypeDDL = ref<Array<DropDownOption> | null>(null);

const isDDLLoading = ref<boolean>(false);
const selectedGroupName = ref<string>("");

const customerForm = customerService.useCustomerCreateForm();
// #endregion

// #region Computed
const isUserLocationSelected = computed(
  () => selectedUserLocationStore.isUserLocationSelected
);
const selectedUserLocation = computed(
  () => selectedUserLocationStore.selectedUserLocation
);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
  emits("mode-state", ViewMode.FORM_CREATE);
  getDDL();
  loadFromCache();
  if (!isUserLocationSelected.value) {
    router.push({
      name: "side-menu-error-code",
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
  }
  setCompanyIdData();
});
// #endregion

// #region Methods
const setCompanyIdData = () => {
  customerForm.setData({
    company_id: selectedUserLocation.value.company.id,
  });
};

const loadFromCache = () => {
  let data = cacheServices.getLastEntity("CUSTOMER_CREATE") as Record<
    string,
    unknown
  >;
  if (!data) return;
  customerForm.setData(data);
};

const getCustomerGroupDDL = async (search = ""): Promise<void> => {
  const result = await customerGroupService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: undefined,
    refresh: false,
    limit: 10,
  });

  if (result.success && result.data) {
    const collection = result.data as Collection<Array<CustomerGroup>>;
    customerGroupDDL.value = collection.data.map((item: CustomerGroup) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const getDDL = async (): Promise<void> => {
  isDDLLoading.value = true;

  try {
    await Promise.all([
      (async () => {
        const result = await dashboardServices.getStatusDDL();
        statusDDL.value = result;
      })(),
      (async () => {
        const result = await dashboardServices.getPaymentTermTypesDDL();
        paymentTermTypeDDL.value = result;
      })(),
      getCustomerGroupDDL(),
    ]);
  } catch (error) {
    console.error("Error loading DDLs:", error);
  } finally {
    isDDLLoading.value = false;
  }
};

const updateGroupName = (newGroupId?: string) => {
  const groupId = newGroupId ?? (customerForm.group_id as string);
  if (!groupId) {
    selectedGroupName.value = "";
    return;
  }
  const group = customerGroupDDL.value?.find((g) => g.code === groupId);
  selectedGroupName.value = group ? group.name : "";
};

const clearGroup = () => {
  customerForm.setData({ group_id: "" });
  selectedGroupName.value = "";
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
  el.scrollIntoView({ behavior: "smooth", block: "center" });
};

const onSubmit = async () => {
  if (customerForm.hasErrors) {
    scrollToError(Object.keys(customerForm.errors)[0]);
  }
  emits("loading-state", true);
  await customerForm
    .submit()
    .then(() => {
      resetForm();
      emits("update-profile");
      router.push({ name: "side-menu-customer-list" });
    })
    .catch((error) => {
      const errorList: Record<string, Array<string>> =
        convertErrorTypeToAlertListType(error);
      showAlertPlaceholder("danger", "", errorList);
    })
    .finally(() => {
      emits("loading-state", false);
    });
};

const resetForm = () => {
  customerForm.reset();
  customerForm.setErrors({});
};

const setCode = () => {
  customerForm.forgetError("code");
  if (customerForm.code == "_AUTO_") {
    customerForm.setData({ code: "" });
  } else {
    customerForm.setData({ code: "_AUTO_" });
  }
};

const showAlertPlaceholder = (
  pAlertType: "hidden" | "danger" | "success" | "warning" | "pending" | "dark",
  pTitle: string,
  pAlertList: Record<string, Array<string>> | null
) => {
  let ap: AlertPlaceholderProps = {
    alertType: pAlertType,
    title: pTitle,
    alertList: pAlertList,
  };
  emits("show-alertplaceholder", ap);
};

const convertErrorTypeToAlertListType = (error: unknown) => {
  const record: Record<string, Array<string>> = {};

  const anyError = error as any;
  const response = isAxiosError(error)
    ? (error as AxiosError).response
    : anyError?.response;

  if (response && response.data) {
    const data = response.data as any;

    if (data.errors && typeof data.errors === "object") {
      for (const key of Object.keys(data.errors)) {
        const value = data.errors[key];

        if (Array.isArray(value)) {
          record[key] = value;
        } else if (value !== undefined && value !== null) {
          record[key] = [String(value)];
        }
      }

      return record;
    }

    if (data.message) {
      record.error = [String(data.message)];
      return record;
    }
  }

  if (error instanceof Error && error.message) {
    record.error = [error.message];
  } else {
    record.error = ["Unknown error"];
  }

  return record;
};
// #endregion

// #region Watchers
watch(
  customerForm,
  debounce((newValue): void => {
    cacheServices.setLastEntity("CUSTOMER_CREATE", newValue.data());
  }, 500),
  { deep: true }
);
// #endregion
</script>

<template>
  <form id="customerForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout
      :cards="cards"
      :using-side-tab="false"
      @handle-expand-card="handleExpandCard"
    >
      <template #card-items-0>
        <div class="p-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
            <div class="pb-4">
              <FormLabel
                :class="{ 'text-danger': customerForm.invalid('code') }"
              >
                {{ t("views.customer.fields.code") }}
              </FormLabel>
              <FormInputCode
                v-model="customerForm.code"
                :class="{ 'border-danger': customerForm.invalid('code') }"
                :placeholder="t('views.customer.fields.code')"
                @set-auto="setCode"
                @change="customerForm.validate('code')"
              />
              <FormErrorMessages :messages="customerForm.errors.code" />
            </div>
            <div class="pb-4">
              <FormLabel
                :class="{ 'text-danger': customerForm.invalid('name') }"
              >
                {{ t("views.customer.fields.name") }}
              </FormLabel>
              <FormInput
                v-model="customerForm.name"
                type="text"
                :class="{ 'border-danger': customerForm.invalid('name') }"
                :placeholder="t('views.customer.fields.name')"
                @change="customerForm.validate('name')"
              />
              <FormErrorMessages :messages="customerForm.errors.name" />
            </div>
            <div class="pb-4">
              <FormLabel
                :class="{
                  'text-danger': customerForm.invalid('group_id'),
                }"
              >
                {{ t("views.customer.fields.group") }}
              </FormLabel>
              <div>
                <div
                  v-if="customerForm.group_id && selectedGroupName"
                  class="relative"
                >
                  <div
                    class="form-control border rounded-md px-3 py-2 bg-slate-50 dark:bg-darkmode-800 text-slate-700 dark:text-slate-300"
                  >
                    {{ selectedGroupName }}
                  </div>
                  <button
                    type="button"
                    class="absolute right-3 top-2.5 text-slate-500 hover:text-danger"
                    @click="clearGroup"
                  >
                    <Lucide icon="X" class="w-4 h-4" />
                  </button>
                </div>
                <FormTomSelect
                  v-else
                  v-model="customerForm.group_id"
                  :class="{
                    'border-danger': customerForm.invalid('group_id'),
                  }"
                  @update:model-value="(val) => {
                    updateGroupName(val as string);
                    customerForm.validate('group_id');
                  }"
                  @search="getCustomerGroupDDL"
                  :options="{
                    placeholder: t('components.dropdown.placeholder'),
                  }"
                >
                  <option
                    v-for="cg in customerGroupDDL"
                    :key="cg.code"
                    :value="cg.code"
                  >
                    {{ cg.name }}
                  </option>
                </FormTomSelect>
              </div>
              <FormErrorMessages :messages="customerForm.errors.group_id" />
            </div>
            <div class="pb-4">
              <FormLabel
                :class="{ 'text-danger': customerForm.invalid('is_member') }"
              >
                {{ t("views.customer.fields.is_member") }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input
                  v-model="customerForm.is_member"
                  type="checkbox"
                  :class="{
                    'border-danger': customerForm.invalid('is_member'),
                  }"
                  :placeholder="t('views.customer.fields.is_member')"
                  @change="customerForm.validate('is_member')"
                />
              </FormSwitch>
              <FormErrorMessages :messages="customerForm.errors.is_member" />
            </div>
            <div class="pb-4">
              <FormLabel
                :class="{ 'text-danger': customerForm.invalid('zone') }"
              >
                {{ t("views.customer.fields.zone") }}
              </FormLabel>
              <FormInput
                v-model="customerForm.zone"
                type="text"
                :class="{ 'border-danger': customerForm.invalid('zone') }"
                :placeholder="t('views.customer.fields.zone')"
                @change="customerForm.validate('zone')"
              />
              <FormErrorMessages :messages="customerForm.errors.zone" />
            </div>
            <div class="pb-4">
              <FormLabel
                :class="{ 'text-danger': customerForm.invalid('status') }"
              >
                {{ t("views.customer.fields.status") }}
              </FormLabel>
              <FormSelect
                v-model="customerForm.status"
                :class="{ 'border-danger': customerForm.invalid('status') }"
                @change="customerForm.validate('status')"
              >
                <option value="0" disabled>
                  {{ t("components.dropdown.placeholder") }}
                </option>
                <option v-for="s in statusDDL" :key="s.code" :value="s.code">
                  {{ t(s.name) }}
                </option>
              </FormSelect>
              <FormErrorMessages :messages="customerForm.errors.status" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
            <div class="pb-4">
              <FormLabel
                :class="{
                  'text-danger': customerForm.invalid('max_open_invoice'),
                }"
              >
                {{ t("views.customer.fields.max_open_invoice") }}
              </FormLabel>
              <FormInput
                v-model="customerForm.max_open_invoice"
                type="number"
                :class="{
                  'border-danger': customerForm.invalid('max_open_invoice'),
                }"
                :placeholder="t('views.customer.fields.max_open_invoice')"
                @change="customerForm.validate('max_open_invoice')"
              />
              <FormErrorMessages
                :messages="customerForm.errors.max_open_invoice"
              />
            </div>
            <div class="pb-4">
              <FormLabel
                :class="{
                  'text-danger': customerForm.invalid(
                    'max_outstanding_invoice'
                  ),
                }"
              >
                {{ t("views.customer.fields.max_outstanding_invoice") }}
              </FormLabel>
              <FormInput
                v-model="customerForm.max_outstanding_invoice"
                type="number"
                :class="{
                  'border-danger': customerForm.invalid(
                    'max_outstanding_invoice'
                  ),
                }"
                :placeholder="
                  t('views.customer.fields.max_outstanding_invoice')
                "
                @change="customerForm.validate('max_outstanding_invoice')"
              />
              <FormErrorMessages
                :messages="customerForm.errors.max_outstanding_invoice"
              />
            </div>
            <div class="pb-4">
              <FormLabel
                :class="{
                  'text-danger': customerForm.invalid('max_invoice_age'),
                }"
              >
                {{ t("views.customer.fields.max_invoice_age") }}
              </FormLabel>
              <FormInput
                v-model="customerForm.max_invoice_age"
                type="number"
                :class="{
                  'border-danger': customerForm.invalid('max_invoice_age'),
                }"
                :placeholder="t('views.customer.fields.max_invoice_age')"
                @change="customerForm.validate('max_invoice_age')"
              />
              <FormErrorMessages
                :messages="customerForm.errors.max_invoice_age"
              />
            </div>
            <div class="pb-4">
              <FormLabel
                :class="{
                  'text-danger': customerForm.invalid('payment_term_type'),
                }"
              >
                {{ t("views.customer.fields.payment_term_type") }}
              </FormLabel>
              <FormSelect
                v-model="customerForm.payment_term_type"
                :class="{
                  'border-danger': customerForm.invalid('payment_term_type'),
                }"
                :placeholder="t('views.customer.fields.payment_term_type')"
                @change="customerForm.validate('payment_term_type')"
              >
                <option value="0" disabled>
                  {{ t("components.dropdown.placeholder") }}
                </option>
                <option
                  v-for="s in paymentTermTypeDDL"
                  :key="s.code"
                  :value="s.code"
                >
                  {{ t(s.name) }}
                </option>
              </FormSelect>
              <FormErrorMessages
                :messages="customerForm.errors.payment_term_type"
              />
            </div>
            <div class="pb-4">
              <FormLabel
                :class="{ 'text-danger': customerForm.invalid('payment_term') }"
              >
                {{ t("views.customer.fields.payment_term") }}
              </FormLabel>
              <FormInput
                v-model="customerForm.payment_term"
                type="number"
                :class="{
                  'border-danger': customerForm.invalid('payment_term'),
                }"
                :placeholder="t('views.customer.fields.payment_term')"
                @change="customerForm.validate('payment_term')"
              />
              <FormErrorMessages :messages="customerForm.errors.payment_term" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="p-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
            <div class="pb-4">
              <FormLabel
                :class="{
                  'text-danger': customerForm.invalid('taxable_enterprise'),
                }"
              >
                {{ t("views.customer.fields.taxable_enterprise") }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input
                  v-model="customerForm.taxable_enterprise"
                  type="checkbox"
                  :class="{
                    'border-danger': customerForm.invalid('taxable_enterprise'),
                  }"
                  :placeholder="t('views.customer.fields.taxable_enterprise')"
                  @change="customerForm.validate('taxable_enterprise')"
                />
              </FormSwitch>
              <FormErrorMessages
                :messages="customerForm.errors.taxable_enterprise"
              />
            </div>
            <div class="pb-4">
              <FormLabel
                :class="{ 'text-danger': customerForm.invalid('tax_id') }"
              >
                {{ t("views.customer.fields.tax_id") }}
              </FormLabel>
              <FormInput
                v-model="customerForm.tax_id"
                type="text"
                :class="{ 'border-danger': customerForm.invalid('tax_id') }"
                :placeholder="t('views.customer.fields.tax_id')"
                @change="customerForm.validate('tax_id')"
              />
              <FormErrorMessages :messages="customerForm.errors.tax_id" />
            </div>
            <div class="pb-4">
              <FormLabel
                :class="{ 'text-danger': customerForm.invalid('remarks') }"
              >
                {{ t("views.customer.fields.remarks") }}
              </FormLabel>
              <FormTextarea
                v-model="customerForm.remarks"
                :class="{ 'border-danger': customerForm.invalid('remarks') }"
                :placeholder="t('views.customer.fields.remarks')"
                @change="customerForm.validate('remarks')"
              />
              <FormErrorMessages :messages="customerForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-button>
        <div class="flex gap-4">
          <Button
            type="submit"
            href="#"
            variant="primary"
            class="w-28 shadow-md"
            :disabled="customerForm.validating || customerForm.hasErrors"
          >
            <Lucide
              v-if="customerForm.validating"
              icon="Loader"
              class="animate-spin"
            />
            <template v-else>
              {{ t("components.buttons.submit") }}
            </template>
          </Button>
          <Button
            type="button"
            href="#"
            variant="soft-secondary"
            class="w-28 shadow-md"
            @click="resetForm"
          >
            {{ t("components.buttons.reset") }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>
</template>
