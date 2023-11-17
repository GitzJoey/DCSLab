<script setup lang="ts">
// #region Imports
import { onMounted, computed, ref, watchEffect } from "vue";
import { useI18n } from "vue-i18n";
import {
    FormInput,
    FormLabel,
    FormSwitch,
    FormTextarea,
    FormSelect,
    FormErrorMessages,
} from "../../base-components/Form";
import { useUserContextStore } from "../../stores/user-context";
import {
    TitleLayout,
    TwoColumnsLayout,
} from "../../base-components/Form/FormLayout";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import { CardState } from "../../types/enums/CardState";
import posSystemImage from "../../assets/images/pos_system.png";
import wareHouseImage from "../../assets/images/warehouse_system.png";
import accountingImage from "../../assets/images/accounting_system.jpg";
import { Check } from "lucide-vue-next";
import Button from "../../base-components/Button";
import { formatDate } from "../../utils/helper";
import ProfileService from "../../services/ProfileService";
import { TwoFactorResponse, QRCode, ConfirmedPasswordStatus } from "../../types/models/TwoFactorAuthentication";
import { UserProfile } from "../../types/models/UserProfile";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Dialog } from "../../base-components/Headless";
// #endregion

// #region Interfaces
interface RoleSelection {
    images: string;
    state: 'selectable' | 'checked' | 'disabled',
    rolekey: string;
};
// #endregion

// #region Declarations
const { t } = useI18n();

const profileServices = new ProfileService();
const userContextStore = useUserContextStore();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state']);
// #endregion

// #region Refs
const loading = ref<boolean>(false);
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: t('views.profile.field_groups.user_profile'), state: CardState.Expanded },
    { title: t('views.profile.field_groups.email_verification'), state: CardState.Expanded },
    { title: t('views.profile.field_groups.personal_information'), state: CardState.Expanded },
    { title: t('views.profile.field_groups.account_settings'), state: CardState.Expanded },
    { title: t('views.profile.field_groups.roles'), state: CardState.Expanded },
    { title: t('views.profile.field_groups.change_password'), state: CardState.Expanded },
    { title: t('views.profile.field_groups.api_token'), state: CardState.Expanded },
    { title: t('views.profile.field_groups.two_factor_authentication'), state: CardState.Expanded },
]);

const roleSelection = ref<Array<RoleSelection>>([
    {
        images: posSystemImage,
        state: 'disabled',
        rolekey: "pos",
    },
    {
        images: wareHouseImage,
        state: 'disabled',
        rolekey: "wh",
    },
    {
        images: accountingImage,
        state: 'disabled',
        rolekey: "wh",
    },
]);

const showQRCodeField = ref<boolean>(false);
const qrCode = ref<QRCode>({
    svg: '',
    url: '',
});
const showTwoFactorConfirmPasswordDialog = ref<boolean>(false);
const twoFactorConfirmPasswordText = ref<string>('');
const twoFactorRecoveryCodes = ref<Array<string>>([]);
const twoFactorConfirmedPasswordStatus = ref<ConfirmedPasswordStatus>({
    confirmed: false
});

const updateUserProfileForm = profileServices.useUpdateUserProfileForm();
const updatePersonalInfoForm = profileServices.useUpdatePersonalInfoForm();
const updateAccountSettingsForm = profileServices.useUpdateAccountSettingsForm();
const updateUserRolesForm = profileServices.useUpdateUserRolesForm();
const updatePasswordForm = profileServices.useUpdatePasswordForm();
const updateTokensForm = profileServices.useUpdateTokenForm();
// #endregion

// #region Computed
const userContextIsLoaded = computed(() => userContextStore.getIsLoaded);
const userContext = computed(() => userContextStore.getUserContext);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    loading.value = true;
    if (userContextIsLoaded.value) {
        setFormData();
        loading.value = false;
    }
});
// #endregion

