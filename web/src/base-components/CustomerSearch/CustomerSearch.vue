<script setup lang="ts">
// #region import
import { ref, computed, onMounted } from "vue";
import { useSelectedUserLocationStore } from "../../stores/user-location";
import CustomerService from "../../services/CustomerService";
import { FormInput, FormLabel } from "../Form";
import { useI18n } from "vue-i18n";
import Lucide from "../Lucide";

// #endregion

// Region declaration
const { t } = useI18n();
const selectedUserStore = useSelectedUserLocationStore();
const customerService = new CustomerService();
const userLocation = computed(() => selectedUserStore.selectedUserLocation);
// End region declaration

// Region type
type Customer = {
  code: string;
  isMember: boolean;
  name: string;
  customerGroupId: string;
  zone: string;
  customerAddressAddress: string[];
  customerAddressId: string[];
  customerAddressCity: string[];
  customerAddressContact: string[];
  customerAddressIsMain: boolean[];
  customerAddressRemark: string[];
  maxOpenInvoice: number;
  maxOutStandingInvoice: number;
  maxInvoiceAge: number;
  paymentTermType: string;
  paymentTerm: number;
  taxable_enterprise: boolean;
  taxId: string;
  remarks: string;
  status: boolean;
  picCreateUser: string[];
  picContactPersonName: string[];
  picEmail: string[];
  picPassword: string[];
  id?: string;
  ulid?: string;
  customer_group: CustomerGroup;
  company_id?: string;
};

type CustomerGroup = {
  name: string;
};


// End region type

// Region data - ui
const customerList = ref<any[]>([]);
const selectedCustomer = ref<string | boolean | undefined>("");
const isAddNewValue = ref<boolean>(false);
const detailCustomer = ref<Customer>({
  code: "",
  isMember: false,
  name: "",
  zone: "",
  maxOpenInvoice: 0,
  maxOutStandingInvoice: 0,
  maxInvoiceAge: 0,
  paymentTermType: "",
  paymentTerm: 0,
  taxable_enterprise: false,
  taxId: "",
  remarks: "",
  status: false,
  picCreateUser: [""],
  picContactPersonName: [""],
  picEmail: [""],
  picPassword: [""],
  customerAddressAddress: [""],
  customerAddressCity: [""],
  customerAddressContact: [''],
  customerAddressId: [''],
  customerAddressIsMain: [false],
  customerAddressRemark: [''],
  customer_group: {
    name: "",
  },
  customerGroupId: ''
});
// End region data

// Method region
async function getCustomerList() {
  const companyId: string = userLocation.value.company.id;
  const data = await customerService.readAny({
    company_id: companyId,
    search: "",
    paginate: false,
  });
  console.log(data.data.data, "Customer list");
  customerList.value = data?.data?.data;
}

async function handleChangeOptions(e: any) {
  const id = e?.target?.value;
  if (id) {
    selectedCustomer.value = id;
    const detailCustomerPayload = await customerService.read(id);
    console.log(detailCustomerPayload.data.data);
    const payload: Customer = detailCustomerPayload?.data?.data;
    detailCustomer.value = payload;
  }
}

async function handleSubmit() {
  const payload = detailCustomer.value;
  const company_id = userLocation.value.company.id;
  payload;
  const objPayload: Customer = payload;
  objPayload.ulid = "newUlid"
  detailCustomer.value = payload
  selectedCustomer.value = payload.ulid
  customerList.value?.push(payload)
  const createSupplier = await customerService.create(
    company_id,
    objPayload.code,
    objPayload.isMember,
    objPayload.name,
    objPayload.customerGroupId,
    objPayload.zone,
    objPayload.customerAddressId,
    objPayload.customerAddressAddress,
    objPayload.customerAddressCity,
    objPayload.customerAddressContact,
    objPayload.customerAddressIsMain,
    objPayload.customerAddressRemark,
    objPayload.maxOpenInvoice,
    objPayload.maxOutStandingInvoice,
    objPayload.maxInvoiceAge,
    objPayload.paymentTermType,
    objPayload.paymentTerm,
    objPayload.taxable_enterprise,
    objPayload.taxId,
    objPayload.remarks,
    objPayload.status,
    objPayload.picCreateUser,
    objPayload.picContactPersonName,
    objPayload.picEmail,
    objPayload.picPassword
  );
}

function handleOpenAddForm() {
  isAddNewValue.value = true;
  selectedCustomer.value = false;
  detailCustomer.value = {
    code: "",
    isMember: false,
    name: "",
    zone: "",
    maxOpenInvoice: 0,
    maxOutStandingInvoice: 0,
    maxInvoiceAge: 0,
    paymentTermType: "",
    paymentTerm: 0,
    taxable_enterprise: false,
    taxId: "",
    remarks: "",
    status: false,
    picCreateUser: [""],
    picContactPersonName: [""],
    picEmail: [""],
    picPassword: [""],
    customerAddressAddress: [""],
    customerAddressCity: [""],
    customerAddressContact: [''],
    customerAddressId: [''],
    customerAddressIsMain: [false],
    customerAddressRemark: [''],
    customer_group: {
      name: "",
    },
    customerGroupId: ''
  };
}
// End method region

// Region on mounted
onMounted(() => {
  getCustomerList();
});
// End region on mounted
</script>

<template>
  <div class="grid grid-cols-12">
    <select class="form-select col-span-11 py-3 px-4 w-full mt-3 lg:mt-0 ml-auto border-none focus:border-none rounded"
      @change="handleChangeOptions($event)">
      <option :value="false" selected>Select Customer</option>
      <option v-for="(customer, index) in customerList" :key="index" :selected="customer?.ulid === selectedCustomer"
        :value="customer?.ulid">
        {{ customer.name }}
      </option>
    </select>
    <div class="box flex col-span-1 ml-5 items-center justify-center bg-[#164e63] text-white cursor-pointer"
      @click="handleOpenAddForm">
      <Lucide icon="Plus" />
    </div>
  </div>

  <div class="box mt-3" v-if="(selectedCustomer ? true : false) || isAddNewValue">
    <div class="p-4">
      <FormLabel html-for="name"> Code </FormLabel>
      <FormInput name="code" type="text" class="w-full" v-model="detailCustomer.code"
        :disabled="selectedCustomer ? true : false" />
    </div>
    <div class="p-4">
      <FormLabel html-for="name"> Name </FormLabel>
      <FormInput name="name" type="text" class="w-full" v-model="detailCustomer.name"
        :disabled="selectedCustomer ? true : false" />
    </div>

    <div class="p-4">
      <FormLabel html-for="name"> Customer Group Name </FormLabel>
      <FormInput name="customerGroupId" type="text" class="w-full" v-model="detailCustomer.customer_group.name"
        :disabled="selectedCustomer ? true : false" />
    </div>

    <div class="p-4" v-if="selectedCustomer ? false : true">
      <button @click="handleSubmit" class="w-full bg-[#164e63] h-[50px] rounded text-white">
        {{ t("components.buttons.submit") }}
      </button>
    </div>
  </div>
</template>
