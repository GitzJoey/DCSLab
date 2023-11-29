<script setup lang="ts">
// #region Imports
import { onMounted, ref } from "vue";
import AlertPlaceholder from "../../base-components/AlertPlaceholder";
import DataList from "../../base-components/DataList";
import { useI18n } from "vue-i18n";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import Table from "../../base-components/Table";
import UserService from "../../services/UserService";
import { User } from "../../types/models/User";
import { Collection } from "../../types/resources/Collection";
import { Role } from "../../types/models/Role";
import { DataListEmittedData } from "../../base-components/DataList/DataList.vue";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { ReadAnyRequest } from "../../types/services/ServiceRequest";
import { useRouter } from "vue-router";
import { ViewMode } from "../../types/enums/ViewMode";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const userServices = new UserService();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state']);
// #endregion

// #region Refs
const datalistErrors = ref<Record<string, Array<string>> | null>(null);
const expandDetail = ref<number | null>(null);
const userLists = ref<Collection<Array<User>> | null>({
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
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emits('mode-state', ViewMode.LIST);
    await getUsers('', true, true, 1, 10);
});
// #endregion

// #region Methods
const getUsers = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {
    emits('loading-state', true);

    const searchReq: ReadAnyRequest = {
        search: search,
        refresh: refresh,
        paginate: paginate,
        page: page,
        per_page: per_page
    };

    let result: ServiceResponse<Collection<Array<User>> | Resource<Array<User>> | null> = await userServices.readAny(searchReq);

    if (result.success && result.data) {
        userLists.value = result.data as Collection<Array<User>>;
    } else {
        datalistErrors.value = result.errors as Record<string, Array<string>>;
    }

    emits('loading-state', false);
}

const onDataListChanged = async (data: DataListEmittedData) => {
    await getUsers(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const viewSelected = (idx: number) => {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
};

const editSelected = (itemIdx: number) => {
    if (!userLists.value) return;

    let ulid = userLists.value.data[itemIdx].ulid;
    router.push({ name: 'side-menu-administrator-user-edit', params: { ulid: ulid } });
}

const flattenedRoles = (roles: Array<Role>): string => {
    if (roles.length == 0) return '';
    return roles.map((x: Role) => x.display_name).join(', ');
}
// #endregion

// #region Watchers
// #endregion
</script>

<template>
    <AlertPlaceholder :errors="datalistErrors" />
    <DataList :title="t('views.user.table.title')" :enable-search="true" :can-print="true" :can-export="true"
        :pagination="userLists ? userLists.meta : null" @dataListChanged="onDataListChanged">
        <template #content>
            <Table class="mt-5" :hover="true">
                <Table.Thead variant="light">
                    <Table.Tr>
                        <Table.Th class="whitespace-nowrap">
                            {{ t("views.user.table.cols.name") }}
                        </Table.Th>
                        <Table.Th class="whitespace-nowrap">
                            {{ t("views.user.table.cols.email") }}
                        </Table.Th>
                        <Table.Th class="whitespace-nowrap">
                            {{ t("views.user.table.cols.roles") }}
                        </Table.Th>
                        <Table.Th class="whitespace-nowrap">
                            {{ t("views.user.table.cols.status") }}
                        </Table.Th>
                        <Table.Th class="whitespace-nowrap"></Table.Th>
                    </Table.Tr>
                </Table.Thead>
                <Table.Tbody v-if="userLists !== null">
                    <template v-if="userLists.data.length == 0">
                        <Table.Tr class="intro-x">
                            <Table.Td colspan="5">
                                <div class="flex justify-center italic">{{
                                    t('components.data-list.data_not_found') }}</div>
                            </Table.Td>
                        </Table.Tr>
                    </template>
                    <template v-for="( item, itemIdx ) in userLists.data" :key="item.ulid">
                        <Table.Tr class="intro-x">
                            <Table.Td>{{ item.name }}</Table.Td>
                            <Table.Td>
                                <a href="" class="hover:animate-pulse" @click.prevent="viewSelected(itemIdx)">
                                    {{ item.email }}
                                </a>
                            </Table.Td>
                            <Table.Td>
                                <span v-for=" r  in  item.roles " :key="r.id">{{ r.display_name }} </span>
                            </Table.Td>
                            <Table.Td>
                                <Lucide v-if="item.profile.status === 'ACTIVE'" icon="CheckCircle" />
                                <Lucide v-if="item.profile.status === 'INACTIVE'" icon="X" />
                            </Table.Td>
                            <Table.Td>
                                <div class="flex justify-end gap-1">
                                    <Button variant="outline-secondary" @click="viewSelected(itemIdx)">
                                        <Lucide icon="Info" class="w-4 h-4" />
                                    </Button>
                                    <Button variant="outline-secondary" @click="editSelected(itemIdx)">
                                        <Lucide icon="CheckSquare" class="w-4 h-4" />
                                    </Button>
                                </div>
                            </Table.Td>
                        </Table.Tr>
                        <Table.Tr :class="{ 'intro-x': true, 'hidden transition-all': expandDetail !== itemIdx }">
                            <Table.Td colspan="5">
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.name') }}
                                    </div>
                                    <div class="flex-1">{{ item.name }}</div>
                                </div>
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.email') }}
                                    </div>
                                    <div class="flex-1">{{ item.email }}</div>
                                </div>
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.first_name')
                                    }}</div>
                                    <div class="flex-1">{{ item.profile.first_name }}</div>
                                </div>
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.last_name')
                                    }}</div>
                                    <div class="flex-1">{{ item.profile.last_name }}</div>
                                </div>
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.address') }}
                                    </div>
                                    <div class="flex-1">{{ item.profile.address }}</div>
                                </div>
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.city') }}
                                    </div>
                                    <div class="flex-1">{{ item.profile.city }}</div>
                                </div>
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.postal_code')
                                    }}</div>
                                    <div class="flex-1">{{ item.profile.postal_code }}</div>
                                </div>
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.country') }}
                                    </div>
                                    <div class="flex-1">{{ item.profile.country }}</div>
                                </div>
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.picture') }}
                                    </div>
                                    <div class="flex-1">{{ item.profile.img_path }}</div>
                                </div>
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.tax_id') }}
                                    </div>
                                    <div class="flex-1">{{ item.profile.tax_id }}</div>
                                </div>
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.ic_num') }}
                                    </div>
                                    <div class="flex-1">{{ item.profile.ic_num }}</div>
                                </div>
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.remarks') }}
                                    </div>
                                    <div class="flex-1">{{ item.profile.remarks }}</div>
                                </div>
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.status') }}
                                    </div>
                                    <div class="flex-1">
                                        <span v-if="item.profile.status === 'ACTIVE'">
                                            {{ t('components.dropdown.values.statusDDL.active') }}
                                        </span>
                                        <span v-if="item.profile.status === 'INACTIVE'">
                                            {{ t('components.dropdown.values.statusDDL.inactive') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.roles') }}
                                    </div>
                                    <div class="flex-1">{{ flattenedRoles(item.roles) }}</div>
                                </div>
                            </Table.Td>
                        </Table.Tr>
                    </template>
                </Table.Tbody>
            </Table>
        </template>
    </DataList>
</template>
