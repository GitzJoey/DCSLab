<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref } from "vue";
import DataList from "@/components/DataList";
import { useI18n } from "vue-i18n";
import Button from "@/components/Base/Button";
import Lucide from "@/components/Base/Lucide";
import Table from "@/components/Base/Table";
import CustomerGroupService from "@/services/CustomerGroupService";
import { CustomerGroup } from "@/types/models/CustomerGroup";
import { Collection } from "@/types/resources/Collection";
import { DataListEmittedData } from "@/components/DataList/DataList.vue";
import { ServiceResponse } from "@/types/services/ServiceResponse";
import { CustomerGroupReadAnyPaginateRequest } from "@/types/services/customer-group/CustomerGroupRequest";
import { useRouter } from "vue-router";
import { Dialog } from "@/components/Base/Headless";
import { ViewMode } from "@/types/enums/ViewMode";
import { NotificationData } from "@/types/models/NotificationData";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { ErrorCode } from "@/types/enums/ErrorCode";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const customerGroupServices = new CustomerGroupService();
const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder', 'show-notification']);
// #endregion

// #region Refs
const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
const customerGroupLists = ref<Collection<Array<CustomerGroup>> | null>({
    data: [],
    meta: {
        current_page: 0,
        from: null,
        last_page: 0,
        path: '',
        per_page: 0,
        to: null,
        total: 0,
    },
    links: {
        first: '',
        last: '',
        prev: null,
        next: null,
    }
});
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emits('mode-state', ViewMode.LIST);

    if (!isUserLocationSelected.value) {
        router.push({ name: 'side-menu-error-code', params: { code: ErrorCode.USERLOCATION_REQUIRED } });
    }

    await getCustomerGroups('', true, true, 1, 10);
});
// #endregion

// #region Methods
const getCustomerGroups = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {
    emits('loading-state', true);

    let company_id = selectedUserLocation.value.company.id;

    const searchReq: CustomerGroupReadAnyPaginateRequest = {
        with_trashed: false,

        company_id: company_id,
        search: search,
        include_id: undefined,

        refresh: refresh,
        page: page,
        per_page: per_page,
    };

    let result: ServiceResponse<Collection<Array<CustomerGroup>> | null> = await customerGroupServices.readAnyPaginate(searchReq);

    if (result.success && result.data) {
        customerGroupLists.value = result.data as Collection<Array<CustomerGroup>>;
    } else {
        showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
    }

    emits('loading-state', false);
};

