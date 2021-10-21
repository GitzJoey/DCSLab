<template>
    <div>
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
                    {{ t('components.data-list.showing') }} {{ data.from }} {{ t('components.data-list.to') }} {{ data.to }} {{ t('components.data-list.of') }} {{ data.total }} {{ t('components.data-list.entries') }}
                </div>
                <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0" v-if="enableSearch">
                    <div class="w-56 relative text-gray-700 dark:text-gray-300">
                        <input type="text" class="form-control w-56 box pr-10 placeholder-theme-13" :placeholder="t('components.data-list.search')" />
                        <SearchIcon class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" />
                    </div>
                </div>
                <button class="btn btn-primary-soft shadow-md mr-2 w-20" @click="$emit('dataListChange', { page: data.current_page, pageSize: pageSize })"><RefreshCwIcon class="w-4 h-4" /></button>
            </div>
            <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
                <slot name="table" :dataList="data"></slot>
            </div>
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-row sm:flex-nowrap items-center">
                <ul class="pagination">
                    <li>
                        <a class="pagination__link" href="" @click.prevent="$emit('dataListChange', { page: first, pageSize: pageSize })">
                            <ChevronsLeftIcon class="w-4 h-4" />
                        </a>
                    </li>
                    <li>
                        <a class="pagination__link" href="" @click.prevent="$emit('dataListChange', { page: previous, pageSize: pageSize })">
                            <ChevronLeftIcon class="w-4 h-4" />
                        </a>
                    </li>
                    <li v-for="(n, nIdx) in pages">
                        <a :class="{'pagination__link':true, 'pagination__link--active': data.current_page === n}" href="" @click.prevent="$emit('dataListChange', { page: n, pageSize: pageSize })">{{ n }}</a>
                    </li>
                    <li>
                        <a class="pagination__link" href="" @click.prevent="$emit('dataListChange', { page: next, pageSize: pageSize })">
                            <ChevronRightIcon class="w-4 h-4" />
                        </a>
                    </li>
                    <li>
                        <a class="pagination__link" href="" @click.prevent="$emit('dataListChange', { page: last, pageSize: pageSize })">
                            <ChevronsRightIcon class="w-4 h-4" />
                        </a>
                    </li>
                </ul>
                <select class="w-20 form-select box mt-3 sm:mt-0" v-model="pageSize" v-on:change="$emit('dataListChange', { page: data.current_page, pageSize: pageSize })">
                    <option value="10">10</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="1000">1000</option>
                </select>
            </div>
        </div>
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
                            <button type="button" class="btn btn-danger w-24">{{ t('components.buttons.delete') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {defineComponent, onMounted, computed, ref, toRef, watch, onUnmounted} from 'vue'
import { useI18n } from 'vue-i18n'

export default defineComponent( {
    name: "DataList",
    props: {
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
    },
    emits: [
        'createNew',
        'dataListChange',
        'print',
        'export'
    ],
    setup(props) {
        const title = toRef(props, 'title');
        const canPrint = toRef(props, 'canPrint');
        const canExport = toRef(props, 'canExport');
        const enableSearch = toRef(props, 'enableSearch');
        const enableCreate = toRef(props, 'enableCreate');
        const enableEdit = toRef(props, 'enableEdit');
        const enableDelete = toRef(props, 'enableDelete');
        const enableDeleteConfirmation = toRef(props, 'enableDeleteConfirmation');
        const enableView = toRef(props, 'enableView');

        const pageSize = ref(10);
        const data = toRef(props, 'data');

        let loading = computed(() => {
            if (data.value.data !== undefined && data.value.data.length !== 0) return false;
            else return true;
        });

        let pages = computed(() => {
            if (data.value.current_page !== undefined && data.value.last_page !== undefined) {
                return pagination(data.value.current_page, data.value.last_page);
            } else {
                return [];
            }
        });

        let first = computed(()=> { return 1; });
        let previous = computed(()=> {
            if (data.value.current_page === undefined) return 1;
            if (data.value.current_page === 1) return 1;
            return data.value.current_page - 1;
        });
        let next = computed(()=> {
            if (data.value.current_page === undefined) return 1;
            if (data.value.current_page === data.value.last_page) return data.value.last_page;
            return data.value.current_page + 1;
        });
        let last = computed(()=> { return data.value.last_page; });

        const { t } = useI18n();

        function pagination(currentPage, pageCount) {
            const delta = 2

            let range = []
            for (let i = Math.max(2, currentPage - delta); i <= Math.min(pageCount - 1, currentPage + delta); i++) {
                range.push(i)
            }

            if (currentPage - delta > 2) {
                range.unshift("...")
            }
            if (currentPage + delta < pageCount - 1) {
                range.push("...")
            }

            range.unshift(1)
            range.push(pageCount)

            return range
        }

        return {
            t,
            title,
            canPrint,
            canExport,
            enableSearch,
            enableCreate,
            enableEdit,
            enableDelete,
            enableView,
            data,
            loading,
            pages,
            pageSize,
            first,
            previous,
            next,
            last,
        }
    }
})
</script>