// #region Methods
const setFormData = () => {
    updateUserProfileForm.setData({
        name: userContext.value.name,
    });

    updatePersonalInfoForm.setData({
        first_name: userContext.value.profile.first_name,
        last_name: userContext.value.profile.last_name,
        address: userContext.value.profile.address,
        city: userContext.value.profile.city,
        postal_code: userContext.value.profile.postal_code,
        country: userContext.value.profile.country,
        img_path: userContext.value.profile.img_path,
        tax_id: userContext.value.profile.tax_id,
        ic_num: userContext.value.profile.ic_num,
        status: userContext.value.profile.status,
        remarks: userContext.value.profile.remarks,
    });

    updateAccountSettingsForm.setData({
        theme: userContext.value.settings.theme,
        date_format: userContext.value.settings.date_format,
        time_format: userContext.value.settings.time_format,
    });

    if (!hasRolePOSOwner()) { roleSelection.value[0].state = 'selectable' }
    if (!hasRoleWHOwner()) { roleSelection.value[1].state = 'selectable' }
    if (!hasRoleACCOwner()) { roleSelection.value[2].state = 'selectable' }
}

const handleExpandCard = (index: number) => {
    if (cards.value[index].state === CardState.Collapsed) {
        cards.value[index].state = CardState.Expanded;
    } else if (cards.value[index].state === CardState.Expanded) {
        cards.value[index].state = CardState.Collapsed;
    }
};

const handleChangeRole = (index: number) => {
    let activeRole: string = roleSelection.value[index].rolekey;

    updateUserRolesForm.setData({
        roles: activeRole
    });
}

const hasRolePOSOwner = () => {
    let result = false;
    for (const r of userContext.value.roles) {
        if (r.display_name == 'POS-owner') {
            result = true;
        }
    }
    return result;
};

const hasRoleWHOwner = () => {
    let result = false;
    for (const r of userContext.value.roles) {
        if (r.display_name == 'WH-owner') {
            result = true;
        }
    }
    return result;
};

const hasRoleACCOwner = () => {
    let result = false;
    for (const r of userContext.value.roles) {
        if (r.display_name == 'ACC-owner') {
            result = true;
        }
    }
    return result;
};

const setTwoFactorFields = () => {
    if (userContext.value.two_factor) {
        showQRCodeField.value = true;
    } else {
        showQRCodeField.value = false;
    }
}

const setTwoFactor = async (event: Event) => {
    let checked: boolean = (event.target as HTMLInputElement).checked;

    if (checked) {
        let enableTwoFactorResponse: ServiceResponse<TwoFactorResponse | null> = await profileServices.enableTwoFactor();

        if (!enableTwoFactorResponse.success) {
            twoFactorConfirmPasswordText.value = '';
            showTwoFactorConfirmPasswordDialog.value = true;
        } else {
            showQRCodeField.value = true;
            await confirmTwoFactorAuthentication();
        }
    } else {
        let disableTwoFactorResponse: ServiceResponse<TwoFactorResponse | null> = await profileServices.disableTwoFactor();

        if (!disableTwoFactorResponse.success) {
            twoFactorConfirmPasswordText.value = '';
            showTwoFactorConfirmPasswordDialog.value = true;
        } else {
            showQRCodeField.value = false;
        }
    }
}

const confirmTwoFactorAuthentication = async () => {
    let response: ServiceResponse<any | null> = await profileServices.TwoFactorAuthenticationConfirmed();

    console.log('confirmTwoFactorAuthentication');
    console.log(response);
}

const showQR = async () => {
    let response: ServiceResponse<QRCode | null> = await profileServices.twoFactorQR();

    if (response.success && response.data) {
        qrCode.value = response.data;
    }
}

const showRecoveryCodes = async () => {
    let response: ServiceResponse<Array<string> | null> = await profileServices.twoFactorRecoveryCodes();

    if (response.success && response.data) {
        twoFactorRecoveryCodes.value = response.data;
    }
}

const showConfirmedPasswordStatus = async () => {
    let response: ServiceResponse<ConfirmedPasswordStatus | null> = await profileServices.TwoFactorConfirmedPasswordStatus();

    if (response.success && response.data) {
        twoFactorConfirmedPasswordStatus.value = response.data;
    }
}

const reloadUserContext = async () => {
    let userprofile = await profileServices.readProfile();
    userContextStore.setUserContext(userprofile.data as UserProfile);
}