const onDataListChanged = async (data: DataListEmittedData) => {
    await getCustomerGroups(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
};

const viewSelected = (idx: number) => {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
};

const editSelected = (itemIdx: number) => {
    if (!customerGroupLists.value) return;

    let ulid = customerGroupLists.value.data[itemIdx].ulid;
    router.push({ name: 'side-menu-customer-group-edit', params: { ulid: ulid } });
};

const deleteSelected = (itemIdx: number) => {
    if (!customerGroupLists.value) return;

    let itemUlid = customerGroupLists.value.data[itemIdx].ulid;

    deleteUlid.value = itemUlid;
    deleteModalShow.value = true;
};

const confirmDelete = async () => {
    deleteModalShow.value = false;
    emits('loading-state', true);

    let result: ServiceResponse<boolean | null> = await customerGroupServices.delete(deleteUlid.value); // Diubah dari companyServices

    if (result.success) {
        emits('update-profile');
        await getCustomerGroups('', true, true, 1, 10); // Diubah dari getCompanies
        showNotification(t('views.customer_group.alert.delete_customer_group.title'), t('views.customer_group.alert.delete_customer_group.content')); // Diubah path terjemahan
    } else {
        showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
    }

    emits('loading-state', false);
};

const showNotification = (pTitle: string, pContent: string) => {
    let n: NotificationData = {
        title: pTitle,
        content: pContent
    };

    emits('show-notification', n);
};

const showAlertPlaceholder = (pAlertType: 'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark', pTitle: string, pAlertList: Record<string, Array<string>> | null) => {
    let ap: AlertPlaceholderProps = {
        alertType: pAlertType,
        title: pTitle,
        alertList: pAlertList,
    };

    emits('show-alertplaceholder', ap);
};
// #endregion

// #region Watchers
// #endregion
</script>

<template>
    <DataList :title="t('views.customer_group.table.title')" :enable-search="true" :can-print="true" :can-export="true"
        :pagination="customerGroupLists ? customerGroupLists.meta : null" @dataListChanged="onDataListChanged">
        <template #content>
            <Table class="mt-5" :hover="true">
                <Table.Thead variant="light">
                    <Table.Tr>
                        <Table.Th class="whitespace-nowrap">
                            {{ t("views.customer_group.table.cols.code") }}
                        </Table.Th>
                        <Table.Th class="whitespace-nowrap">
                            {{ t("views.customer_group.table.cols.name") }}
                        </Table.Th>
                        <Table.Th class="whitespace-nowrap"></Table.Th>
                    </Table.Tr>
                </Table.Thead>
                <Table.Tbody v-if="customerGroupLists !== null">
                    <template v-if="customerGroupLists.data.length == 0">
                        <Table.Tr class="intro-x">
                            <Table.Td colspan="5">
                                <div class="flex justify-center italic">{{
                                    t('components.data-list.data_not_found') }}</div>
                            </Table.Td>
                        </Table.Tr>
                    </template>
                    <template v-for="(item, itemIdx) in customerGroupLists.data" :key="item.ulid">
                        <Table.Tr class="intro-x">
                            <Table.Td>{{ item.code }}</Table.Td>
                            <Table.Td>{{ item.name }}</Table.Td>
                            <Table.Td>
                                <div class="flex justify-end gap-1">
                                    <Button variant="outline-secondary" @click="viewSelected(itemIdx)">
                                        <Lucide icon="Info" class="w-4 h-4" />
                                    </Button>
                                    <Button variant="outline-secondary" @click="editSelected(itemIdx)">
                                        <Lucide icon="Pen" class="w-4 h-4" />
                                    </Button>
                                    <Button variant="outline-secondary" @click="deleteSelected(itemIdx)">
                                        <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                                    </Button>
                                </div>
                            </Table.Td>
                        </Table.Tr>
                        <Table.Tr :class="{ 'intro-x': true, 'hidden transition-all': expandDetail !== itemIdx }">
                            <Table.Td colspan="5">
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.code') }}</div>
                                    <div class="flex-1">{{ item.code }}</div>
                                </div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.name') }}</div>
                                    <div class="flex-1">{{ item.name }}</div>
                                </div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.remarks') }}</div>
                                    <div class="flex-1">{{ item.remarks || '-' }}</div>
                                </div>

                                <div class="w-full border-b my-3"></div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.max_open_invoice') }}</div>
                                    <div class="flex-1">{{ item.max_open_invoice }}</div>
                                </div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.max_outstanding_invoice') }}</div>
                                    <div class="flex-1">{{ item.max_outstanding_invoice }}</div>
                                </div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.max_invoice_age') }}</div>
                                    <div class="flex-1">{{ item.max_invoice_age }}</div>
                                </div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.payment_term_type') }}</div>
                                    <div class="flex-1">{{ item.payment_term_type }}</div>
                                </div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.payment_term') }}</div>
                                    <div class="flex-1">{{ item.payment_term }}</div>
                                </div>

                                <div class="w-full border-b my-3"></div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.sell_at_cost') }}</div>
                                    <div class="flex-1">
                                        <span v-if="item.sell_at_cost">{{ t('components.dropdown.values.switch.on')
                                            }}</span>
                                        <span v-else>{{ t('components.dropdown.values.switch.off') }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.selling_point') }}</div>
                                    <div class="flex-1">{{ item.selling_point }}</div>
                                </div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.selling_point_multiple') }}</div>
                                    <div class="flex-1">{{ item.selling_point_multiple }}</div>
                                </div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.price_markup_percent') }}</div>
                                    <div class="flex-1">{{ item.price_markup_percent }}%</div>
                                </div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.price_markup_nominal') }}</div>
                                    <div class="flex-1">{{ item.price_markup_nominal }}</div>
                                </div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.price_markdown_percent') }}</div>
                                    <div class="flex-1">{{ item.price_markdown_percent }}%</div>
                                </div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.price_markdown_nominal') }}</div>
                                    <div class="flex-1">{{ item.price_markdown_nominal }}</div>
                                </div>

                                <div class="w-full border-b my-3"></div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.rounding_type') }}</div>
                                    <div class="flex-1">{{ item.rounding_type }}</div>
                                </div>
                                <div class="flex flex-row mt-1">
                                    <div class="ml-5 w-48 text-right pr-5 font-medium">{{
                                        t('views.customer_group.fields.rounding_digit') }}</div>
                                    <div class="flex-1">{{ item.rounding_digit }}</div>
                                </div>
                            </Table.Td>
                        </Table.Tr>
                    </template>
                </Table.Tbody>
            </Table>
            <Dialog :open="deleteModalShow" @close="() => { deleteModalShow = false; }">
                <Dialog.Panel>
                    <div class="p-5 text-center">
                        <Lucide icon="XCircle" class="w-16 h-16 mx-auto mt-3 text-danger" />
                        <div class="mt-5 text-3xl">{{ t('components.delete-modal.title') }}</div>
                        <div class="mt-2 text-slate-500">
                            {{ t('components.delete-modal.desc_1') }}
                            <br />
                            {{ t('components.delete-modal.desc_2') }}
                        </div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <Button type="button" variant="outline-secondary" class="w-24 mr-1"
                            @click="() => { deleteModalShow = false; }">
                            {{ t('components.buttons.cancel') }}
                        </Button>
                        <Button type="button" variant="danger" class="w-24" @click="(confirmDelete)">
                            {{ t('components.buttons.delete') }}
                        </Button>
                    </div>
                </Dialog.Panel>
            </Dialog>
        </template>
    </DataList>
</template>
