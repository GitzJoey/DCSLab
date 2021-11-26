<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.company.table.title')" :data="companyList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
            <template v-slot:table="tableProps">
                <table class="table table-report -mt-2">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">{{ t('views.company.table.cols.code') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.company.table.cols.name') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.company.table.cols.default') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.company.table.cols.status') }}</th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="intro-x" v-if="tableProps.dataList !== undefined" v-for="(c, cIdx) in tableProps.dataList.data">
                            <td>{{ c.code }}</td>
                            <td>{{ c.name }}</td>
                            <td>
                                <CheckCircleIcon v-if="c.default === 1" />
                                <XIcon v-if="c.default === 0" />
                            </td>
                            <td>
                                <CheckCircleIcon v-if="c.status === 1" />
                                <XIcon v-if="c.status === 0" />
                            </td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <a class="flex items-center mr-3" href="" @click.prevent="showSelected(uIdx)">
                                        <InfoIcon class="w-4 h-4 mr-1" />
                                        {{ t('components.data-list.view') }}
                                    </a>
                                    <a class="flex items-center mr-3" href="" @click.prevent="editSelected(uIdx)">
                                        <CheckSquareIcon class="w-4 h-4 mr-1" />
                                        {{ t('components.data-list.edit') }}
                                    </a>
                                    <a class="flex items-center text-theme-21" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal" @click="deleteSelected(uIdx)">
                                        <Trash2Icon class="w-4 h-4 mr-1" /> {{ t('components.data-list.delete') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div id="delete-confirmation-modal" class="modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <div class="p-5 text-center">
                                    <XCircleIcon class="w-16 h-16 text-theme-21 mx-auto mt-3" />
                                    <div class="text-3xl mt-5">{{ t('components.data-list.delete_confirmation.title') }}</div>
                                    <div class="text-gray-600 mt-2">
                                        {{ t('components.data-list.delete_confirmation.desc_1') }}<br />{{ t('components.data-list.delete_confirmation.desc_2') }}
                                    </div>
                                </div>
                                <div class="px-5 pb-8 text-center">
                                    <button type="button" data-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">
                                        {{ t('components.buttons.cancel') }}
                                    </button>
                                    <button type="button" data-dismiss="modal" class="btn btn-danger w-24" @click="confirmDelete">{{ t('components.buttons.delete') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </DataList>
    </div>

    <div class="intro-y box" v-if="mode !== 'list'">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.company.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.company.actions.edit') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'show'">{{ t('views.company.actions.show') }}</h2>
        </div>
        <div class="loader-container">
            <VeeForm id="companyForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" :validation-schema="schema" v-slot="{ handleReset, errors, validate }">

            </VeeForm>
            <div class="loader-overlay" v-if="loading"></div>
        </div>
        <hr/>
        <div>
            <button type="button" class="btn btn-secondary w-15 m-3" @click="backToList">{{ t('components.buttons.back') }}</button>
        </div>
    </div>
</template>

<script setup>
// Vue Import
import { inject, onMounted, ref, computed } from 'vue'
// Helper Import
import { getLang } from '../../lang';
import mainMixins from '../../mixins';
import { helper } from '../../utils/helper';
// Components Import
import DataList from '../../global-components/data-list/Main'
import AlertPlaceholder from '../../global-components/alert-placeholder/Main'

// Vee-Validate Schema
const schema = {
    code: 'required',
    name: 'required',
};
// Mixins
const { t, route } = mainMixins();

// Data - VueX
// Data - UI
const mode = ref('list');
const loading = ref(false);
const alertErrors = ref([]);
const deleteId = ref('');

// Data - Views
const companyList = ref([]);
const company = ref({
    code: '',
    name: '',
    default: '',
    status: ''
});
const statusDDL = ref([]);

// onMounted
onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);

    getAllCompany({ page: 1 });
    getDDL();

    loading.value = false;
});

// Methods
function getAllCompany(args) {
    companyList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    axios.get(route('api.get.db.company.company.read', { "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        companyList.value = response.data;
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
        axios.post(route('api.post.db.company.company.save'), new FormData(cash('#companyForm')[0])).then(response => {
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else if (mode.value === 'edit') {
        axios.post(route('api.post.db.company.company.edit', company.value.hId), new FormData(cash('#companyForm')[0])).then(response => {
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

function emptyCompany() {
    return {
        code: '',
        name: '',
        default: '',
        status: '1',
    }
}

function resetAlertErrors() {
    alertErrors.value = [];
}

function createNew() {
    mode.value = 'create';
    company.value = emptyCompany();
}

function onDataListChange({page, pageSize, search}) {
    getAllCompany({page, pageSize, search});
}

function editSelected(index) {
    mode.value = 'edit';
    company.value = companyList.value.data[index];
}

function deleteSelected(index) {
    deleteId.value = companyList.value.data[index].hId;
}

function confirmDelete() {

}

function showSelected(index) {
    mode.value = 'show';
    company.value = companyList.value.data[index];
}

function backToList() {
    resetAlertErrors();
    mode.value = 'list';
    getAllCompany({ page: companyList.value.current_page, pageSize: companyList.value.per_page });
}

// Computed
// Watcher
</script>
