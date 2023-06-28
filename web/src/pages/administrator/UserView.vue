<script setup lang="ts">
//#region Imports
import { onMounted, ref, watch } from "vue";
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
} from "../../base-components/Form";
import { ViewMode } from "../../types/enums/ViewMode";
import UserService from "../../services/UserService";
import { User } from "../../types/models/User";
import { Collection } from "../../types/resources/Collection";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { DataListEmittedData } from "../../base-components/DataList/DataList.vue";
import { Dialog } from "../../base-components/Headless";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import RoleService from "../../services/RoleService";
import { DropDownOption } from "../../types/services/DropDownOption";
import { FormRequest } from "../../types/requests/FormRequest";
import DashboardService from "../../services/DashboardService";
import { Role } from "../../types/models/Role";
import CacheService from "../../services/CacheService";
import { debounce } from "lodash";
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
const datalistErrors = ref<Record<string, string[]> | null>(null);
const cards: Array<TwoColumnsLayoutCards> = [
  { title: 'User Information', active: true },
  { title: 'User Profile', active: true },
  { title: 'Roles', active: true },
  { title: 'Settings', active: true },
  { title: 'Token Managements', active: true },
  { title: 'Password Managements', active: true },
];
const deleteId = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
//#endregion

//#region Data - Views
const userForm = ref<FormRequest<User>>({
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
const userLists = ref<Collection<User[]> | null>({
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
  await getDDL();
});

//#endregion

//#region Computed
//#endregion

//#region Methods
const toggleDetail = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const getUsers = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {
  let result: ServiceResponse<Collection<User[]> | Resource<User[]> | null> = await userServices.readAny(
    search,
    refresh,
    paginate,
    page,
    per_page
  );

  if (result.success && result.data) {
    userLists.value = result.data as Collection<User[]>;
  } else {
    datalistErrors.value = result.errors as Record<string, string[]>;
  }
}

const getRoles = async () => {
  let result: ServiceResponse<Resource<Array<Role>> | null> = await roleServices.readAny();

  if (result.success && result.data) {
    rolesDDL.value = result.data.data as Array<Role>;
  }
}

const getDDL = async (): Promise<void> => {
  await getRoles();
  countriesDDL.value = await dashboardServices.getCountriesDDL();
  statusDDL.value = await dashboardServices.getStatusDDL();
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

const onDataListChanged = (data: DataListEmittedData) => {
  getUsers(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const createNew = () => {
  mode.value = ViewMode.FORM_CREATE;

  let cachedData: unknown | null = cacheServices.getLastEntity('User');

  userForm.value = cachedData == null ? emptyUser() : cachedData as FormRequest<User>;
}

const editSelected = (itemIdx: number) => {
  mode.value = ViewMode.FORM_EDIT;
  userForm.value.data = userLists.value?.data[itemIdx] as User;
}

const deleteSelected = (itemUlid: string) => {
  deleteId.value = itemUlid;
  deleteModalShow.value = true;
}

const onSubmit = async () => {
  loading.value = true;

  loading.value = false;
};
//#endregion

//#region Watcher
watch(
  userForm,
  debounce((newValue): void => {
    if (mode.value != ViewMode.FORM_CREATE) return;
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
        <template #title>{{ t("views.user.page_title") }}</template>
        <template #optional>
          <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
            <Button v-if="mode == ViewMode.LIST" as="a" href="#" variant="primary" class="shadow-md" @click="createNew">
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
                      <div class="flex justify-center italic">{{ t('components.data-list.data_not_found') }}</div>
                    </Table.Td>
                  </Table.Tr>
                </template>
                <template v-for="( item, itemIdx ) in userLists.data" :key="item.ulid">
                  <Table.Tr class="intro-x">
                    <Table.Td>{{ item.name }}</Table.Td>
                    <Table.Td>
                      <a href="" class="hover:animate-pulse" @click.prevent="toggleDetail(itemIdx)">
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
                        <Button variant="outline-secondary" @click="toggleDetail(itemIdx)">
                          <Lucide icon="Info" class="w-4 h-4" />
                        </Button>
                        <Button variant="outline-secondary" @click="editSelected(itemIdx)">
                          <Lucide icon="CheckSquare" class="w-4 h-4" />
                        </Button>
                        <Button variant="outline-secondary" disabled @click="deleteSelected(item.ulid)">
                          <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                        </Button>
                      </div>
                    </Table.Td>
                  </Table.Tr>
                  <Table.Tr :class="{ 'intro-x': true, 'hidden transition-all': expandDetail !== itemIdx }">
                    <Table.Td colspan="5">
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.name') }}</div>
                        <div class="flex-1">{{ item.name }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.email') }}</div>
                        <div class="flex-1">{{ item.email }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.roles') }}</div>
                        <div class="flex-1">{{ '' }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.status') }}</div>
                        <div class="flex-1">
                          <span v-if="item.profile.status === 'ACTIVE'">
                            {{ t('components.dropdown.values.statusDDL.active') }}
                          </span>
                          <span v-if="item.profile.status === 'INACTIVE'">
                            {{ t('components.dropdown.values.statusDDL.inactive') }}
                          </span>
                        </div>
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
        <VeeForm id="userForm" v-slot="{ errors }" @submit="onSubmit">
          <AlertPlaceholder :errors="errors" />
          <TwoColumnsLayout :cards="cards" :show-side-tab="true">
            <template #card-items-0>
              <div class="p-5">
                <div class="pb-4">
                  <FormLabel html-for="name" :class="{ 'text-danger': errors['name'] }">
                    {{ t('views.user.fields.name') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="name" rules="required|alpha_num"
                    :label="t('views.user.fields.name')">
                    <FormInput id="name" v-model="userForm.data.name" v-bind="field" name="name" type="text"
                      :class="{ 'border-danger': errors['name'] }" :placeholder="t('views.user.fields.name')" />
                  </VeeField>
                  <VeeErrorMessage name="name" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="email" :class="{ 'text-danger': errors['email'] }">
                    {{ t('views.user.fields.email') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="email" rules="required|email" :label="t('views.user.fields.email')">
                    <FormInput id="email" v-model="userForm.data.email" v-bind="field" name="email" type="text"
                      :class="{ 'border-danger': errors['email'] }" :placeholder="t('views.user.fields.email')"
                      :readonly="mode === ViewMode.FORM_EDIT" />
                  </VeeField>
                  <VeeErrorMessage name="email" class="mt-2 text-danger" />
                </div>
              </div>
            </template>
            <template #card-items-1>
              <div class="p-5">
                <div class="pb-4">
                  <FormLabel html-for="first_name">{{ t('views.user.fields.first_name') }}</FormLabel>
                  <FormInput id="first_name" v-model="userForm.data.profile.first_name" name="first_name" type="text"
                    :placeholder="t('views.user.fields.first_name')" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="last_name">{{ t('views.user.fields.last_name') }}</FormLabel>
                  <FormInput id="last_name" v-model="userForm.data.profile.last_name" name="last_name" type="text"
                    :placeholder="t('views.user.fields.last_name')" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="address" class="form-label">{{ t('views.user.fields.address') }}</FormLabel>
                  <FormInput id="address" v-model="userForm.data.profile.address" name="address" type="text"
                    :placeholder="t('views.user.fields.address')" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="city">{{ t('views.user.fields.city') }}</FormLabel>
                  <FormInput id="city" v-model="userForm.data.profile.city" name="city" type="text" class="form-control"
                    :placeholder="t('views.user.fields.city')" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="postal_code">{{ t('views.user.fields.postal_code') }}</FormLabel>
                  <FormInput id="postal_code" v-model="userForm.data.profile.postal_code" name="postal_code" type="text"
                    :placeholder="t('views.user.fields.postal_code')" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="country" :class="{ 'text-danger': errors['country'] }">
                    {{ t('views.user.fields.country') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="country" rules="required" :label="t('views.user.fields.country')">
                    <FormSelect id="country" v-model="userForm.data.profile.country" v-bind="field" name="country"
                      :class="{ 'border-danger': errors['country'] }" :placeholder="t('views.user.fields.country')">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="c in countriesDDL" :key="c.name" :value="c.name">{{ c.name }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="country" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="tax_id" :class="{ 'text-danger': errors['tax_id'] }">
                    {{ t('views.user.fields.tax_id') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="tax_id" rules="required" :placeholder="t('views.user.fields.tax_id')"
                    :label="t('views.user.fields.tax_id')">
                    <FormInput id="tax_id" v-model="userForm.data.profile.tax_id" v-bind="field" name="tax_id" type="text"
                      :class="{ 'border-danger': errors['tax_id'] }" />
                  </VeeField>
                  <VeeErrorMessage name="tax_id" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="ic_num" :class="{ 'text-danger': errors['ic_num'] }">
                    {{ t('views.user.fields.ic_num') }}
                  </FormLabel>
                  <VeeField rules="required" name="ic_num" :label="t('views.user.fields.ic_num')">
                    <FormInput id="ic_num" v-model="userForm.data.profile.ic_num" name="ic_num" type="text"
                      :class="{ 'border-danger': errors['ic_num'] }" :placeholder="t('views.user.fields.ic_num')" />
                  </VeeField>
                  <VeeErrorMessage name="ic_num" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="status" :class="{ 'text-danger': errors['status'] }">
                    {{ t('views.user.fields.status') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="status" rules="required" :label="t('views.user.fields.status')">
                    <FormSelect id="status" v-model="userForm.data.profile.status" v-bind="field" name="status"
                      :class="{ 'border-danger': errors['status'] }">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="status" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="remarks" :class="{ 'text-danger': errors['remarks'] }">
                    {{ t('views.user.fields.remarks') }}
                  </FormLabel>
                  <FormTextarea id="remarks" v-model="userForm.data.profile.remarks" name="remarks" type="text"
                    :placeholder="t('views.user.fields.remarks')" rows="3" />
                </div>
              </div>
            </template>
            <template #card-items-2>
              <div class="p-5">
                <div class="pb-4">
                  <FormLabel html-for="roles" :class="{ 'text-danger': errors['roles[]'] }">
                    {{ t('views.user.fields.roles') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="roles[]" rules="required" :label="t('views.user.fields.roles')">
                    <FormSelect id="roles" v-model="userForm.data.roles" multiple size="6" v-bind="field"
                      :class="{ 'border-danger': errors['roles[]'] }">
                      <option v-for="r in rolesDDL" :key="r.id" :value="r">
                        {{ r.display_name }}
                      </option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="roles[]" class="mt-2 text-danger" />
                </div>
              </div>
            </template>
            <template #card-items-3>
              <div class="p-5">
                <div class="pb-4">
                  <FormLabel html-for="theme">
                    {{ t('views.user.fields.settings.theme') }}
                  </FormLabel>
                  <FormSelect v-show="mode == ViewMode.FORM_CREATE || mode == ViewMode.FORM_EDIT" id="theme"
                    v-model="userForm.data.settings.theme" name="theme">
                    <option value="side-menu-light-full">Menu Light</option>
                    <option value="side-menu-light-mini">Mini Menu Light</option>
                    <option value="side-menu-dark-full">Menu Dark</option>
                    <option value="side-menu-dark-mini">Mini Menu Dark</option>
                  </FormSelect>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="format_date">
                    {{ t('views.user.fields.settings.date_format') }}
                  </FormLabel>
                  <FormSelect v-show="mode == ViewMode.FORM_CREATE || mode == ViewMode.FORM_EDIT" id="format_date"
                    v-model="userForm.data.settings.date_format" name="date_format">
                    <option value="yyyy_MM_dd">{{ 'YYYY-MM-DD' }}</option>
                    <option value="dd_MMM_yyyy">{{ 'DD-MMM-YYYY' }}</option>
                  </FormSelect>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="format_time">
                    {{ t('views.user.fields.settings.time_format') }}
                  </FormLabel>
                  <FormSelect v-show="mode == ViewMode.FORM_CREATE || mode == ViewMode.FORM_EDIT" id="format_time"
                    v-model="userForm.data.settings.time_format" name="format_time">
                    <option value="hh_mm_ss">{{ 'HH:mm:ss' }}</option>
                    <option value="h_m_A">{{ 'H:m A' }}</option>
                  </FormSelect>
                </div>
              </div>
            </template>
            <template #card-items-4>
              <div class="p-5">
                <div class="pb-4">

                </div>
              </div>
            </template>
            <template #card-items-5>
              <div class="p-5">
                <div class="pb-4">

                </div>
              </div>
            </template>
          </TwoColumnsLayout>
          <Button as="submit" href="#" variant="primary" class="shadow-md">
            <Lucide icon="Plus" class="w-4 h-4" />&nbsp;{{ t("components.buttons.submit") }}
          </Button>
        </VeeForm>
      </div>
    </LoadingOverlay>
  </div>
</template>
