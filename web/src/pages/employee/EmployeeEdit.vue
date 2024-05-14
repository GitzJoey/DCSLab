<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import EmployeeService from "../../services/EmployeeService";
import CompanyService from "../../services/CompanyService";
import DashboardService from "../../services/DashboardService";
import CacheService from "../../services/CacheService";
import { DropDownOption } from "../../types/models/DropDownOption";
import { TwoColumnsLayout } from "../../base-components/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormTextarea,
    FormSelect,
    FormInputCode,
    FormErrorMessages,
    FormFileUpload
} from "../../base-components/Form";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "../../types/enums/CardState";
import Button from "../../base-components/Button";
import { ViewMode } from "../../types/enums/ViewMode";
import { debounce, forEach } from "lodash";
import Table from "../../base-components/Table";
import Lucide from "../../base-components/Lucide";
import { useSelectedUserLocationStore } from "../../stores/user-location";
import { useRoute, useRouter } from "vue-router";
import { ErrorCode } from "../../types/enums/ErrorCode";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Employee } from "../../types/models/Employee";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const route = useRoute();

const employeeServices = new EmployeeService();
const companyServices = new CompanyService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();

const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'views.employee.field_groups.company_info', state: CardState.Expanded, },
    { title: 'views.employee.field_groups.employee_data', state: CardState.Expanded, },
    { title: 'views.employee.field_groups.employee_access', state: CardState.Expanded, },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const statusDDL = ref<Array<DropDownOption> | null>(null);
const countriesDDL = ref<Array<DropDownOption> | null>(null);
const accessLists = ref<Record<string, any>>({});

const employeeForm = employeeServices.useEmployeeEditForm(route.params.ulid as string);
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emits('mode-state', ViewMode.FORM_EDIT);

    if (!isUserLocationSelected.value) {
        router.push({ name: 'side-menu-error-code', params: { code: ErrorCode.USERLOCATION_REQUIRED } });
    }

    getDDL();
    getAccessList();
    setCompanyIdData();

    await loadData(route.params.ulid as string);
});
// #endregion

// #region Methods
const getAccessList = () => {
    companyServices.read(selectedUserLocation.value.company.ulid).then((result) => {
        if (result.success && result.data) {
            accessLists.value = [];

            accessLists.value = result.data.branches as Record<string, any>;
        }
    })
}

const setCompanyIdData = () => {
    employeeForm.setData({ company_id: selectedUserLocation.value.company.id });
}

const updateAccessList = (id: string) => {
    const index = employeeForm.arr_access_branch_id.indexOf(id);
    if (index === -1) {
        employeeForm.arr_access_branch_id.push(id);
    } else {
        // Jika id sudah ada dalam array, hapus
        employeeForm.arr_access_branch_id.splice(index, 1);
    }
}

const loadData = async (ulid: string) => {
    emits('loading-state', true);
    let response: ServiceResponse<Employee | null> = await employeeServices.read(ulid);

    if (response && response.data) {
        employeeForm.setData({
            company_id: response.data.company.id,
            code: response.data.code,
            name: response.data.user.name,
            email: response.data.user.email,
            address: response.data.user.profile.address,
            city: response.data.user.profile.city,
            postal_code: response.data.user.profile.postal_code,
            img_path: response.data.user.profile.img_path,
            country: response.data.user.profile.country,
            tax_id: response.data.user.profile.tax_id,
            ic_num: response.data.user.profile.ic_num,
            join_date: response.data.join_date,
            remarks: response.data.user.profile.remarks,
            status: response.data.status,
            arr_access_branch_id: response.data.employee_accesses.map((e) => e.id),
        });
    }
    emits('loading-state', false);
}

const getDDL = (): void => {
    dashboardServices.getCountriesDDL().then((result: Array<DropDownOption> | null) => {
        countriesDDL.value = result;
    });

    dashboardServices.getStatusDDL().then((result: Array<DropDownOption> | null) => {
        statusDDL.value = result;
    });
}

const handleExpandCard = (index: number) => {
    if (cards.value[index].state === CardState.Collapsed) {
        cards.value[index].state = CardState.Expanded
    } else if (cards.value[index].state === CardState.Expanded) {
        cards.value[index].state = CardState.Collapsed
    }
}

const scrollToError = (id: string): void => {
    let el = document.getElementById(id);

    if (!el) return;

    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}


