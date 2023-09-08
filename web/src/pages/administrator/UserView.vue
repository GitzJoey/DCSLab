<script setup lang="ts">
//#region Imports
import { onMounted, ref, watch, computed } from "vue";
import AlertPlaceholder from "../../base-components/AlertPlaceholder";
import DataList from "../../base-components/DataList";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import { useI18n } from "vue-i18n";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import Table from "../../base-components/Table";
import {
    TitleLayout, TwoColumnsLayout
} from "../../base-components/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormTextarea,
    FormSelect,
    FormInputCode,
    FormFileUpload,
} from "../../base-components/Form";
import { ViewMode } from "../../types/enums/ViewMode";
import UserService from "../../services/UserService";
import { User } from "../../types/models/User";
import { UserFormFieldValues } from "../../types/forms/UserFormFieldValues";
import { Collection } from "../../types/resources/Collection";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { DataListEmittedData } from "../../base-components/DataList/DataList.vue";
import { Dialog } from "../../base-components/Headless";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import RoleService from "../../services/RoleService";
import { DropDownOption } from "../../types/models/DropDownOption";
import DashboardService from "../../services/DashboardService";
import { Role } from "../../types/models/Role";
import CacheService from "../../services/CacheService";
import { debounce } from "lodash";
import { CardState } from "../../types/enums/CardState";
import { SearchFormFieldValues } from "../../types/forms/SearchFormFieldValues";
import { client, useForm } from "laravel-precognition-vue";
//#endregion

//#region Interfaces
//#endregion

//#region Declarations
const { t } = useI18n();
const userServices = new UserService();
const roleServices = new RoleService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const datalistErrors = ref<Record<string, Array<string>> | null>(null);
const crudErrors = ref<Record<string, Array<string>>>({});
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'User Information', state: CardState.Expanded, },
    { title: 'User Profile', state: CardState.Expanded },
    { title: 'Roles', state: CardState.Expanded },
    { title: 'Settings', state: CardState.Expanded },
    { title: 'Token Managements', state: CardState.Expanded },
    { title: 'Password Managements', state: CardState.Expanded },
    { title: '', state: CardState.Hidden, id: 'button' }
]);
const deleteId = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
//#endregion

//#region Data - Views
client.axios().defaults.withCredentials = true;
const userForm2 = useForm('post', 'http://localhost:8000/api/post/dashboard/admin/user/save', {
    email: '',
});

const userForm = ref<Resource<User>>({
    data: {
        id: '',
        ulid: '',
        name: '',
        email: '',
        email_verified: false,
        profile: {
            first_name: '',
            last_name: '',
            address: '',
            city: '',
            postal_code: '',
            country: '',
            status: 'ACTIVE',
            tax_id: 0,
            ic_num: 0,
            img_path: '',
            remarks: '',
        },
        roles: [],
        companies: [],
        settings: {
            theme: 'side-menu-light-full',
            date_format: 'yyyy_MM_dd',
            time_format: 'hh_mm_ss',
        }
    }
});
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
const rolesDDL = ref<Array<Role> | null>(null);
const statusDDL = ref<Array<DropDownOption> | null>(null);
const countriesDDL = ref<Array<DropDownOption> | null>(null);
//#endregion

//#region onMounted
onMounted(async () => {
    await getUsers('', true, true, 1, 10);
    getDDL();
});
//#endregion

