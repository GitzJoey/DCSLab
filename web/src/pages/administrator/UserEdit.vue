<script setup lang="ts">
// #region Imports
import { onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";
import { Role } from "../../types/models/Role";
import UserService from "../../services/UserService";
import RoleService from "../../services/RoleService";
import DashboardService from "../../services/DashboardService";
import CacheService from "../../services/CacheService";
import { TwoColumnsLayout } from "../../base-components/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormTextarea,
    FormSelect,
    FormSwitch,
    FormFileUpload,
    FormErrorMessages,
} from "../../base-components/Form";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "../../types/enums/CardState";
import { DropDownOption } from "../../types/models/DropDownOption";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { ViewMode } from "../../types/enums/ViewMode";
import { User } from "../../types/models/User";
import Button from "../../base-components/Button";
import { debounce } from "lodash";
import Lucide from "../../base-components/Lucide";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const userServices = new UserService();
const roleServices = new RoleService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'views.user.field_groups.user_info', state: CardState.Expanded, },
    { title: 'views.user.field_groups.user_profile', state: CardState.Expanded },
    { title: 'views.user.field_groups.roles', state: CardState.Expanded },
    { title: 'views.user.field_groups.settings', state: CardState.Expanded },
    { title: 'views.user.field_groups.tokens_management', state: CardState.Expanded },
    { title: 'views.user.field_groups.password_management', state: CardState.Expanded },
    { title: 'views.user.field_groups.two_factor_auth', state: CardState.Expanded },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const rolesDDL = ref<Array<Role> | null>(null);
const statusDDL = ref<Array<DropDownOption> | null>(null);
const countriesDDL = ref<Array<DropDownOption> | null>(null);

const userForm = userServices.useUserEditForm(route.params.ulid as string);
// #endregion

// #region Computed
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emits('mode-state', ViewMode.FORM_EDIT);
    getDDL();

    emits('loading-state', true);
    await loadData(route.params.ulid as string);
    emits('loading-state', false);
});
// #endregion

// #region Methods
const loadData = async (ulid: string) => {
    let response: ServiceResponse<User | null> = await userServices.read(ulid);

    if (response && response.data) {
        let rolesArr: Array<Role> = [];
        response.data.roles.forEach((r) => {
            rolesArr.push({
                id: r.id,
                display_name: r.display_name
            });
        });

        userForm.setData({
            name: response.data.name,
            email: response.data.email,

            first_name: response.data.profile.first_name,
            last_name: response.data.profile.last_name,
            address: response.data.profile.address,
            city: response.data.profile.city,
            postal_code: response.data.profile.postal_code,
            country: response.data.profile.country,
            img_path: response.data.profile.img_path,
            tax_id: response.data.profile.tax_id,
            ic_num: response.data.profile.ic_num,
            status: response.data.profile.status,
            remarks: response.data.profile.remarks,

            roles: rolesArr,

            theme: response.data.settings.theme,
            date_format: response.data.settings.date_format,
            time_format: response.data.settings.time_format,

            tokens_reset: false,
            reset_password: false,
            reset_2fa: false,
        });
    }
}

const getDDL = (): void => {
    roleServices.readAny().then((result: ServiceResponse<Resource<Array<Role>> | null>) => {
        if (result.success && result.data) {
            rolesDDL.value = result.data.data as Array<Role>;
        }
    });

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
    if (userForm.hasErrors) {
        scrollToError(Object.keys(userForm.errors)[0]);
    }

    emits('loading-state', true);
    await userForm.submit().then(() => {
        resetForm();
        router.push({ name: 'side-menu-administrator-user-list' });
    }).catch(error => {
        console.error(error);
    }).finally(() => {
        emits('loading-state', false);
    });
};

const resetForm = () => {
    userForm.reset();
    userForm.setErrors({});
}
// #endregion