const onSubmit = async () => {
    if (employeeForm.hasErrors) {
        scrollToError(Object.keys(employeeForm.errors)[0]);
    }

    emits('loading-state', true);

    await employeeForm.submit().then(() => {
        resetForm();
        router.push({ name: 'side-menu-employee-employee-list' });
    }).catch((error) => {
        console.error(error);
    }).finally(() => {
        emits('loading-state', false);
    })
};

const resetForm = async () => {
    employeeForm.reset();
    employeeForm.setErrors({});
    await loadData(route.params.ulid as string);
}
// #endregion

// #region Watchers
watch(
    employeeForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('EMPLOYEE_EDIT', newValue.data())
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
   <form id="employeeForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
                <div class="p-5">
                    <FormLabel>
                        {{ selectedUserLocation.company.code }}
                        <br />
                        {{ selectedUserLocation.company.name }}
                    </FormLabel>
                </div>
            </template>
            <template #card-items-1>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel html-for="code" :class="{ 'text-danger': employeeForm.invalid('code') }">
                            {{ t('views.employee.fields.code') }}
                        </FormLabel>
                        <FormInputCode id="code" v-model="employeeForm.code" name="code" type="text"
                            :class="{ 'border-danger': employeeForm.invalid('code') }"
                            :placeholder="t('views.employee.fields.code')" @change="employeeForm.validate('code')" />
                        <FormErrorMessages :messages="employeeForm.errors.code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="name" :class="{ 'text-danger': employeeForm.invalid('name') }">
                            {{ t('views.employee.fields.name') }}
                        </FormLabel>
                        <FormInput id="name" v-model="employeeForm.name" name="name" type="text"
                            :class="{ 'border-danger': employeeForm.invalid('name') }"
                            :placeholder="t('views.employee.fields.name')" @change="employeeForm.validate('name')" />
                        <FormErrorMessages :messages="employeeForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="email" :class="{ 'text-danger': employeeForm.invalid('email') }">
                            {{ t('views.employee.fields.email') }}
                        </FormLabel>
                        <FormInput id="email" v-model="employeeForm.email" name="email" type="text"
                            :class="{ 'border-danger': employeeForm.invalid('email') }"
                            :placeholder="t('views.employee.fields.email')" @change="employeeForm.validate('email')" />
                        <FormErrorMessages :messages="employeeForm.errors.email" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="address" :class="{ 'text-danger': employeeForm.invalid('address') }">
                            {{ t('views.employee.fields.address') }}
                        </FormLabel>
                        <FormTextarea id="address" v-model="employeeForm.address" name="address" type="text"
                            :placeholder="t('views.employee.fields.address')"
                            @change="employeeForm.validate('address')" />
                        <FormErrorMessages :messages="employeeForm.errors.address" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="city" :class="{ 'text-danger': employeeForm.invalid('city') }">
                            {{ t('views.employee.fields.city') }}
                        </FormLabel>
                        <FormInput id="city" v-model="employeeForm.city" name="city" type="text"
                            :placeholder="t('views.employee.fields.city')" @change="employeeForm.validate('city')" />
                        <FormErrorMessages :messages="employeeForm.errors.city" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="postal_code"
                            :class="{ 'text-danger': employeeForm.invalid('postal_code') }">
                            {{ t('views.employee.fields.postal_code') }}
                        </FormLabel>
                        <FormInput id="postal_code" v-model="employeeForm.postal_code" name="postal_code" type="text"
                            :placeholder="t('views.employee.fields.postal_code')"
                            @change="employeeForm.validate('postal_code')" />
                        <FormErrorMessages :messages="employeeForm.errors.postal_code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="img_path" :class="{ 'text-danger': false }">
                            {{ t('views.employee.fields.img_path') }}
                        </FormLabel>
                        <FormFileUpload id="img_path" v-model="employeeForm.img_path" name="img_path" type="text"
                            :class="{ 'border-danger': false }" :placeholder="t('views.employee.fields.img_path')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="country" :class="{ 'text-danger': employeeForm.invalid('country') }">
                            {{ t('views.employee.fields.country') }}
                        </FormLabel>
                        <FormSelect v-model="employeeForm.country" id="country" name="country"
                            :class="{ 'border-danger': employeeForm.invalid('country') }"
                            :placeholder="t('views.employee.fields.country')"
                            @change="employeeForm.validate('country')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in countriesDDL" :key="c.name" :value="c.name">{{ c.name }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="employeeForm.errors.country" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="tax_id" :class="{ 'text-danger': employeeForm.invalid('tax_id') }">
                            {{ t('views.employee.fields.tax_id') }}
                        </FormLabel>
                        <FormInput id="tax_id" v-model="employeeForm.tax_id" name="tax_id" type="text"
                            :placeholder="t('views.employee.fields.tax_id')"
                            @change="employeeForm.validate('tax_id')" />
                        <FormErrorMessages :messages="employeeForm.errors.tax_id" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="ic_num" :class="{ 'text-danger': employeeForm.invalid('ic_num') }">
                            {{ t('views.employee.fields.ic_num') }}
                        </FormLabel>
                        <FormInput id="ic_num" v-model="employeeForm.ic_num" name="ic_num" type="text"
                            :placeholder="t('views.employee.fields.ic_num')"
                            @change="employeeForm.validate('ic_num')" />
                        <FormErrorMessages :messages="employeeForm.errors.ic_num" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="join_date" :class="{ 'text-danger': false }">
                            {{ t('views.employee.fields.join_date') }}
                        </FormLabel>
                        <FormInput id="join_date" v-model="employeeForm.join_date" name="join_date" type="date"
                            :placeholder="t('views.employee.fields.join_date')"
                            @change="employeeForm.validate('join_date')" />
                        <FormErrorMessages :messages="employeeForm.errors.join_date" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="remarks" :class="{ 'text-danger': false }">
                            {{ t('views.employee.fields.remarks') }}
                        </FormLabel>
                        <FormTextarea id="remarks" v-model="employeeForm.remarks" name="remarks" type="text"
                            :placeholder="t('views.employee.fields.remarks')" rows="3" />
                        <FormErrorMessages :messages="employeeForm.errors.remarks" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="status" :class="{ 'text-danger': employeeForm.invalid('status') }">
                            {{ t('views.employee.fields.status') }}
                        </FormLabel>
                        <FormSelect id="status" v-model="employeeForm.status" name="status"
                            :class="{ 'border-danger': employeeForm.invalid('status') }"
                            @change="employeeForm.validate('status')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="employeeForm.errors.status" />
                    </div>
                </div>
            </template>
            <template #card-items-2>
                <div class="p-5">
                    <FormLabel for="inputAccessLists"> {{ t('views.employee.fields.access.access_lists') }} </FormLabel>

                    <Table class="mt-5" :hover="true">
                        <Table.Thead>
                            <Table.Tr>
                                <Table.Th colspan="2">{{ t('views.employee.fields.access.table.cols.selected') }}
                                </Table.Th>
                                <Table.Th>{{ t('views.employee.fields.access.table.cols.available_access') }}</Table.Th>
                            </Table.Tr>
                        </Table.Thead>
                        <Table.Tbody>
                            <template v-for="(branch, aIdx) in accessLists" :key="aIdx">
                                <Table.Tr>
                                    <Table.Td class="border-b dark:border-dark-5 w-10">
                                        <div class="form-switch">

                                            <input class="form-check-input" type="checkbox" id="inputAccessLists"
                                                :value="branch.id"
                                                :checked="employeeForm.arr_access_branch_id.includes(branch.id)"
                                                @change="updateAccessList(branch.id)" />
                                        </div>
                                    </Table.Td>
                                    <Table.Td class="w-10"></Table.Td>
                                    <Table.Td
                                        :class="{ 'line-through': ['INACTIVE', 'DELETED'].includes(branch.status), 'underline': branch.default }">
                                        <strong>{{ branch.name }}</strong>
                                    </Table.Td>
                                </Table.Tr>
                            </template>
                        </Table.Tbody>
                    </Table>

                </div>
            </template>
            <template #card-items-button>
                <div class="flex gap-4">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md">
                        <Lucide v-if="employeeForm.validating" icon="Loader" class="animate-spin" />
                        <template v-else>
                            {{ t("components.buttons.submit") }}
                        </template>
                    </Button>
                    <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md" @click="resetForm">
                        {{ t("components.buttons.reset") }}
                    </Button>
                </div>
            </template>
        </TwoColumnsLayout>
    </form>
</template>