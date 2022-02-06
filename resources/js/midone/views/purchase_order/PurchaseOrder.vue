<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.purchase_order.table.list_table.title')" :data="poList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
        </DataList>
    </div>
    <div class="intro-y box" v-if="mode !== 'list'">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.purchase_order.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.purchase_order.actions.edit') }}</h2>
        </div>
        <div class="loader-container bg-gray-200 border-2 border-l-white border-r-white">
            <VeeForm id="poForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                <div class="intro-y grid grid-cols-2 mb-5">
                    <div class="">
                        <button class="btn btn-primary shadow-md mr-2" @click.prevent="gotoTabs('po')">{{ t('views.purchase_order.tabs.purchase_order') }}</button>
                        <button class="btn btn-primary shadow-md mr-2" @click.prevent="gotoTabs('supplier')">{{ t('views.purchase_order.tabs.supplier') }}</button>
                        <button class="btn btn-primary shadow-md" @click.prevent="gotoTabs('warehouse')">{{ t('views.purchase_order.tabs.warehouse') }}</button>
                    </div>
                    <div class="flex flex-row-reverse">
                        <div class="pos-dropdown dropdown ml-auto sm:ml-0">
                            <button class="dropdown-toggle btn px-2 box text-gray-700 dark:text-gray-300" aria-expanded="false">
                                <span class="w-5 h-5 flex items-center justify-center"> <ChevronDownIcon class="w-4 h-4" /> </span>
                            </button>
                            <div class="pos-dropdown__dropdown-menu dropdown-menu">
                                <div class="dropdown-menu__content box dark:bg-dark-1 p-2">
                                    <a href="" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md"><span class="truncate">INV-0206020 - Kevin Spacey</span> </a>
                                    <a href="" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md"><span class="truncate">INV-0206022 - Arnold Schwarzenegger</span> </a>
                                    <a href="" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md"><span class="truncate">INV-0206021 - Robert De Niro</span> </a>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary shadow-md mr-2">{{ t('components.buttons.new_order') }}</button>
                    </div>
                </div>
                <div class="grid grid-cols-12 gap-5 ">
                    <div class="intro-y col-span-12 lg:col-span-8">
                        <div class="lg:flex intro-y">
                            <div class="relative text-gray-700 dark:text-gray-300">
                                <input type="text" class="form-control py-3 px-4 w-full lg:w-64 box pr-10 placeholder-theme-13" placeholder="Search item...">
                                <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-feather="search"></i> 
                            </div>
                            <select class="form-select py-3 px-4 box w-full lg:w-auto mt-3 lg:mt-0 ml-auto">
                                <option>Sort By</option>
                                <option>A to Z</option>
                                <option>Z to A</option>
                                <option>Lowest Price</option>
                                <option>Highest Price</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-12 gap-5 mt-5">
                            <div class="col-span-12 sm:col-span-4 2xl:col-span-3 box p-5 cursor-pointer zoom-in">
                                <div class="font-medium text-base">Soup</div>
                                <div class="text-gray-600">5 Items</div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 2xl:col-span-3 box bg-theme-25 dark:bg-theme-25 p-5 cursor-pointer zoom-in">
                                <div class="font-medium text-base text-white">Pancake & Toast</div>
                                <div class="text-theme-34 dark:text-gray-400">8 Items</div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 2xl:col-span-3 box p-5 cursor-pointer zoom-in">
                                <div class="font-medium text-base">Pasta</div>
                                <div class="text-gray-600">4 Items</div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 2xl:col-span-3 box p-5 cursor-pointer zoom-in">
                                <div class="font-medium text-base">Waffle</div>
                                <div class="text-gray-600">3 Items</div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 2xl:col-span-3 box p-5 cursor-pointer zoom-in">
                                <div class="font-medium text-base">Snacks</div>
                                <div class="text-gray-600">8 Items</div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 2xl:col-span-3 box p-5 cursor-pointer zoom-in">
                                <div class="font-medium text-base">Deserts</div>
                                <div class="text-gray-600">8 Items</div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 2xl:col-span-3 box p-5 cursor-pointer zoom-in">
                                <div class="font-medium text-base">Beverage</div>
                                <div class="text-gray-600">9 Items</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-5 mt-5 pt-5 border-t border-theme-31">
                            <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3">
                                <div class="box rounded-md p-3 relative zoom-in">
                                    <div class="flex-none pos-image relative block">
                                        <div class="pos-image__preview image-fit">
                                            <img alt="Tinker Tailwind HTML Admin Template" src="dist/images/food-beverage-1.jpg">
                                        </div>
                                    </div>
                                    <div class="block font-medium text-center truncate mt-3">Vanilla Latte</div>
                                </div>
                            </a>
                            <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3">
                                <div class="box rounded-md p-3 relative zoom-in">
                                    <div class="flex-none pos-image relative block">
                                        <div class="pos-image__preview image-fit">
                                            <img alt="Tinker Tailwind HTML Admin Template" src="dist/images/food-beverage-17.jpg">
                                        </div>
                                    </div>
                                    <div class="block font-medium text-center truncate mt-3">Fried Calamari</div>
                                </div>
                            </a>
                            <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3">
                                <div class="box rounded-md p-3 relative zoom-in">
                                    <div class="flex-none pos-image relative block">
                                        <div class="pos-image__preview image-fit">
                                            <img alt="Tinker Tailwind HTML Admin Template" src="dist/images/food-beverage-4.jpg">
                                        </div>
                                    </div>
                                    <div class="block font-medium text-center truncate mt-3">Root Beer</div>
                                </div>
                            </a>
                            <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3">
                                <div class="box rounded-md p-3 relative zoom-in">
                                    <div class="flex-none pos-image relative block">
                                        <div class="pos-image__preview image-fit">
                                            <img alt="Tinker Tailwind HTML Admin Template" src="dist/images/food-beverage-15.jpg">
                                        </div>
                                    </div>
                                    <div class="block font-medium text-center truncate mt-3">Fried/Grilled Banana</div>
                                </div>
                            </a>
                            <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3">
                                <div class="box rounded-md p-3 relative zoom-in">
                                    <div class="flex-none pos-image relative block">
                                        <div class="pos-image__preview image-fit">
                                            <img alt="Tinker Tailwind HTML Admin Template" src="dist/images/food-beverage-18.jpg">
                                        </div>
                                    </div>
                                    <div class="block font-medium text-center truncate mt-3">Chicken Wings</div>
                                </div>
                            </a>
                            <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3">
                                <div class="box rounded-md p-3 relative zoom-in">
                                    <div class="flex-none pos-image relative block">
                                        <div class="pos-image__preview image-fit">
                                            <img alt="Tinker Tailwind HTML Admin Template" src="dist/images/food-beverage-16.jpg">
                                        </div>
                                    </div>
                                    <div class="block font-medium text-center truncate mt-3">Crispy Mushroom</div>
                                </div>
                            </a>
                            <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3">
                                <div class="box rounded-md p-3 relative zoom-in">
                                    <div class="flex-none pos-image relative block">
                                        <div class="pos-image__preview image-fit">
                                            <img alt="Tinker Tailwind HTML Admin Template" src="dist/images/food-beverage-18.jpg">
                                        </div>
                                    </div>
                                    <div class="block font-medium text-center truncate mt-3">Chicken Wings</div>
                                </div>
                            </a>
                            <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3">
                                <div class="box rounded-md p-3 relative zoom-in">
                                    <div class="flex-none pos-image relative block">
                                        <div class="pos-image__preview image-fit">
                                            <img alt="Tinker Tailwind HTML Admin Template" src="dist/images/food-beverage-12.jpg">
                                        </div>
                                    </div>
                                    <div class="block font-medium text-center truncate mt-3">French Fries</div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <!-- END: Item List -->
                    <!-- BEGIN: Ticket -->
                    <div class="col-span-12 lg:col-span-4">
                        <div class="intro-y pr-1">
                            <div class="box p-2">
                                <div class="pos__tabs nav nav-tabs justify-center" role="tablist"> <a id="ticket-tab" data-toggle="tab" data-target="#ticket" href="javascript:;" class="flex-1 py-2 rounded-md text-center active" role="tab" aria-controls="ticket" aria-selected="true">Ticket</a> <a id="details-tab" data-toggle="tab" data-target="#details" href="javascript:;" class="flex-1 py-2 rounded-md text-center" role="tab" aria-controls="details" aria-selected="false">Details</a> </div>
                            </div>
                        </div>
                        <div class="tab-content">
                            <div id="ticket" class="tab-pane active" role="tabpanel" aria-labelledby="ticket-tab">
                                <div class="pos__ticket box p-2 mt-5">
                                    <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="flex items-center p-3 cursor-pointer transition duration-300 ease-in-out bg-white dark:bg-dark-3 hover:bg-gray-200 dark:hover:bg-dark-1 rounded-md">
                                        <div class="pos__ticket__item-name truncate mr-1">Vanilla Latte</div>
                                        <div class="text-gray-600">x 1</div>
                                        <i data-feather="edit" class="w-4 h-4 text-gray-600 ml-2"></i> 
                                        <div class="ml-auto font-medium">$110</div>
                                    </a>
                                    <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="flex items-center p-3 cursor-pointer transition duration-300 ease-in-out bg-white dark:bg-dark-3 hover:bg-gray-200 dark:hover:bg-dark-1 rounded-md">
                                        <div class="pos__ticket__item-name truncate mr-1">Fried Calamari</div>
                                        <div class="text-gray-600">x 1</div>
                                        <i data-feather="edit" class="w-4 h-4 text-gray-600 ml-2"></i> 
                                        <div class="ml-auto font-medium">$103</div>
                                    </a>
                                    <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="flex items-center p-3 cursor-pointer transition duration-300 ease-in-out bg-white dark:bg-dark-3 hover:bg-gray-200 dark:hover:bg-dark-1 rounded-md">
                                        <div class="pos__ticket__item-name truncate mr-1">Root Beer</div>
                                        <div class="text-gray-600">x 1</div>
                                        <i data-feather="edit" class="w-4 h-4 text-gray-600 ml-2"></i> 
                                        <div class="ml-auto font-medium">$31</div>
                                    </a>
                                    <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="flex items-center p-3 cursor-pointer transition duration-300 ease-in-out bg-white dark:bg-dark-3 hover:bg-gray-200 dark:hover:bg-dark-1 rounded-md">
                                        <div class="pos__ticket__item-name truncate mr-1">Fried/Grilled Banana</div>
                                        <div class="text-gray-600">x 1</div>
                                        <i data-feather="edit" class="w-4 h-4 text-gray-600 ml-2"></i> 
                                        <div class="ml-auto font-medium">$108</div>
                                    </a>
                                    <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="flex items-center p-3 cursor-pointer transition duration-300 ease-in-out bg-white dark:bg-dark-3 hover:bg-gray-200 dark:hover:bg-dark-1 rounded-md">
                                        <div class="pos__ticket__item-name truncate mr-1">Chicken Wings</div>
                                        <div class="text-gray-600">x 1</div>
                                        <i data-feather="edit" class="w-4 h-4 text-gray-600 ml-2"></i> 
                                        <div class="ml-auto font-medium">$27</div>
                                    </a>
                                </div>
                                <div class="box flex p-5 mt-5">
                                    <div class="w-full relative text-gray-700">
                                        <input type="text" class="form-control py-3 px-4 w-full bg-gray-200 border-gray-200 pr-10 placeholder-theme-13" placeholder="Use coupon code...">
                                        <i class="w-4 h-4 hidden absolute-sm my-auto inset-y-0 mr-3 right-0" data-feather="search"></i> 
                                    </div>
                                    <button class="btn btn-primary ml-2">Apply</button>
                                </div>
                                <div class="box p-5 mt-5">
                                    <div class="flex">
                                        <div class="mr-auto">Subtotal</div>
                                        <div class="font-medium">$250</div>
                                    </div>
                                    <div class="flex mt-4">
                                        <div class="mr-auto">Discount</div>
                                        <div class="font-medium text-theme-21">-$20</div>
                                    </div>
                                    <div class="flex mt-4">
                                        <div class="mr-auto">Tax</div>
                                        <div class="font-medium">15%</div>
                                    </div>
                                    <div class="flex mt-4 pt-4 border-t border-gray-200 dark:border-dark-5">
                                        <div class="mr-auto font-medium text-base">Total Charge</div>
                                        <div class="font-medium text-base">$220</div>
                                    </div>
                                </div>
                                <div class="flex mt-5">
                                    <button class="btn w-32 border-gray-400 dark:border-dark-5 text-gray-600 dark:text-gray-300">Clear Items</button>
                                    <button class="btn btn-primary w-32 shadow-md ml-auto">Charge</button>
                                </div>
                            </div>
                            <div id="details" class="tab-pane" role="tabpanel" aria-labelledby="details-tab">
                                <div class="box p-5 mt-5">
                                    <div class="flex items-center border-b border-gray-200 dark:border-dark-5 pb-5">
                                        <div>
                                            <div class="text-gray-600">Time</div>
                                            <div class="mt-1">02/06/20 02:10 PM</div>
                                        </div>
                                        <i data-feather="clock" class="w-4 h-4 text-gray-600 ml-auto"></i> 
                                    </div>
                                    <div class="flex items-center border-b border-gray-200 dark:border-dark-5 py-5">
                                        <div>
                                            <div class="text-gray-600">Customer</div>
                                            <div class="mt-1">Johnny Depp</div>
                                        </div>
                                        <i data-feather="user" class="w-4 h-4 text-gray-600 ml-auto"></i> 
                                    </div>
                                    <div class="flex items-center border-b border-gray-200 dark:border-dark-5 py-5">
                                        <div>
                                            <div class="text-gray-600">People</div>
                                            <div class="mt-1">3</div>
                                        </div>
                                        <i data-feather="users" class="w-4 h-4 text-gray-600 ml-auto"></i> 
                                    </div>
                                    <div class="flex items-center pt-5">
                                        <div>
                                            <div class="text-gray-600">Table</div>
                                            <div class="mt-1">21</div>
                                        </div>
                                        <i data-feather="mic" class="w-4 h-4 text-gray-600 ml-auto"></i> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>











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
import { inject, onMounted, ref, computed, watch } from 'vue'
// Helper Import
import mainMixins from '../../mixins';
// Core Components Import
import { useStore } from '../../store/index';
// Components Import
import DataList from '../../global-components/data-list/Main'
import AlertPlaceholder from '../../global-components/alert-placeholder/Main'

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
const tabs = ref('');
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

function gotoTabs(tab) {
    tabs.value = tab;
}
// Computed
// Watcher
</script>