<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.po.table.title')" :data="poList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
        </DataList>
    </div>
    <div v-if="mode !== 'list'">
        <h2 class="intro-y text-lg font-bold mt-5" v-if="mode === 'create'">{{ t('views.purchase_order.actions.create') }}</h2>
        <h2 class="intro-y text-lg font-bold mt-5" v-if="mode === 'edit'">{{ t('views.purchase_order.actions.edit') }}</h2>
    </div>
    <hr/>
    <div>
        <button type="button" class="btn btn-secondary w-15 m-3" @click="backToList">{{ t('components.buttons.back') }}</button>
    </div>
</template>

<script setup>
// Vue Import
import { inject, onMounted, ref, computed, watch } from 'vue'
// Helper Import
import mainMixins from '../../mixins';
// Core Components Import
import { useStore } from '../../store/index';
// Components Import
import DataList from '../../global-components/data-list/Main'
import AlertPlaceholder from '../../global-components/alert-placeholder/Main'

// Vee-Validate Schema
// Declarations
const store = useStore();

// Mixins
const { t, route } = mainMixins();

// Data - VueX
const selectedUserCompany = computed(() => store.state.main.selectedUserCompany );

// Data - UI
const mode = ref('list');
const loading = ref(false);
const alertErrors = ref([]);
const deleteId = ref('');
const expandDetail = ref(null);
// Data - Views
const poList = ref([]);
const po = ref({
    
});

// onMounted
onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);

    if (selectedUserCompany.value !== '') {
    }

    mode.value = "create";

    loading.value = false;
});

// Methods
function getAllPO(args) {
    poList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    let companyId = selectedUserCompany.value;

    axios.get(route('api.get.db.purchase_order.purchaseorder.read', { "companyId": companyId, "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        poList.value = response.data;
        loading.value = false;
    });
}

function resetAlertErrors() {
    alertErrors.value = [];
}

function backToList() {
    resetAlertErrors();
    mode.value = 'list';
    getAllPO({ page: poList.value.current_page, pageSize: poList.value.per_page });
}
// Computed
// Watcher
</script>