const submitTwoFactorConfirmPassword = async () => {
    let response: ServiceResponse<TwoFactorResponse | null> = await profileServices.TwoFactorConfirmPassword(twoFactorConfirmPasswordText.value);

    if (!response.success) {
        await confirmTwoFactorAuthentication();
    } else {
        console.log(response);
    }

    await reloadUserContext();

    showTwoFactorConfirmPasswordDialog.value = false;
}

const cancelTwoFactorConfirmPassword = async () => {
    showTwoFactorConfirmPasswordDialog.value = false;

    await reloadUserContext();
}

const sendEmailVerification = () => {

}

const onSubmitUpdateUserProfile = async () => {

};
const onSubmitUpdatePersonalInfo = async () => {

};
const onSubmitUpdateAccountSettings = async () => {

};
const onSubmitUpdateUserRoles = async () => {

};
const onSubmitUpdatePassword = async () => {

};
const onSubmitUpdateToken = async () => {

};
// #region Methods

// #region Watchers
watchEffect(() => {
    if (userContextIsLoaded.value) {
        setFormData();
        setTwoFactorFields();

        loading.value = false;
    }
});

watchEffect(() => {
    if (showQRCodeField.value) {
        showQR();
        showRecoveryCodes();
        showConfirmedPasswordStatus();
    }
});
// #endregion
</script>

