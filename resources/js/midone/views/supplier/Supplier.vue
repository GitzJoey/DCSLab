<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.users.table.title')" :data="userList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
        </DataList>
    </div>

    <div class="intro-y box" v-if="mode !== 'list'">

    </div>
</template>

<script setup>
// Vue Import
import { inject, onMounted, ref, computed } from 'vue'
// Helper Import
import { getLang } from '../../lang';
import mainMixins from '../../mixins';
import { helper } from '../../utils/helper';
// Core Components Import
// Components Import
import DataList from '../../global-components/data-list/Main'
import AlertPlaceholder from '../../global-components/alert-placeholder/Main'

// Vee-Validate Schema
const schema = {
    code: 'required',
    name: 'required',
};

// Declarations
// Mixins
const { t, route } = mainMixins();

// Data - VueX
// Data - UI
const mode = ref('list');
const loading = ref(false);
const alertErrors = ref([]);
const deleteId = ref('');
const expandDetail = ref(null);

// Data - Views
const supplierList = ref([]);
const supplier = reF({
    code: '',
    name: '',
    term: '',
    contact: '',
    address: '',
    city: '',
    is_tax: '1',
    tax_number: '',
    remarks: '',
    status: '1',
});

// onMounted
onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);

    getAllSupplier({ page: 1 });
    getDDL();

    loading.value = false;
});

// Methods
function getAllSupplier(args) {
    supplierList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    axios.get(route('api.get.db.supplier.supplier.read', { "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        supplierList.value = response.data;
        loading.value = false;
    });
}

function getDDL() {
    axios.get(route('api.get.db.common.ddl.list.statuses')).then(response => {
        statusDDL.value = response.data;
    });
}

function onSubmit(values, actions) {
    loading.value = true;
    if (mode.value === 'create') {
        axios.post(route('api.post.db.supplier.supplier.save'), new FormData(cash('#supplierForm')[0])).then(response => {
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else if (mode.value === 'edit') {
        axios.post(route('api.post.db.supplier.supplier.edit', supplier.value.hId), new FormData(cash('#supplierForm')[0])).then(response => {
            actions.resetForm();
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else { }
}

function handleError(e, actions) {
    //Laravel Validations
    if (e.response.data.errors !== undefined && Object.keys(e.response.data.errors).length > 0) {
        for (var key in e.response.data.errors) {
            for (var i = 0; i < e.response.data.errors[key].length; i++) {
                actions.setFieldError(key, e.response.data.errors[key][i]);
            }
        }
        alertErrors.value = e.response.data.errors;
    } else {
        //Catch From Controller
        alertErrors.value = {
            controller: e.response.status + ' ' + e.response.statusText +': ' + e.response.data.message
        };
    }
}

function invalidSubmit(e) {
    alertErrors.value = e.errors;
}

function emptySupplier() {
    return {
        code: '[AUTO]',
        name: '',
        term: '',
        contact: '',
        address: '',
        city: '',
        is_tax: '1',
        tax_number: '',
        remarks: '',
        status: '1',
    }
}

function resetAlertErrors() {
    alertErrors.value = [];
}

function createNew() {
    mode.value = 'create';
    supplier.value = emptySupplier();
}

function onDataListChange({page, pageSize, search}) {
    getAllSupplier({page, pageSize, search});
}

function editSelected(index) {
    mode.value = 'edit';
    supplier.value = supplierList.value.data[index];
}

function deleteSelected(index) {
    deleteId.value = supplierList.value.data[index].hId;
}

function confirmDelete() {
    axios.post(route('api.post.db.supplier.supplier.delete', deleteId.value)).then(response => {
        backToList();
    }).catch(e => {
        alertErrors.value = e.response.data;
    }).finally(() => {

    });
}

function showSelected(index) {
    mode.value = 'show';
    supplier.value = supplierList.value.data[index];
}

function backToList() {
    resetAlertErrors();
    mode.value = 'list';
    getAllSupplier({ page: supplierList.value.current_page, pageSize: supplierList.value.per_page });
}

function toggleDetail(idx) {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
}

function generateCode() {
    if (supplier.value.code === '[AUTO]') supplier.value.code = '';
    else  supplier.value.code = '[AUTO]'
}

// Computed
// Watcher
</script>