// #region Watchers
watch(
    userForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('USER_EDIT', newValue.data())
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="userForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel html-for="name" :class="{ 'text-danger': userForm.invalid('name') }">
                            {{ t('views.user.fields.name') }}
                        </FormLabel>
                        <FormInput v-model="userForm.name" id="name" name="name" type="text"
                            :class="{ 'border-danger': userForm.invalid('name') }"
                            :placeholder="t('views.user.fields.name')" @change="userForm.validate('name')" />
                        <FormErrorMessages :messages="userForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="email">
                            {{ t('views.user.fields.email') }}
                        </FormLabel>
                        <FormInput v-model="userForm.email" id="email" name="email" type="text" disabled />
                    </div>
                </div>
            </template>
            <template #card-items-1>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel html-for="first_name">{{ t('views.user.fields.first_name') }}</FormLabel>
                        <FormInput v-model="userForm.first_name" id="first_name" name="first_name" type="text"
                            :class="{ 'border-danger': false }" :placeholder="t('views.user.fields.name')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="last_name">{{ t('views.user.fields.last_name') }}</FormLabel>
                        <FormInput v-model="userForm.last_name" id="last_name" name="last_name" type="text"
                            :placeholder="t('views.user.fields.last_name')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="address" class="form-label">{{ t('views.user.fields.address') }}
                        </FormLabel>
                        <FormInput v-model="userForm.address" id="address" name="address" type="text"
                            :placeholder="t('views.user.fields.address')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="city">{{ t('views.user.fields.city') }}</FormLabel>
                        <FormInput v-model="userForm.city" id="city" name="city" type="text" class="form-control"
                            :placeholder="t('views.user.fields.city')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="postal_code">{{ t('views.user.fields.postal_code') }}
                        </FormLabel>
                        <FormInput v-model="userForm.postal_code" id="postal_code" name="postal_code" type="text"
                            :placeholder="t('views.user.fields.postal_code')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="country" :class="{ 'text-danger': userForm.invalid('country') }">
                            {{ t('views.user.fields.country') }}
                        </FormLabel>
                        <FormSelect v-model="userForm.country" id="country" name="country"
                            :class="{ 'border-danger': userForm.invalid('country') }"
                            :placeholder="t('views.user.fields.country')" @change="userForm.validate('country')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in countriesDDL" :key="c.name" :value="c.name">{{ c.name }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="userForm.errors.country" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="img_path" :class="{ 'text-danger': false }">
                            {{ t('views.user.fields.picture') }}
                        </FormLabel>
                        <FormFileUpload id="img_path" v-model="userForm.img_path" name="img_path" type="text"
                            :class="{ 'border-danger': false }" :placeholder="t('views.user.fields.picture')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="tax_id" :class="{ 'text-danger': userForm.invalid('tax_id') }">
                            {{ t('views.user.fields.tax_id') }}
                        </FormLabel>
                        <FormInput v-model="userForm.tax_id" id="tax_id" name="tax_id" type="text"
                            :class="{ 'border-danger': userForm.invalid('tax_id') }"
                            @change="userForm.validate('tax_id')" />
                        <FormErrorMessages :messages="userForm.errors.tax_id" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="ic_num" :class="{ 'text-danger': userForm.invalid('ic_num') }">
                            {{ t('views.user.fields.ic_num') }}
                        </FormLabel>
                        <FormInput id="ic_num" v-model="userForm.ic_num" name="ic_num" type="text"
                            :class="{ 'border-danger': userForm.invalid('ic_num') }"
                            @change="userForm.validate('ic_num')" />
                        <FormErrorMessages :messages="userForm.errors.ic_num" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="status" :class="{ 'text-danger': userForm.invalid('status') }">
                            {{ t('views.user.fields.status') }}
                        </FormLabel>
                        <FormSelect id="status" v-model="userForm.status" name="status"
                            :class="{ 'border-danger': userForm.invalid('status') }" @change="userForm.validate('status')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}
                            </option>
                        </FormSelect>
                        <FormErrorMessages :messages="userForm.errors.status" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="remarks">
                            {{ t('views.user.fields.remarks') }}
                        </FormLabel>
                        <FormTextarea id="remarks" v-model="userForm.remarks" name="remarks" type="text"
                            :placeholder="t('views.user.fields.remarks')" rows="3" />
                    </div>
                </div>
            </template>
            <template #card-items-2>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel html-for="roles" :class="{ 'text-danger': userForm.invalid('roles') }">
                            {{ t('views.user.fields.roles') }}
                        </FormLabel>
                        <FormSelect id="roles" v-model="userForm.roles" multiple size="6"
                            :class="{ 'border-danger': userForm.invalid('roles') }" @change="userForm.validate('roles')">
                            <option v-for="r in rolesDDL" :key="r.id" :value="r">
                                {{ r.display_name }}
                            </option>
                        </FormSelect>
                        <FormErrorMessages :messages="userForm.errors.roles" />
                    </div>
                </div>
            </template>
            <template #card-items-3>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel html-for="theme">
                            {{ t('views.user.fields.settings.theme') }}
                        </FormLabel>
                        <FormSelect v-model="userForm.theme" id="theme" name="theme">
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
                        <FormSelect v-model="userForm.date_format" id="date_format" name="date_format">
                            <option value="yyyy_MM_dd">{{ 'YYYY-MM-DD' }}</option>
                            <option value="dd_MMM_yyyy">{{ 'DD-MMM-YYYY' }}</option>
                        </FormSelect>
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="time_format">
                            {{ t('views.user.fields.settings.time_format') }}
                        </FormLabel>
                        <FormSelect v-model="userForm.time_format" id="time_format" name="time_format">
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
                        <FormSwitch>
                            <FormSwitch.Input type="checkbox" v-model="userForm.tokens_reset" />
                        </FormSwitch>
                    </div>
                </div>
            </template>
            <template #card-items-5>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel html-for="reset_password">
                            {{ t('views.user.fields.reset_password') }}
                        </FormLabel>
                        <FormSwitch>
                            <FormSwitch.Input type="checkbox" v-model="userForm.reset_password" />
                        </FormSwitch>
                    </div>
                </div>
            </template>
            <template #card-items-6>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel html-for="reset_2fa">
                            {{ t('views.user.fields.reset_2fa') }}
                        </FormLabel>
                        <FormSwitch>
                            <FormSwitch.Input type="checkbox" v-model="userForm.reset_2fa" />
                        </FormSwitch>
                    </div>
                </div>
            </template>
            <template #card-items-button>
                <div class="flex gap-4">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
                        :disabled="userForm.validating || userForm.hasErrors">
                        <Lucide v-if="userForm.validating" icon="Loader" class="animate-spin" />
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