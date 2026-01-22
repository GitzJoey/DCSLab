<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import { isAxiosError, AxiosError } from "axios";
import InvestorService from "@/services/InvestorService";
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
} from "@/components/Base/Form";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "@/types/enums/CardState";
import Button from "@/components/Base/Button";
import { ViewMode } from "@/types/enums/ViewMode";
import { debounce } from "lodash";
import Lucide from "@/components/Base/Lucide";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { useRouter } from "vue-router";
import { ErrorCode } from "@/types/enums/ErrorCode";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const investorServices = new InvestorService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();

const selectedUserLocationStore = useSelectedUserLocationStore();
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
    title: "views.investor.field_groups.company_info",
    state: CardState.Expanded,
  },
  {
    title: "views.investor.field_groups.investor_data",
    state: CardState.Expanded,
  },
  { title: "", state: CardState.Hidden, id: "button" },
]);

const statusDDL = ref<Array<DropDownOption> | null>(null);

const investorForm = investorServices.useInvestorCreateForm();
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

  if (!isUserLocationSelected.value) {
    router.push({
      name: "side-menu-error-code",
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
  }

  await Promise.all([getDDL()]);

  loadFromCache();
  setCompanyIdData();
});
// #endregion

// #region Methods
const setCompanyIdData = () => {
  investorForm.setData({
    company_id: selectedUserLocation.value.company.id,
  });
};

const loadFromCache = () => {
  let data = cacheServices.getLastEntity("INVESTOR_CREATE") as Record<
    string,
    unknown
  >;
  if (!data) return;
  investorForm.setData(data);
};

const getDDL = async (): Promise<void> => {
    const result = await dashboardServices.getStatusDDL();
    if (result) {
        statusDDL.value = result;
    }
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
  if (investorForm.hasErrors) {
    scrollToError(Object.keys(investorForm.errors)[0]);
  }

  emits("loading-state", true);
  await investorForm
    .submit()
    .then(() => {
      resetForm();
      emits("update-profile");
      router.push({ name: "side-menu-company-investor-list" });
    })
    .catch((error) => {
      let errorList: Record<
        string,
        Array<string>
      > = convertErrorTypeToAlertListType(error);
      showAlertPlaceholder("danger", "", errorList);
    })
    .finally(() => {
      emits("loading-state", false);
    });
};

const resetForm = () => {
  investorForm.reset();
  investorForm.setErrors({});
};

const setCode = () => {
  investorForm.forgetError("code");
  if (investorForm.code == "_AUTO_") {
    investorForm.setData({ code: "" });
  } else {
    investorForm.setData({ code: "_AUTO_" });
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
  investorForm,
  debounce((newValue): void => {
    cacheServices.setLastEntity("INVESTOR_CREATE", newValue.data());
  }, 500),
  { deep: true }
);
// #endregion
</script>

<template>
  <form id="investorForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout
      :cards="cards"
      :using-side-tab="false"
      @handle-expand-card="handleExpandCard"
    >
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="investorForm.company_id" />
        </div>
      </template>
      <template #card-items-1>
        <div class="p-5">
          <div class="pb-4">
            <FormLabel :class="{ 'text-danger': investorForm.invalid('code') }">
              {{ t("views.investor.fields.code") }}
            </FormLabel>
            <FormInputCode
              v-model="investorForm.code"
              type="text"
              :class="{ 'border-danger': investorForm.invalid('code') }"
              :placeholder="t('views.investor.fields.code')"
              @set-auto="setCode"
              @change="investorForm.validate('code')"
            />
            <FormErrorMessages :messages="investorForm.errors.code" />
          </div>
          <div class="pb-4">
            <FormLabel :class="{ 'text-danger': investorForm.invalid('name') }">
              {{ t("views.investor.fields.name") }}
            </FormLabel>
            <FormInput
              v-model="investorForm.name"
              type="text"
              :class="{ 'border-danger': investorForm.invalid('name') }"
              :placeholder="t('views.investor.fields.name')"
              @change="investorForm.validate('name')"
            />
            <FormErrorMessages :messages="investorForm.errors.name" />
          </div>
          <div class="pb-4">
            <FormLabel>
              {{ t("views.investor.fields.remarks") }}
            </FormLabel>
            <FormTextarea
              v-model="investorForm.remarks"
              type="text"
              :placeholder="t('views.investor.fields.remarks')"
              rows="3"
            />
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
            :disabled="investorForm.validating || investorForm.hasErrors"
          >
            <Lucide
              v-if="investorForm.validating"
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