<template>
    <div class="mt-8">
        <LoadingOverlay :visible="loading">
            <TitleLayout>
                <template #title>
                    {{ t("views.profile.title") }}
                </template>
            </TitleLayout>

            <TwoColumnsLayout :cards="cards" :show-side-tab="true" :using-side-tab="true"
                @handleExpandCard="handleExpandCard">
                <template #side-menu-title>
                    {{ userContext.name }}
                </template>
                <template #card-items-0>
                    <div class="p-5">
                        <form id="updateUserProfileForm" @submit.prevent="onSubmitUpdateUserProfile">
                            <div class="pb-4">
                                <FormLabel html-for="name"
                                    :class="{ 'text-danger': updateUserProfileForm.invalid('name') }">
                                    {{ t("views.profile.fields.name") }}
                                </FormLabel>
                                <FormInput id="name" v-model="updateUserProfileForm.name" name="name" type="text"
                                    :class="{ 'border-danger': updateUserProfileForm.invalid('name') }"
                                    :placeholder="t('views.profile.fields.name')"
                                    @change="updateUserProfileForm.validate('name')" />
                                <FormErrorMessages :messages="updateUserProfileForm.errors.name" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="email">
                                    {{ t("views.profile.fields.email") }}
                                </FormLabel>
                                <FormInput id="email" v-model="userContext.email" name="email" type="text"
                                    :placeholder="t('views.profile.fields.email')" disabled />
                            </div>
                            <div>
                                <Button type="submit" size="sm" href="#" variant="primary" class="w-28 shadow-md"
                                    :disabled="updateUserProfileForm.validating || updateUserProfileForm.hasErrors">
                                    <Lucide v-if="updateUserProfileForm.validating" icon="Loader" class="animate-spin" />
                                    <template v-else>
                                        {{ t("components.buttons.update") }}
                                    </template>
                                </Button>
                            </div>
                        </form>
                    </div>
                </template>
                <template #card-items-1>
                    <div class="p-5">
                        <div v-if="userContext.email_verified" class="pb-4">
                            <span>{{ t('views.profile.tooltip.email_verified') }}</span>
                        </div>
                        <div v-else>
                            <Button type="button" size="sm" href="#" variant="primary" class="w-42 shadow-md"
                                @click="sendEmailVerification">
                                {{ t("components.buttons.send_verification_email") }}
                            </Button>
                        </div>
                    </div>
                </template>
                <template #card-items-2>
                    <div class="p-5">
                        <form id="updatePersonalInfoForm" @submit.prevent="onSubmitUpdatePersonalInfo">
                            <div class="pb-4">
                                <FormLabel html-for="first_name">
                                    {{ t("views.profile.fields.first_name") }}
                                </FormLabel>
                                <FormInput id="first_name" v-model="updatePersonalInfoForm.first_name" name="first_name"
                                    type="text" :placeholder="t('views.profile.fields.first_name')" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="last_name">
                                    {{ t("views.profile.fields.last_name") }}
                                </FormLabel>
                                <FormInput id="last_name" v-model="updatePersonalInfoForm.last_name" name="last_name"
                                    type="text" :placeholder="t('views.profile.fields.last_name')" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="address">
                                    {{ t("views.profile.fields.address") }}
                                </FormLabel>
                                <FormTextarea id="address" v-model="updatePersonalInfoForm.address" name="address" rows="5"
                                    :placeholder="t('views.profile.fields.address')" />
                            </div>
                            <div class="flex gap-2">
                                <div class="pb-4 w-full">
                                    <FormLabel html-for="city">
                                        {{ t("views.profile.fields.city") }}
                                    </FormLabel>
                                    <FormInput id="address" v-model="updatePersonalInfoForm.city" name="city" type="text"
                                        class="w-full" :placeholder="t('views.profile.fields.city')" />
                                </div>
                                <div class="pb-4">
                                    <FormLabel html-for="postal_code">
                                        {{ t("views.profile.fields.postal_code") }}
                                    </FormLabel>
                                    <FormInput id="postal_code" v-model="updatePersonalInfoForm.postal_code"
                                        name="postal_code" type="number"
                                        :placeholder="t('views.profile.fields.postal_code')" />
                                </div>
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="country">
                                    {{ t("views.profile.fields.country") }}
                                </FormLabel>
                                <FormSelect id="country" v-model="updatePersonalInfoForm.country" name="country"
                                    :class="{ 'border-danger': updatePersonalInfoForm.invalid('country') }"
                                    :placeholder="t('views.profile.fields.country')"
                                    @change="updatePersonalInfoForm.validate('country'); updatePersonalInfoForm.submit()">
                                    <option>Singapore</option>
                                    <option>Indonesia</option>
                                </FormSelect>
                                <FormErrorMessages :messages="updatePersonalInfoForm.errors.country" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="tax_id"
                                    :class="{ 'text-danger': updatePersonalInfoForm.invalid('tax_id') }">
                                    {{ t("views.profile.fields.tax_id") }}
                                </FormLabel>
                                <FormInput id="tax_id" v-model="updatePersonalInfoForm.tax_id" name="tax_id" type="text"
                                    :class="{ 'border-danger': updatePersonalInfoForm.invalid('tax_id') }"
                                    :placeholder="t('views.profile.fields.tax_id')"
                                    @change="updatePersonalInfoForm.validate('tax_id'); updatePersonalInfoForm.submit();" />
                                <FormErrorMessages :messages="updatePersonalInfoForm.errors.tax_id" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="ic_num"
                                    :class="{ 'text-danger': updatePersonalInfoForm.invalid('ic_num') }">
                                    {{ t("views.profile.fields.ic_num") }}
                                </FormLabel>
                                <FormInput id="ic_num" v-model="updatePersonalInfoForm.ic_num" name="ic_num" type="text"
                                    :class="{ 'border-danger': updatePersonalInfoForm.invalid('ic_num') }"
                                    :placeholder="t('views.profile.fields.ic_num')"
                                    @change="updatePersonalInfoForm.validate('ic_num')" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="remarks">
                                    {{ t("views.profile.fields.remarks") }}
                                </FormLabel>
                                <FormTextarea id="remarks" v-model="updatePersonalInfoForm.remarks" name="remarks" rows="5"
                                    :placeholder="t('views.profile.fields.remarks')" />
                            </div>
                            <div>
                                <Button type="submit" size="sm" href="#" variant="primary" class="w-28 shadow-md"
                                    :disabled="updateUserProfileForm.validating || updateUserProfileForm.hasErrors">
                                    <Lucide v-if="updateUserProfileForm.validating" icon="Loader" class="animate-spin" />
                                    <template v-else>
                                        {{ t("components.buttons.update") }}
                                    </template>
                                </Button>
                            </div>
                        </form>
                    </div>
                </template>
                <template #card-items-3>
                    <div class="p-5">
                        <form id="updateAccountSettingsForm" @submit.prevent="onSubmitUpdateAccountSettings">
                            <div class="pb-4">
                                <FormLabel html-for="themes">
                                    {{ t("views.profile.fields.settings.theme") }}
                                </FormLabel>
                                <FormSelect id="themes" v-model="updateAccountSettingsForm.theme" name="themes">
                                    <option value="side-menu-light-full">Menu Light</option>
                                    <option value="side-menu-light-mini">Mini Menu Light</option>
                                    <option value="side-menu-dark-full">Menu Dark</option>
                                    <option value="side-menu-dark-mini">Mini Menu Dark</option>
                                </FormSelect>
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="date_format">
                                    {{ t("views.profile.fields.settings.date_format") }}
                                </FormLabel>
                                <FormSelect id="date_format" v-model="updateAccountSettingsForm.date_format"
                                    name="date_format">
                                    <option value="yyyy_MM_dd">
                                        {{ formatDate(new Date().toString(), "YYYY-MM-DD") }}
                                    </option>
                                    <option value="dd_MMM_yyyy">
                                        {{ formatDate(new Date().toString(), "DD-MMM-YYYY") }}
                                    </option>
                                </FormSelect>
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="time_format">
                                    {{ t("views.profile.fields.settings.time_format") }}
                                </FormLabel>
                                <FormSelect id="time_format" v-model="updateAccountSettingsForm.time_format"
                                    name="time_format">
                                    <option value="hh_mm_ss">
                                        {{ formatDate(new Date().toString(), "HH:mm:ss") }}
                                    </option>
                                    <option value="h_m_A">
                                        {{ formatDate(new Date().toString(), "H:m A") }}
                                    </option>
                                </FormSelect>
                            </div>
                            <div>
                                <Button type="submit" size="sm" href="#" variant="primary" class="w-28 shadow-md"
                                    :disabled="updateUserProfileForm.validating || updateUserProfileForm.hasErrors">
                                    <Lucide v-if="updateUserProfileForm.validating" icon="Loader" class="animate-spin" />
                                    <template v-else>
                                        {{ t("components.buttons.update") }}
                                    </template>
                                </Button>
                            </div>
                        </form>
                    </div>
                </template>
                <template #card-items-4>
                    <div class="p-5">
                        <form id="updateUserRolesForm" @submit.prevent="onSubmitUpdateUserRoles">
                            <div class="pb-4">
                                <div class="grid grid-cols-3 gap-2 place-items center">
                                    <div v-for="(item, index) in roleSelection" :key="index"
                                        class="flex flex-col items-center">
                                        <div :class="{ 'cursor-pointer': item.state == 'selectable', 'flex flex-col items-center justify-center': true }"
                                            @click="handleChangeRole(index)">
                                            <img alt="" :src="item.images" width="100" height="100" />
                                            <div v-if="item.state == 'checked'" class="grid grid-cols-1 place-items-center">
                                                <Check class="text-success" />
                                            </div>
                                            <Button v-else-if="item.state == 'selectable'" type="submit" variant="primary"
                                                size="sm" class="w-28 shadow-md">
                                                {{ t("components.buttons.activate") }}
                                            </Button>
                                            <span v-else>&nbsp;</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </template>
                <template #card-items-5>
                    <div class="p-5">
                        <form id="updatePasswordForm" @submit.prevent="onSubmitUpdatePassword">
                            <div class="pb-4">
                                <FormLabel html-for="current_password"
                                    :class="{ 'text-danger': updatePasswordForm.invalid('current_password') }">
                                    {{ t("views.profile.fields.change_password.current_password") }}
                                </FormLabel>
                                <FormInput id="current_password" v-model="updatePasswordForm.current_password"
                                    name="current_password" type="password"
                                    :class="{ 'border-danger': updatePasswordForm.invalid('current_password') }"
                                    :placeholder="t('views.profile.fields.change_password.current_password')"
                                    @change="updatePasswordForm.validate('current_password')" />
                                <FormErrorMessages :messages="updatePasswordForm.errors.current_password" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="password"
                                    :class="{ 'text-danger': updatePasswordForm.invalid('password') }">
                                    {{ t("views.profile.fields.change_password.password") }}
                                </FormLabel>
                                <FormInput id="password" v-model="updatePasswordForm.password" name="password"
                                    type="password" :class="{ 'border-danger': updatePasswordForm.invalid('password') }"
                                    :placeholder="t('views.profile.fields.change_password.password')" />
                                <FormErrorMessages :messages="updatePasswordForm.errors.password" />
                            </div>
                            <div class="pb-4">
                                <FormLabel html-for="password_confirmation">
                                    {{ t("views.profile.fields.change_password.password_confirmation") }}
                                </FormLabel>
                                <FormInput id="password_confirmation" v-model="updatePasswordForm.password_confirmation"
                                    name="password_confirmation" type="password"
                                    :placeholder="t('views.profile.fields.change_password.password_confirmation')" />
                            </div>
                            <div>
                                <Button type="submit" size="sm" href="#" variant="primary" class="w-28 shadow-md"
                                    :disabled="updateUserProfileForm.validating || updateUserProfileForm.hasErrors">
                                    <Lucide v-if="updateUserProfileForm.validating" icon="Loader" class="animate-spin" />
                                    <template v-else>
                                        {{ t("components.buttons.update") }}
                                    </template>
                                </Button>
                            </div>
                        </form>
                    </div>
                </template>
                <template #card-items-6>
                    <div class="p-5">
                        <form id="updateTokenForm" @submit.prevent="onSubmitUpdateToken">
                            <input id="resetToken" type="hidden" v-model="updateTokensForm.reset_tokens" />
                            <div>
                                <Button type="submit" size="sm" href="#" variant="primary" class="w-28 shadow-md"
                                    :disabled="updateTokensForm.validating || updateTokensForm.hasErrors">
                                    <Lucide v-if="updateTokensForm.validating" icon="Loader" class="animate-spin" />
                                    <template v-else>
                                        {{ t("components.buttons.reset") }}
                                    </template>
                                </Button>
                            </div>
                        </form>
                    </div>
                </template>
                <template #card-items-7>
                    <div class="p-5">
                        <div class="pb-4">
                            <FormLabel html-for="status_2fa">
                                {{ t('views.profile.fields.2fa.status') }}
                            </FormLabel>
                            <FormSwitch>
                                <FormSwitch.Input type="checkbox" @change="setTwoFactor" v-model="userContext.two_factor" />
                            </FormSwitch>
                        </div>
                        <div class="pb-4">
                            <div v-if="showQRCodeField">
                                <img v-html="qrCode.svg" alt="QR Code" />
                                <br />
                                {{ t('views.profile.fields.2fa.qr-code_description_1') }}
                                <br />
                                {{ t('views.profile.fields.2fa.qr-code_description_2') }}
                                <br />
                                <br />
                            </div>
                            <div>
                                <FormLabel html-for="recoverycodes_2fa">
                                    {{ t('views.profile.fields.2fa.recovery-codes') }}
                                </FormLabel>
                                <div>
                                    <template v-for="(rc, rcIdx) in twoFactorRecoveryCodes">
                                        <span class="italic">{{ rcIdx + 1 }}. {{ rc }}</span>
                                        <br />
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="pb-4">

                        </div>
                        <div class="pb-4">
                            <Dialog staticBackdrop :open="showTwoFactorConfirmPasswordDialog"
                                @close="() => { cancelTwoFactorConfirmPassword(); }">
                                <Dialog.Panel class="px-5 py-10">
                                    <div class="text-center">
                                        <div class="mb-5">
                                            <FormLabel html-for="twoFactorConfirmPasswordText">
                                                {{ t('views.profile.fields.2fa.confirm_password') }}
                                            </FormLabel>
                                            <FormInput id="twoFactorConfirmPasswordText"
                                                v-model="twoFactorConfirmPasswordText" name="twoFactorConfirmPasswordText"
                                                type="password"
                                                :placeholder="t('views.profile.fields.2fa.confirm_password')" />
                                        </div>
                                        <div class="flex gap-2 justify-center items-center">
                                            <Button type="button" variant="primary"
                                                @click="() => { submitTwoFactorConfirmPassword(); }" class="w-24">
                                                {{ t('components.buttons.submit') }}
                                            </Button>
                                            <Button type="button" variant="primary"
                                                @click="() => { cancelTwoFactorConfirmPassword(); }" class="w-24">
                                                {{ t('components.buttons.cancel') }}
                                            </Button>
                                        </div>
                                    </div>
                                </Dialog.Panel>
                            </Dialog>
                        </div>
                    </div>
                </template>
            </TwoColumnsLayout>
        </LoadingOverlay>
    </div>
</template>
