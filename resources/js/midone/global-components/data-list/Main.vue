<template>
    <div v-if="visible">
        <h2 class="intro-y text-lg font-bold mt-5">{{ title }}</h2>
        <div class="grid grid-cols-12 gap-6 mt-5" v-if="loading">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <LoadingIcon icon="puff" />
            </div>
        </div>
        <div class="grid grid-cols-12 gap-6 mt-5" v-if="!loading">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <button class="btn btn-primary shadow-md mr-2 w-20" v-if="enableCreate" @click="$emit('createNew')"><PlusIcon class="w-4 h-4" /></button>
                <div class="dropdown" data-placement="bottom-start" v-if="canPrint || canExport">
                    <button class="dropdown-toggle btn px-2 box text-gray-700 dark:text-gray-300" aria-expanded="false">
                    <span class="w-5 h-5 flex items-center justify-center">
                      <ColumnsIcon class="w-4 h-4" />
                    </span>
                    </button>
                    <div class="dropdown-menu w-40">
                        <div class="dropdown-menu__content box dark:bg-dark-1 p-2">
                            <a v-if="canPrint" href="" @click.prevent="$emit('print')" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md">
                                <PrinterIcon class="w-4 h-4 mr-2" /> {{ t('components.data-list.print') }}
                            </a>
                            <a v-if="canExport" href="" @click.prevent="$emit('export', 'xls')" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md">
                                <FileTextIcon class="w-4 h-4 mr-2" /> {{ t('components.data-list.exportToExcel') }}
                            </a>
                            <a v-if="canExport" href="" @click.prevent="$emit('export', 'pdf')" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md">
                                <FileTextIcon class="w-4 h-4 mr-2" /> {{ t('components.data-list.exportToPDF') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block mx-auto text-gray-600">
                    <template v-if="!dataNotFound">
                        {{ t('components.data-list.showing') }} {{ data.from }} {{ t('components.data-list.to') }} {{ data.to }} {{ t('components.data-list.of') }} {{ data.total }} {{ t('components.data-list.entries') }}
                    </template>
                </div>
                <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0 mr-3" v-if="enableSearch">
                    <div class="w-56 relative text-gray-700 dark:text-gray-300">
                        <input type="text" class="form-control w-56 box pr-10 placeholder-theme-13" v-model="search" :placeholder="t('components.data-list.search')" @focus="$event.target.select()" @change="$emit('dataListChange', { page: data.current_page, pageSize: pageSize, search: search })" />
                        <SearchIcon class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" />
                    </div>
                </div>
                <button class="btn btn-primary-soft shadow-md mr-2 w-20" @click="$emit('dataListChange', { page: data.current_page, pageSize: pageSize, search: search })"><RefreshCwIcon class="w-4 h-4" /></button>
            </div>
            <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
                <slot name="table" :dataList="data"></slot>
                <table class="table table-report -mt-2" v-if="dataNotFound">
                    <tbody>
                        <tr class="intro-x">
                            <td class="text-sm italic text-center">{{ t('components.data-list.data_not_found') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-row sm:flex-nowrap items-center" v-if="!dataNotFound">
                <ul class="pagination">
                    <li>
                        <a class="pagination__link" href="" @click.prevent="$emit('dataListChange', { page: first, pageSize: pageSize, search: search })">
                            <ChevronsLeftIcon class="w-4 h-4" />
                        </a>
                    </li>
                    <li>
                        <a class="pagination__link" href="" @click.prevent="$emit('dataListChange', { page: previous, pageSize: pageSize, search: search })">
                            <ChevronLeftIcon class="w-4 h-4" />
                        </a>
                    </li>
                    <li v-for="(n, nIdx) in pages">
                        <a :class="{'pagination__link':true, 'pagination__link--active': data.current_page === n}" href="" @click.prevent="$emit('dataListChange', { page: n, pageSize: pageSize, search: search })">{{ n }}</a>
                    </li>
                    <li>
                        <a class="pagination__link" href="" @click.prevent="$emit('dataListChange', { page: next, pageSize: pageSize, search: search })">
                            <ChevronRightIcon class="w-4 h-4" />
                        </a>
                    </li>
                    <li>
                        <a class="pagination__link" href="" @click.prevent="$emit('dataListChange', { page: last, pageSize: pageSize, search: search })">
                            <ChevronsRightIcon class="w-4 h-4" />
                        </a>
                    </li>
                </ul>
                <select class="w-20 form-select box mt-3 sm:mt-0" v-model="pageSize" v-on:change="$emit('dataListChange', { page: data.current_page, pageSize: pageSize, search: search })">
                    <option value="10">10</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="1000">1000</option>
                </select>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, toRef } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
    visible: { type: Boolean, default: true },
    title: { type: String },
    canPrint: { type: Boolean, default: false },
    canExport: { type: Boolean, default: false },
    enableSearch: { type: Boolean, default: false },
    data: { type: Object, default: null },
    enableCreate: { type: Boolean, default: true },
    enableEdit: { type: Boolean, default: true },
    enableDelete: { type: Boolean, default: true },
    enableDeleteConfirmation: { type: Boolean, default: true },
    enableView: { type: Boolean, default: true }
});

const emit = defineEmits(['createNew', 'dataListChange', 'print', 'export']);

const visible = toRef(props, 'visible');
const title = toRef(props, 'title');
const canPrint = toRef(props, 'canPrint');
const canExport = toRef(props, 'canExport');
const enableSearch = toRef(props, 'enableSearch');
const enableCreate = toRef(props, 'enableCreate');
const enableEdit = toRef(props, 'enableEdit');
const enableDelete = toRef(props, 'enableDelete');
const enableDeleteConfirmation = toRef(props, 'enableDeleteConfirmation');
const enableView = toRef(props, 'enableView');
const search = ref('');

const pageSize = ref(10);

const data = toRef(props, 'data');

const loading = computed(() => {
    return data.value.data === undefined;
});

const dataNotFound = computed(() => {
    return data.value.data !== undefined && data.value.data.length === 0;
});

const pages = computed(() => {
    if (data.value.meta.current_page !== undefined && data.value.meta.last_page !== undefined) {
         return paginate(data.value.meta.total, data.value.meta.current_page, data.value.meta.per_page, 5);
    } else {
        return [];
    }
});

const first = computed(()=> { return 1; });

let previous = computed(()=> {
    if (data.value.meta.current_page === undefined) return 1;
    if (data.value.meta.current_page === 1) return 1;
    return data.value.meta.current_page - 1;
});

const next = computed(()=> {
    if (data.value.meta.current_page === undefined) return 1;
    if (data.value.meta.current_page === data.value.meta.last_page) return data.value.meta.last_page;
    return data.value.meta.current_page + 1;
});

const last = computed(()=> { return data.value.meta.last_page; });

const { t } = useI18n();

function paginate(totalItems, currentPage, pageSize, maxPages) {
    let totalPages = Math.ceil(totalItems / pageSize);

    if (currentPage < 1) {
        currentPage = 1;
    } else if (currentPage > totalPages) {
        currentPage = totalPages;
    }

    let startPage;
    let endPage;
    if (totalPages <= maxPages) {
        startPage = 1;
        endPage = totalPages;
    } else {
        let maxPagesBeforeCurrentPage = Math.floor(maxPages / 2);
        let maxPagesAfterCurrentPage = Math.ceil(maxPages / 2) - 1;
        if (currentPage <= maxPagesBeforeCurrentPage) {
            startPage = 1;
            endPage = maxPages;
        } else if (currentPage + maxPagesAfterCurrentPage >= totalPages) {
            startPage = totalPages - maxPages + 1;
            endPage = totalPages;
        } else {
            startPage = currentPage - maxPagesBeforeCurrentPage;
            endPage = currentPage + maxPagesAfterCurrentPage;
        }
    }

    let startIndex = (currentPage - 1) * pageSize;
    let endIndex = Math.min(startIndex + pageSize - 1, totalItems - 1);

    let pages = Array.from(Array((endPage + 1) - startPage).keys()).map(i => startPage + i);

    return pages;
}
</script>