//#region Methods
const getUsers = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {
    loading.value = true;

    const searchReq: SearchFormFieldValues = {
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

    loading.value = false;
}

const getDDL = (): void => {
    roleServices.readAny().then((result: ServiceResponse<Resource<Array<Role>> | null>) => {
        if (result.success && result.data) {
            rolesDDL.value = result.data.data as Array<Role>;
        }
    });
    /*
    dashboardServices.getCountriesDDL().then((result: Array<DropDownOption> | null) => {
        countriesDDL.value = result;
    });

    dashboardServices.getStatusDDL().then((result: Array<DropDownOption> | null) => {
        statusDDL.value = result;
    });
    */
}

const emptyUser = () => {
    return {
        data: {
            id: '',
            ulid: '',
            name: '',
            email: '',
            email_verified: false,
            profile: {
                first_name: '',
                last_name: '',
                address: '',
                city: '',
                postal_code: '',
                country: '',
                status: 'ACTIVE',
                tax_id: 0,
                ic_num: 0,
                img_path: '',
                remarks: '',
            },
            roles: [],
            companies: [],
            settings: {
                theme: 'side-menu-light-full',
                date_format: 'yyyy_MM_dd',
                time_format: 'hh_mm_ss',
            }
        }
    }
}

const onDataListChanged = async (data: DataListEmittedData) => {
    await getUsers(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const createNew = () => {
    mode.value = ViewMode.FORM_CREATE;

    let cachedData: unknown | null = cacheServices.getLastEntity('User');

    userForm.value = cachedData == null ? emptyUser() : cachedData as Resource<User>;
}

const viewSelected = (idx: number) => {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
};

const editSelected = (itemIdx: number) => {
    mode.value = ViewMode.FORM_EDIT;
    userForm.value.data = userLists.value?.data[itemIdx] as User;
}

const deleteSelected = (itemUlid: string) => {
    deleteId.value = itemUlid;
    deleteModalShow.value = true;
}

const handleExpandCard = (index: number) => {
    if (cards.value[index].state === CardState.Collapsed) {
        cards.value[index].state = CardState.Expanded
    } else if (cards.value[index].state === CardState.Expanded) {
        cards.value[index].state = CardState.Collapsed
    }
}

const onSubmit = async () => {
    loading.value = true;

    let result: ServiceResponse<User | null> = {
        success: false,
    }

    if (mode.value == ViewMode.FORM_CREATE) {
        console.log('create');
        //result = await userServices.create(values);
    } else if (mode.value == ViewMode.FORM_EDIT) {
        console.log('edit');
        //result = await userServices.edit(userForm.value.data.ulid, values);
    } else {
        result.success = false;
    }

    if (!result.success) {
        console.log('error');
    } else {
        backToList();
    }

    loading.value = false;
};

const backToList = async () => {
    cacheServices.removeLastEntity('User');

    mode.value = ViewMode.LIST;
    await getUsers('', true, true, 1, 10);
}

const flattenedRoles = (roles: Array<Role>): string => {
    if (roles.length == 0) return '';
    return roles.map((x: Role) => x.display_name).join(', ');
}
//#endregion

//#region Computed
const titleView = computed((): string => {
    switch (mode.value) {
        case ViewMode.FORM_CREATE:
            return t('views.user.actions.create');
        case ViewMode.FORM_EDIT:
            return t('views.user.actions.edit');
        case ViewMode.LIST:
        default:
            return t('views.user.page_title');
    }
});
//#endregion

//#region Watcher
watch(
    userForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('User', newValue)
    }, 500),
    { deep: true }
);
//#endregion
</script>

<template>
    <div class="mt-8">
        <LoadingOverlay :visible="loading">
            <TitleLayout>
                <template #title>
                    {{ titleView }}
                </template>
                <template #optional>
                    <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
                        <Button v-if="mode == ViewMode.LIST" as="a" href="#" variant="primary" class="shadow-md"
                            @click="createNew">
                            <Lucide icon="Plus" class="w-4 h-4" />&nbsp;{{
                                t("components.buttons.create_new")
                            }}
                        </Button>
                    </div>
                </template>
            </TitleLayout>

            <div v-if="mode == ViewMode.LIST">
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
                                                <Button variant="outline-secondary" disabled
                                                    @click="deleteSelected(item.ulid)">
                                                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                                                </Button>
                                            </div>
                                        </Table.Td>
                                    </Table.Tr>
                                    <Table.Tr
                                        :class="{ 'intro-x': true, 'hidden transition-all': expandDetail !== itemIdx }">
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
                                    <Button type="button" variant="danger" class="w-24">
                                        {{ t('components.buttons.delete') }}
                                    </Button>
                                </div>
                            </Dialog.Panel>
                        </Dialog>
                    </template>
                </DataList>
            </div>
            <div v-else>
                <AlertPlaceholder :errors="crudErrors" />
                <form id="userForm" @submit="onSubmit">
                    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
                        <template #card-items-0>
                            <div class="p-5">
                                <div class="pb-4">
                                    <FormLabel html-for="name" :class="{ 'text-danger': false }">
                                        {{ t('views.user.fields.name') }}
                                    </FormLabel>
                                    <FormInput v-model="userForm.data.name" id="name" name="name" type="text"
                                        :class="{ 'border-danger': false }" :placeholder="t('views.user.fields.name')" />
                                </div>
                                <div class="pb-4">
                                    <FormLabel html-for="email" :class="{ 'text-danger': false }">
                                        {{ t('views.user.fields.email') }}
                                    </FormLabel>
                                    <FormInput v-model="userForm.data.email" id="email" name="email" type="text"
                                        :class="{ 'border-danger': false }" :placeholder="t('views.user.fields.email')"
                                        :readonly="mode === ViewMode.FORM_EDIT" />
                                </div>
                            </div>
                        </template>
                        <template #card-items-1>
                            <div class="p-5">
                                <div class="pb-4">
                                    <FormLabel html-for="first_name">{{ t('views.user.fields.first_name') }}</FormLabel>
                                    <FormInput v-model="userForm2.email" id="first_name" name="first_name" type="text"
                                        :class="{ 'border-danger': false }" :placeholder="t('views.user.fields.name')"
                                        @change="console.log('a'); userForm2.validate('email')" />
                                </div>
                                <div class="pb-4">
                                    <FormLabel html-for="last_name">{{ t('views.user.fields.last_name') }}</FormLabel>
                                    <FormInput v-model="userForm.data.profile.last_name" id="last_name" name="last_name"
                                        type="text" :placeholder="t('views.user.fields.last_name')" />
                                </div>
                                <div class="pb-4">
                                    <FormLabel html-for="address" class="form-label">{{ t('views.user.fields.address') }}
                                    </FormLabel>
                                    <FormInput v-model="userForm.data.profile.address" id="address" name="address"
                                        type="text" :placeholder="t('views.user.fields.address')" />
                                </div>
                                <div class="pb-4">
                                    <FormLabel html-for="city">{{ t('views.user.fields.city') }}</FormLabel>
                                    <FormInput v-model="userForm.data.profile.city" id="city" name="city" type="text"
                                        class="form-control" :placeholder="t('views.user.fields.city')" />
                                </div>
                                <div class="pb-4">
                                    <FormLabel html-for="postal_code">{{ t('views.user.fields.postal_code') }}
                                    </FormLabel>
                                    <FormInput v-model="userForm.data.profile.postal_code" id="postal_code"
                                        name="postal_code" type="text" :placeholder="t('views.user.fields.postal_code')" />
                                </div>
                                <div class="pb-4">
                                    <FormLabel html-for="country" :class="{ 'text-danger': false }">
                                        {{ t('views.user.fields.country') }}
                                    </FormLabel>
                                    <FormSelect v-model="userForm.data.profile.country" id="country" name="country"
                                        :class="{ 'border-danger': false }" :placeholder="t('views.user.fields.country')">
                                        <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                        <option v-for="c in countriesDDL" :key="c.name" :value="c.name">{{ c.name }}
                                        </option>
                                    </FormSelect>
                                </div>
                                <div class="pb-4">
                                    <FormLabel html-for="img_path" :class="{ 'text-danger': false }">
                                        {{ t('views.user.fields.picture') }}
                                    </FormLabel>
                                    <FormFileUpload id="img_path" v-model="userForm.data.profile.img_path" name="img_path"
                                        type="text" :class="{ 'border-danger': false }"
                                        :placeholder="t('views.user.fields.picture')" />
                                </div>
                                <div class="pb-4">
                                    <FormLabel html-for="tax_id" :class="{ 'text-danger': false }">
                                        {{ t('views.user.fields.tax_id') }}
                                    </FormLabel>
                                    <FormInput v-model="userForm.data.profile.tax_id" id="tax_id" name="tax_id" type="text"
                                        :class="{ 'border-danger': false }" />
                                </div>
                                <div class="pb-4">
                                    <FormLabel html-for="ic_num" :class="{ 'text-danger': false }">
                                        {{ t('views.user.fields.ic_num') }}
                                    </FormLabel>
                                    <FormInput id="ic_num" v-model="userForm.data.profile.ic_num" name="ic_num" type="text"
                                        :class="{ 'border-danger': false }" />
                                </div>
                                <div class="pb-4">
                                    <FormLabel html-for="status" :class="{ 'text-danger': false }">
                                        {{ t('views.user.fields.status') }}
                                    </FormLabel>
                                    <FormSelect id="status" v-model="userForm.data.profile.status" name="status"
                                        :class="{ 'border-danger': false }">
                                        <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                        <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}
                                        </option>
                                    </FormSelect>
                                </div>
                                <div class="pb-4">
                                    <FormLabel html-for="remarks" :class="{ 'text-danger': false }">
                                        {{ t('views.user.fields.remarks') }}
                                    </FormLabel>
                                    <FormTextarea id="remarks" v-model="userForm.data.profile.remarks" name="remarks"
                                        type="text" :placeholder="t('views.user.fields.remarks')" rows="3" />
                                </div>
                            </div>
                        </template>
                        <template #card-items-2>
                            <div class="p-5">
                                <div class="pb-4">
                                    <FormLabel html-for="roles" :class="{ 'text-danger': false }">
                                        {{ t('views.user.fields.roles') }}
                                    </FormLabel>
                                    <FormSelect id="roles" v-model="userForm.data.roles" multiple size="6"
                                        :class="{ 'border-danger': false }">
                                        <option v-for="r in rolesDDL" :key="r.id" :value="r">
                                            {{ r.display_name }}
                                        </option>
                                    </FormSelect>
                                </div>
                            </div>
                        </template>
                        <template #card-items-3>
                            <div class="p-5">
                                <div class="pb-4">
                                    <FormLabel html-for="theme">
                                        {{ t('views.user.fields.settings.theme') }}
                                    </FormLabel>
                                    <FormSelect v-model="userForm.data.settings.theme"
                                        v-show="mode == ViewMode.FORM_CREATE || mode == ViewMode.FORM_EDIT" id="theme"
                                        name="theme">
                                        <option value="side-menu-light-full">Menu Light</option>
                                        <option value="side-menu-light-mini">Mini Menu Light</option>
                                        <option value="side-menu-dark-full">Menu Dark</option>
                                        <option value="side-menu-dark-mini">Mini Menu Dark</option>
                                    </FormSelect>
                                </div>
                                <div class="pb-4">
                                    <FormLabel html-for="date_format">
                                        {{ t('views.user.fields.settings.date_format') }}
                                    </FormLabel>
                                    <FormSelect v-model="userForm.data.settings.date_format"
                                        v-show="mode == ViewMode.FORM_CREATE || mode == ViewMode.FORM_EDIT" id="date_format"
                                        name="date_format">
                                        <option value="yyyy_MM_dd">{{ 'YYYY-MM-DD' }}</option>
                                        <option value="dd_MMM_yyyy">{{ 'DD-MMM-YYYY' }}</option>
                                    </FormSelect>
                                </div>
                                <div class="pb-4">
                                    <FormLabel html-for="time_format">
                                        {{ t('views.user.fields.settings.time_format') }}
                                    </FormLabel>
                                    <FormSelect v-model="userForm.data.settings.time_format"
                                        v-show="mode == ViewMode.FORM_CREATE || mode == ViewMode.FORM_EDIT" id="time_format"
                                        name="time_format">
                                        <option value="hh_mm_ss">{{ 'HH:mm:ss' }}</option>
                                        <option value="h_m_A">{{ 'H:m A' }}</option>
                                    </FormSelect>
                                </div>
                            </div>
                        </template>
                        <template #card-items-4>
                            <div class="p-5">
                                <div class="pb-4">
                                    <FormLabel html-for="tokens_reset">
                                        {{ t('views.user.fields.tokens.reset') }}
                                    </FormLabel>

                                </div>
                            </div>
                        </template>
                        <template #card-items-5>
                            <div class="p-5">
                                <div class="pb-4">

                                </div>
                            </div>
                        </template>
                        <template #card-items-button>
                            <div class="flex gap-4">
                                <Button type="submit" href="#" variant="primary" class="w-28 shadow-md">
                                    {{ t("components.buttons.submit") }}
                                </Button>
                                <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md">
                                    {{ t("components.buttons.reset") }}
                                </Button>
                            </div>
                        </template>
                    </TwoColumnsLayout>
                </form>
                <Button as="button" variant="secondary" class="mt-2 w-24" @click="backToList">
                    {{ t('components.buttons.back') }}
                </Button>
            </div>
        </LoadingOverlay>
    </div>
</template>
