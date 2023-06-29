<script setup lang="ts">
//#region Import
import { ref, computed } from "vue";
import { onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { TitleLayout } from "../../Form/FormLayout";
import { ViewMode } from "../../../types/enums/ViewMode";
import Lucide from "../../Lucide";
import { useSelectedUserLocationStore } from "../../../stores/user-location";
import SupplierService from "../../../services/SupplierService";
import { FormInput, FormLabel, FormTextarea, FormSelect } from "../../Form";
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
const selectedSupplier = ref<string | boolean | undefined>("");
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
  try {
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
  } catch (error) {
    throw error;
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
    const obj: Supplier = {
      code: payload?.code,
      name: payload?.name,
      address: payload?.address,
      city: payload?.city,
      contact: payload?.contact,
      taxable_enterprise: payload?.taxable_enterprise,
      tax_id: payload?.tax_id,
      payment_term: payload?.payment_term,
      payment_term_type: payload?.payment_term_type,
      remarks: payload?.remarks,
      status: payload?.status,
      ulid: "randomUlid",
    };
    detailSupplier.value = obj;
    selectedSupplier.value = obj.ulid;
    supplierList.value?.push(obj);
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
        :selected="supplier?.ulid === selectedSupplier"
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
    <div class="p-4" v-if="selectedSupplier ? false : true">
      <button
        @click="handleSubmit"
        class="w-full bg-[#164e63] h-[50px] rounded text-white"
      >
        {{ t("components.buttons.submit") }}
      </button>
    </div>
  </div>
</template>
