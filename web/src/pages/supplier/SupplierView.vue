<script setup lang="ts">
//#region Import
import { ref, computed } from "vue";
import { onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { TitleLayout } from "../../base-components/Form/FormLayout";
import { ViewMode } from "../../types/enums/ViewMode";
import DataList from "../../base-components/DataList/DataList.vue";
import Table from "../../base-components/Table";
import Lucide from "../../base-components/Lucide";
import { useSelectedUserLocationStore } from "../../stores/user-location";
import SupplierService from "../../services/SupplierService";
import {
  FormInput,
  FormLabel,
  FormTextarea,
  FormSelect,
} from "../../base-components/Form";
//#endregion

//#region Declarations
const { t } = useI18n();
const selectedUserStore = useSelectedUserLocationStore();
const supplierService = new SupplierService();
const userLocation = computed(() => selectedUserStore.selectedUserLocation);
//#endregion

// Define Type
type Supplier = {
  code: string;
  name: string;
  address: string;
  city: string;
  contact: string;
  taxable_enterprise: number;
  tax_id: string;
  payment_term_type: string;
  payment_term: number;
  remarks: string;
  status: string;
  ulid?: string;
  companyId?: string;
};
// End Define Type

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const supplierList = ref<Supplier[] | undefined | null>(null);
const selectedSupplier = ref<string | boolean>("");
const detailSupplier = ref<Supplier>({
  code: "",
  name: "",
  address: "",
  city: "",
  contact: "",
  taxable_enterprise: 0,
  tax_id: "",
  payment_term_type: "",
  payment_term: 0,
  remarks: "",
  status: "",
});
const isAddNewValue = ref<boolean>(false);
//#endregion

// Start Method
async function getSupplierList() {
  try {
    const companyId = userLocation.value.company.id;
    const data = await supplierService.readAny({ company_id: companyId });
    supplierList.value = data?.data?.data;
  } catch (error) {
    throw error;
  }
}

async function handleChangeOptions(e: any) {
  const id = e?.target?.value;
  if (id) {
    selectedSupplier.value = id;
    const detailSupplierPayload = await supplierService.read(id);
    const payload = detailSupplierPayload?.data?.data;
    detailSupplier.value.code = payload?.code;
    detailSupplier.value.name = payload?.name;
    detailSupplier.value.address = payload?.address;
    detailSupplier.value.city = payload?.city;
    detailSupplier.value.contact = payload?.contact;
    detailSupplier.value.taxable_enterprise = payload?.taxable_enterprise;
    detailSupplier.value.tax_id = payload?.tax_id;
    detailSupplier.value.payment_term = payload?.payment_term;
    detailSupplier.value.payment_term_type = payload?.payment_term_type;
    detailSupplier.value.remarks = payload?.remarks;
    detailSupplier.value.status = payload?.status;
  } else {
    selectedSupplier.value = false;
    isAddNewValue.value = false;
  }
}

function handleOpenAddForm() {
  isAddNewValue.value = true;
  selectedSupplier.value = false;
  detailSupplier.value.code = "";
  detailSupplier.value.name = "";
  detailSupplier.value.address = "";
  detailSupplier.value.city = "";
  detailSupplier.value.contact = "";
  detailSupplier.value.taxable_enterprise = 0;
  detailSupplier.value.tax_id = "";
  detailSupplier.value.payment_term = 0;
  detailSupplier.value.payment_term_type = "";
  detailSupplier.value.remarks = "";
  detailSupplier.value.status = "";
}

async function handleSubmit() {
  try {
    const payload = detailSupplier.value;
    const companyId = userLocation.value.company.id;

    const createSupplier = await supplierService.create(
      companyId,
      payload.code,
      payload.name,
      payload.address,
      payload.city,
      payload.contact,
      payload.taxable_enterprise,
      payload.tax_id,
      payload.payment_term_type,
      payload.payment_term,
      payload.remarks,
      payload.status,
      [],
      [],
      [],
      [],
      [],
      []
    );
  } catch (error) {}
}
// End Method

//#region onMounted
onMounted(() => {
  getSupplierList();
});
//#endregion
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <div v-if="mode == ViewMode.LIST"></div>
      <TitleLayout>
        <template #title>{{ t("views.supplier.page_title") }}</template>
      </TitleLayout>

      <div class="grid grid-cols-12">
        <select
          class="form-select col-span-11 py-3 px-4 w-full mt-3 lg:mt-0 ml-auto border-none focus:border-none rounded"
          @change="handleChangeOptions($event)"
          placeholder="Select Supplier"
          :value="selectedSupplier ? selectedSupplier : false"
        >
          <option :value="false" selected>Select Supplier</option>
          <option
            v-for="(supplier, index) in supplierList"
            :key="index"
            :value="supplier?.ulid"
          >
            {{ supplier?.name }}
          </option>
        </select>

        <div
          class="box flex col-span-1 ml-5 items-center justify-center bg-[#164e63] text-white cursor-pointer"
          @click="handleOpenAddForm"
        >
          <Lucide icon="Plus" />
        </div>
      </div>
      <div
        class="box mt-3"
        v-if="(selectedSupplier ? true : false) || isAddNewValue"
      >
        <div class="p-4">
          <FormLabel html-for="name"> Code </FormLabel>
          <FormInput
            v-model="detailSupplier.code"
            :disabled="selectedSupplier ? true : false"
            name="code"
            type="text"
            :value="detailSupplier.code"
            class="w-full"
          />
        </div>

        <div class="p-4">
          <FormLabel html-for="name"> Name </FormLabel>
          <FormInput
            :disabled="selectedSupplier ? true : false"
            name="name"
            v-model="detailSupplier.name"
            :value="detailSupplier.name"
            type="text"
            class="w-full"
          />
        </div>

        <div class="p-4">
          <FormLabel html-for="name"> Address </FormLabel>
          <FormInput
            :disabled="selectedSupplier ? true : false"
            name="address"
            type="text"
            class="w-full"
            :value="detailSupplier.address"
            v-model="detailSupplier.address"
          />
        </div>

        <div class="p-4">
          <FormLabel html-for="name"> City </FormLabel>
          <FormInput
            :disabled="selectedSupplier ? true : false"
            name="City"
            type="text"
            class="w-full"
            :value="detailSupplier.city"
            v-model="detailSupplier.city"
          />
        </div>

        <div class="p-4">
          <FormLabel html-for="name"> Contact </FormLabel>
          <FormInput
            :disabled="selectedSupplier ? true : false"
            name="contact"
            type="text"
            :value="detailSupplier.contact"
            v-model="detailSupplier.contact"
            class="w-full"
          />
        </div>

        <div class="p-4">
          <FormLabel html-for="name"> Tax Id </FormLabel>
          <FormInput
            :disabled="selectedSupplier ? true : false"
            name="taxId"
            type="text"
            class="w-full"
            :value="detailSupplier.tax_id"
            v-model="detailSupplier.tax_id"
          />
        </div>

        <div class="p-4">
          <FormLabel html-for="name"> Payment Term Type </FormLabel>
          <FormSelect
            :value="
              detailSupplier.payment_term_type
                ? detailSupplier.payment_term_type
                : null
            "
            v-model="detailSupplier.payment_term_type"
            :disabled="selectedSupplier ? true : false"
          >
            <option
              :value="t('components.dropdown.values.paymentTermTypeDDL.pia')"
            >
              {{ t("components.dropdown.values.paymentTermTypeDDL.pia") }}
            </option>
            <option
              :value="t('components.dropdown.values.paymentTermTypeDDL.net')"
            >
              {{ t("components.dropdown.values.paymentTermTypeDDL.net") }}
            </option>
            <option
              :value="t('components.dropdown.values.paymentTermTypeDDL.eom')"
            >
              {{ t("components.dropdown.values.paymentTermTypeDDL.eom") }}
            </option>
            <option
              :value="t('components.dropdown.values.paymentTermTypeDDL.cod')"
            >
              {{ t("components.dropdown.values.paymentTermTypeDDL.cod") }}
            </option>
            <option
              :value="t('components.dropdown.values.paymentTermTypeDDL.cnd')"
            >
              {{ t("components.dropdown.values.paymentTermTypeDDL.cnd") }}
            </option>
            <option
              :value="t('components.dropdown.values.paymentTermTypeDDL.cbs')"
            >
              {{ t("components.dropdown.values.paymentTermTypeDDL.cbs") }}
            </option>
          </FormSelect>
        </div>

        <div class="p-4">
          <FormLabel html-for="name"> Payment Term </FormLabel>
          <FormInput
            :disabled="selectedSupplier ? true : false"
            name="payment_term"
            type="number"
            class="w-full"
            :value="detailSupplier.payment_term"
            v-model="detailSupplier.payment_term"
          />
        </div>

        <div class="p-4">
          <FormLabel html-for="name"> Remarks </FormLabel>
          <FormInput
            :disabled="selectedSupplier ? true : false"
            name="remarks"
            type="text"
            class="w-full"
            :value="detailSupplier.remarks"
            v-model="detailSupplier.remarks"
          />
        </div>

        <div class="p-4">
          <FormLabel html-for="name"> Taxable Enterprise </FormLabel>
          <FormInput
            :disabled="selectedSupplier ? true : false"
            name="taxable_enterprise"
            type="number"
            class="w-full"
            :value="detailSupplier.taxable_enterprise"
            v-model="detailSupplier.taxable_enterprise"
          />
        </div>

        <div class="p-4">
          <FormLabel html-for="name"> Status </FormLabel>
          <FormSelect
            :disabled="selectedSupplier ? true : false"
            :value="detailSupplier.status"
            v-model="detailSupplier.status"
          >
            <option :value="t('components.dropdown.values.statusDDL.active')">
              {{ t("components.dropdown.values.statusDDL.active") }}
            </option>

            <option :value="t('components.dropdown.values.statusDDL.inactive')">
              {{ t("components.dropdown.values.statusDDL.inactive") }}
            </option>

            <option :value="t('components.dropdown.values.statusDDL.deleted')">
              {{ t("components.dropdown.values.statusDDL.deleted") }}
            </option>
          </FormSelect>
        </div>

        <div class="p-4" v-if="selectedSupplier ? false : true">
          <button
            @click="handleSubmit"
            class="w-full bg-[#164e63] h-[50px] rounded text-white"
          >
            {{ t("components.buttons.submit") }}
          </button>
        </div>
      </div>
    </LoadingOverlay>
  </div>
</template>
