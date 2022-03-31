<template>
            <VeeForm id="supplierForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">

                <TabGroup>
                    <TabList class="nav-tabs">
                        <Tab class="w-full py-2" tag="button">
                            supplier
                        </Tab>
                        <Tab class="w-full py-2" tag="button">
                            poc
                        </Tab>
                        <Tab class="w-full py-2" tag="button">
                            product
                        </Tab>
                    </TabList>
                    <TabPanels class="border-l border-r border-b">
                        <TabPanel class="leading-relaxed p-5">
                            <div class="mb-3">
                                <label for="inputCode" class="form-label">{{ t('views.supplier.fields.code') }}</label>
                                <div class="flex items-center">
                                    <VeeField id="inputCode" name="code" type="text" :class="{'form-control':true, 'border-danger': errors['code']}" :placeholder="t('views.supplier.fields.code')" :label="t('views.supplier.fields.code')" rules="required" @blur="reValidate(errors)" v-model="supplier.code" :readonly="mode === 'create' && supplier.code === '[AUTO]'" />
                                    <button type="button" class="btn btn-secondary mx-1" @click="generateCode" v-show="mode === 'create'">{{ t('components.buttons.auto') }}</button>
                                </div>
                                <ErrorMessage name="code" class="text-danger" />
                            </div>
                            <div class="mb-3">
                                <label for="inputContact" class="form-label">{{ t('views.supplier.fields.contact') }}</label>
                                <input id="inputContact" name="contact" type="text" class="form-control" :placeholder="t('views.supplier.fields.contact')" v-model="supplier.contact" />
                            </div>
                        </TabPanel>
                        <TabPanel class="leading-relaxed p-5">
                            <div class="mb-3">
                                <label for="inputPOCName" class="form-label">{{ t('views.supplier.fields.poc.name') }}</label>
                                <VeeField id="inputPOCName" name="poc_name" type="text" :class="{'form-control':true, 'border-danger': errors['poc_name']}" :placeholder="t('views.supplier.fields.poc.name')" :label="t('views.supplier.fields.poc.name')" rules="required" v-model="supplier.supplier_poc.name" />
                                <ErrorMessage name="poc_name" class="text-danger" />
                            </div>
                            <div class="mb-3">
                                <label for="inputEmail" class="form-label">{{ t('views.supplier.fields.poc.email') }}</label>
                                <VeeField id="inputEmail" name="email" type="text" :class="{'form-control':true, 'border-danger': errors['email']}" :placeholder="t('views.supplier.fields.poc.email')" :label="t('views.supplier.fields.poc.email')" rules="required|email" v-model="supplier.supplier_poc.email" :readonly="mode === 'edit'" />
                                <ErrorMessage name="email" class="text-danger" />
                            </div>
                        </TabPanel>
                        <TabPanel class="leading-relaxed p-5">
                            aaaaaaaaaaaaaaaa
                        </TabPanel>
                    </TabPanels>
                </TabGroup>
</VeeForm>
</template>

<script setup>
//#region Imports
import { onMounted, ref } from "vue";
import axios from "@/axios";
import { useI18n } from "vue-i18n";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const alertErrors = ref([]);
//#endregion

//#region Data - Views
const supplier = ref({
    code: '',
    name: '',
    term: '',
    contact: '',
    address: '',
    supplier_poc: {
        hId: '',
        profile: {
            first_name: ''
        }
    },
    city: '',
    taxable_enterprise: 1,
    tax_id: '',
    remarks: '',
    supplier_products: [],
    payment_term_type: '',
    payment_term: 0,
    selected_products: [],
    main_products: [],
    status: 1,
});
//#endregion

//#region onMounted
onMounted(() => {
    axios.get('/api/get/dashboard/devtool/test').then(response => {
        console.log(response.data);
    });
});
//#endregion

//#region Methods
const reValidate = (errors) => {
    alertErrors.value = errors;
}//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>
