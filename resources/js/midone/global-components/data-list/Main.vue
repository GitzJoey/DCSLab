<template>
    <div>
        <h2 class="intro-y text-lg font-medium mt-10">{{ title }}</h2>
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                <button class="btn btn-primary shadow-md mr-2 w-20" v-if="enableCreate"><PlusIcon class="w-4 h-4" /></button>
                <div class="dropdown" data-placement="bottom-start" v-if="canPrint || canExport">
                    <button class="dropdown-toggle btn px-2 box text-gray-700 dark:text-gray-300" aria-expanded="false">
                        <span class="w-5 h-5 flex items-center justify-center">
                          <ColumnsIcon class="w-4 h-4" />
                        </span>
                    </button>
                    <div class="dropdown-menu w-40">
                        <div class="dropdown-menu__content box dark:bg-dark-1 p-2">
                            <a v-if="canPrint" href="" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md">
                                <PrinterIcon class="w-4 h-4 mr-2" /> Print
                            </a>
                            <a v-if="canExport" href="" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md">
                                <FileTextIcon class="w-4 h-4 mr-2" /> Export to Excel
                            </a>
                            <a v-if="canExport" href="" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md">
                                <FileTextIcon class="w-4 h-4 mr-2" /> Export to PDF
                            </a>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block mx-auto text-gray-600">
                    Showing 1 to 10 of 150 entries
                </div>
                <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0" v-if="enableSearch">
                    <div class="w-56 relative text-gray-700 dark:text-gray-300">
                        <input type="text" class="form-control w-56 box pr-10 placeholder-theme-13" placeholder="Search..." />
                        <SearchIcon class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" />
                    </div>
                </div>
                <button class="btn shadow-md mr-2 w-20"><RefreshCwIcon class="w-4 h-4" /></button>
            </div>
            <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
                <slot name="table" :dataList="data"></slot>
            </div>
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-row sm:flex-nowrap items-center">
                <ul class="pagination">
                    <li>
                        <a class="pagination__link" href="">
                            <ChevronsLeftIcon class="w-4 h-4" />
                        </a>
                    </li>
                    <li>
                        <a class="pagination__link" href="">
                            <ChevronLeftIcon class="w-4 h-4" />
                        </a>
                    </li>
                    <li>
                        <a class="pagination__link" href="">...</a>
                    </li>
                    <li>
                        <a class="pagination__link" href="">1</a>
                    </li>
                    <li>
                        <a class="pagination__link pagination__link--active" href="">2</a>
                    </li>
                    <li>
                        <a class="pagination__link" href="">3</a>
                    </li>
                    <li>
                        <a class="pagination__link" href="">...</a>
                    </li>
                    <li>
                        <a class="pagination__link" href="">
                            <ChevronRightIcon class="w-4 h-4" />
                        </a>
                    </li>
                    <li>
                        <a class="pagination__link" href="">
                            <ChevronsRightIcon class="w-4 h-4" />
                        </a>
                    </li>
                </ul>
                <select class="w-20 form-select box mt-3 sm:mt-0">
                    <option>10</option>
                    <option>25</option>
                    <option>35</option>
                    <option>50</option>
                </select>
            </div>
        </div>
        <div id="delete-confirmation-modal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="p-5 text-center">
                            <XCircleIcon class="w-16 h-16 text-theme-21 mx-auto mt-3" />
                            <div class="text-3xl mt-5">Are you sure?</div>
                            <div class="text-gray-600 mt-2">
                                Do you really want to delete these records? <br />This process cannot be undone.
                            </div>
                        </div>
                        <div class="px-5 pb-8 text-center">
                            <button type="button" data-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">
                                Cancel
                            </button>
                            <button type="button" class="btn btn-danger w-24">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent, onMounted, computed, toRef, watch } from 'vue'
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
        enableView: { type: Boolean, default: true },
        filterColumns: { type: String, default: '' }
    },
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
        const filterColumns = toRef(props, 'filterColumns');

        const data = toRef(props, 'data');

        const { t } = useI18n();

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
            data
        }
    }
})
</script>
