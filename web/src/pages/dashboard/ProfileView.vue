<script setup lang="ts">
  // #region Imports
  import { onMounted, computed, ref, watchEffect, provide } from 'vue';
  import { useI18n } from 'vue-i18n';
  import {
    FormInput,
    FormLabel,
    FormSwitch,
    FormTextarea,
    FormSelect,
    FormErrorMessages,
  } from '@/components/Base/Form';
  import { useUserContextStore } from '@/stores/user-context';
  import { useZiggyRouteStore } from '@/stores/ziggy-route';
  import { useMenuStore, Menu as sMenu } from '@/stores/menu';
  import { TitleLayout, TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
  import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
  import LoadingOverlay from '@/components/LoadingOverlay';
  import { CardState } from '@/types/enums/CardState';
  import posSystemImage from '@/assets/images/pos_system.png';
  import wareHouseImage from '@/assets/images/warehouse_system.png';
  import accountingImage from '@/assets/images/accounting_system.jpg';
  import googlePlayBadge from '@/assets/images/google-play-badge.png';
  import Lucide from '@/components/Base/Lucide';
  import Button from '@/components/Base/Button';
  import { formatDate } from '@/utils/helper';
  import ProfileService from '@/services/ProfileService';
  import DashboardService from '@/services/DashboardService';
  import { TwoFactorResponse, QRCode, SecretKeyResponse } from '@/types/models/TwoFactorAuthentication';
  import { ConfirmPasswordStatusResponse } from '@/types/models/ConfirmPassword';
  import { UserProfile } from '@/types/models/UserProfile';
  import { ServiceResponse } from '@/types/services/ServiceResponse';
  import { Dialog } from '@/components/Base/Headless';
  import { Config } from 'ziggy-js';
  import AlertPlaceholder from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
  import { type NotificationElement } from '@/components/Base/Notification/Notification.vue';
  // #endregion

  // #region Interfaces
  interface RoleSelection {
    images: string;
    state: 'selectable' | 'checked' | 'disabled';
    rolekey: string;
  }
  // #endregion

  // #region Declarations
  const { t } = useI18n();

  const dashboardServices = new DashboardService();
  const profileServices = new ProfileService();

  const userContextStore = useUserContextStore();
  const menuStore = useMenuStore();
  const ziggyRouteStore = useZiggyRouteStore();
  // #endregion

  // #region Props, Emits
  // #endregion

  // #region Refs
  const loading = ref<boolean>(false);
  const cards = ref<Array<TwoColumnsLayoutCards>>([
    {
      title: 'views.profile.field_groups.user_profile',
      state: CardState.Expanded,
    },
    {
      title: 'views.profile.field_groups.email_verification',
      state: CardState.Expanded,
    },
    {
      title: 'views.profile.field_groups.personal_information',
      state: CardState.Expanded,
    },
    {
      title: 'views.profile.field_groups.account_settings',
      state: CardState.Expanded,
    },
    { title: 'views.profile.field_groups.roles', state: CardState.Expanded },
    {
      title: 'views.profile.field_groups.change_password',
      state: CardState.Expanded,
    },
    {
      title: 'views.profile.field_groups.api_token',
      state: CardState.Expanded,
    },
    {
      title: 'views.profile.field_groups.two_factor_authentication',
      state: CardState.Expanded,
    },
  ]);

  const sendVerificationEmailNotification = ref<NotificationElement>();

  provide('bind[sendVerificationEmailNotification]', (el: NotificationElement) => {
    sendVerificationEmailNotification.value = el;
  });

  const roleSelection = ref<Array<RoleSelection>>([
    {
      images: posSystemImage,
      state: 'disabled',
      rolekey: 'pos',
    },
    {
      images: wareHouseImage,
      state: 'disabled',
      rolekey: 'wh',
    },
    {
      images: accountingImage,
      state: 'disabled',
      rolekey: 'wh',
    },
  ]);

  const twoFactorAuthStatus = ref<boolean>(false);
  const showQRCodeField = ref<boolean>(false);
  const showRecoveryCodesField = ref<boolean>(false);
  const showSecretKeyField = ref<boolean>(false);

  const qrCode = ref<QRCode>({
    svg: '',
    url: '',
  });
  const twoFactorCode = ref<string>('');
  const twoFactorCodeErrorText = ref<string>('');
  const twoFactorRecoveryCodes = ref<Array<string>>([]);
  const twoFactorSecretKey = ref<string>('');

  const confirmPasswordStatus = ref<ConfirmPasswordStatusResponse>({
    confirmed: false,
  });
  const showConfirmPasswordDialog = ref<boolean>(false);
  const confirmPasswordPurpose = ref<'2FA' | 'QRCODE' | ''>('');
  const confirmPasswordText = ref<string>('');
  const confirmPasswordErrorText = ref<string>('');

  const updateUserProfileForm = profileServices.useUpdateUserProfileForm();
  const updatePersonalInfoForm = profileServices.useUpdatePersonalInfoForm();
  const updateAccountSettingsForm = profileServices.useUpdateAccountSettingsForm();
  const updateUserRolesForm = profileServices.useUpdateUserRolesForm();
  const updatePasswordForm = profileServices.useUpdatePasswordForm();
  const updateTokensForm = profileServices.useUpdateTokenForm();

  const alertType = ref<'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark'>('hidden');
  const alertTitle = ref<string>('');
  const alertList = ref<Record<string, Array<string>> | null>(null);
  // #endregion

  // #region Provide/Inject
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

    roleSelection.value.forEach((r) => {
      if (r.rolekey == 'pos' && hasRolePOSOwner()) {
        r.state = 'checked';
      } else if (r.rolekey == 'wh' && hasRoleWHOwner()) {
        r.state = 'checked';
      } else if (r.rolekey == 'acc' && hasRoleACCOwner()) {
        r.state = 'checked';
      } else {
        r.state = 'selectable';
      }
    });
  };

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
      roles: activeRole,
    });
  };

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

  const setTwoFactorAuthStatus = async () => {
    twoFactorAuthStatus.value = userContext.value.two_factor;
  };

  const setTwoFactor = async (event: Event) => {
    let checked: boolean = (event.target as HTMLInputElement).checked;
    twoFactorAuthStatus.value = checked;

    await checkConfirmPasswordStatus();

    if (confirmPasswordStatus.value.confirmed) {
      await setTwoFactorWithoutOrAfterConfirmPassword();
    } else {
      confirmPasswordPurpose.value = '2FA';
      await setTwoFactorWithConfirmPassword();
    }
  };

  const setTwoFactorWithConfirmPassword = async () => {
    confirmPasswordText.value = '';
    confirmPasswordErrorText.value = '';
    showConfirmPasswordDialog.value = true;
  };

  const setTwoFactorWithoutOrAfterConfirmPassword = async () => {
    if (twoFactorAuthStatus.value) {
      await profileServices.enableTwoFactor();
      await showQR();
    } else {
      await profileServices.disableTwoFactor();
      await reloadUserContext();
    }
  };

  const doConfirmTwoFactorAuthentication = async () => {
    let code = twoFactorCode.value;
    let response: ServiceResponse<TwoFactorResponse | null> =
      await profileServices.TwoFactorAuthenticationConfirmed(code);

    if (response.success) {
      showQRCodeField.value = false;

      await reloadUserContext();
      await showRecoveryCodes();
      await showSecretKey();
    } else {
      twoFactorCodeErrorText.value = t('views.profile.fields.2fa.confirm_2fa_auth_error');
    }
  };

  const showQR = async () => {
    await checkConfirmPasswordStatus();

    if (confirmPasswordStatus.value.confirmed) {
      await showQRWithoutOrAfterConfirmPassword();
    } else {
      confirmPasswordPurpose.value = 'QRCODE';
      await showQRWithConfirmPassword();
    }
  };

  const showQRWithoutOrAfterConfirmPassword = async () => {
    let response: ServiceResponse<QRCode | null> = await profileServices.twoFactorQR();

    if (response.success && response.data) {
      qrCode.value = response.data;
      showQRCodeField.value = true;
    }
  };

  const showQRWithConfirmPassword = async () => {
    confirmPasswordText.value = '';
    confirmPasswordErrorText.value = '';
    showConfirmPasswordDialog.value = true;
  };

  const showRecoveryCodes = async () => {
    let response: ServiceResponse<Array<string> | null> = await profileServices.twoFactorRecoveryCodes();

    if (response.success && response.data) {
      twoFactorRecoveryCodes.value = response.data;
      showRecoveryCodesField.value = true;
    }
  };

  const showSecretKey = async () => {
    let response: ServiceResponse<SecretKeyResponse | null> = await profileServices.twoFactorSecretKey();

    if (response.success) {
      if (response.data) {
        twoFactorSecretKey.value = response.data.secretKey;
        showSecretKeyField.value = true;
      }
    }
  };

  const checkConfirmPasswordStatus = async () => {
    let response: ServiceResponse<ConfirmPasswordStatusResponse | null> = await profileServices.confirmPasswordStatus();

    if (response.success && response.data) {
      confirmPasswordStatus.value = response.data;
    }
  };

  const submitConfirmPassword = async () => {
    let response: ServiceResponse<TwoFactorResponse | null> = await profileServices.confirmPassword(
      confirmPasswordText.value,
    );

    if (response.success) {
      switch (confirmPasswordPurpose.value) {
        case '2FA':
          await setTwoFactorWithoutOrAfterConfirmPassword();
          break;
        case 'QRCODE':
          await showQRWithoutOrAfterConfirmPassword();
          break;
        case '':
        default:
          break;
      }

      await closeConfirmPasswordDialog();
    } else {
      confirmPasswordErrorText.value = t('views.profile.fields.2fa.confirm_password_error');
    }
  };

  const closeConfirmPasswordDialog = async () => {
    showConfirmPasswordDialog.value = false;

    confirmPasswordText.value = '';
    confirmPasswordErrorText.value = '';

    confirmPasswordPurpose.value = '';

    await reloadUserContext();
  };

  const reloadUserContext = async () => {
    let userprofile = await profileServices.readProfile();
    userContextStore.setUserContext(userprofile.data as UserProfile);
  };

  const sendEmailVerification = async () => {
    loading.value = true;

    let result = await profileServices.sendEmailVerification();

    if (result.success && sendVerificationEmailNotification.value) {
      sendVerificationEmailNotification.value.showToast();
    }

    loading.value = false;
  };

  const updateUserProfile = async () => {
    let userprofile = await profileServices.readProfile();
    if (userprofile.success) {
      userContextStore.setUserContext(userprofile.data as UserProfile);
    }
  };

  const updateUserMenu = async () => {
    let menuResult = await dashboardServices.readUserMenu();
    menuStore.setMenu(menuResult.data as Array<sMenu>);

    let apiResult = await dashboardServices.readUserApi();
    ziggyRouteStore.setZiggy(apiResult.data as Config);
  };

  const onSubmitUpdateUserProfile = async () => {
    loading.value = true;

    await updateUserProfileForm
      .submit()
      .then(async () => {
        await updateUserProfile();
      })
      .catch((error) => {
        let errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error as Error);
        showAlertPlaceholder('danger', '', errorList);
      })
      .finally(() => {
        loading.value = false;
      });
  };

  const onSubmitUpdatePersonalInfo = async () => {
    loading.value = true;

    await updatePersonalInfoForm
      .submit()
      .then(async () => {
        await updateUserProfile();
      })
      .catch((error) => {
        let errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error as Error);
        showAlertPlaceholder('danger', '', errorList);
      })
      .finally(() => {
        loading.value = false;
      });
  };

  const onSubmitUpdateAccountSettings = async () => {
    loading.value = true;

    await updateAccountSettingsForm
      .submit()
      .then(async () => {
        await updateUserProfile();
      })
      .catch((error) => {
        let errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error as Error);
        showAlertPlaceholder('danger', '', errorList);
      })
      .finally(() => {
        loading.value = false;
      });
  };

  const onSubmitUpdateUserRoles = async () => {
    loading.value = true;

    await updateUserRolesForm
      .submit()
      .then(async () => {
        await updateUserProfile();
        await updateUserMenu();
      })
      .catch((error) => {
        let errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error as Error);
        showAlertPlaceholder('danger', '', errorList);
      })
      .finally(() => {
        loading.value = false;
      });
  };

  const onSubmitUpdatePassword = async () => {
    loading.value = true;

    await updatePasswordForm
      .submit()
      .then(async () => {
        await updateUserProfile();
        updatePasswordForm.reset();
      })
      .catch((error) => {
        let errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error as Error);
        showAlertPlaceholder('danger', '', errorList);
      })
      .finally(() => {
        loading.value = false;
      });
  };

  const onSubmitUpdateToken = async () => {
    loading.value = true;

    await updateTokensForm
      .submit()
      .then(async () => {
        await updateUserProfile();
        updateTokensForm.reset();
      })
      .catch((error) => {
        let errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error as Error);
        showAlertPlaceholder('danger', '', errorList);
      })
      .finally(() => {
        loading.value = false;
      });
  };

  const showAlertPlaceholder = (
    pAlertType: 'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark',
    pTitle: string,
    pAlertList: Record<string, Array<string>> | null,
  ) => {
    alertType.value = pAlertType;
    alertTitle.value = pTitle;
    alertList.value = pAlertList;
  };

  const resetAlertPlaceholder = () => {
    alertTitle.value = '';
    alertList.value = null;
    alertType.value = 'hidden';
  };

  const convertErrorTypeToAlertListType = (error: Error) => {
    const record: Record<string, Array<string>> = {};

    record.error = [error.message];

    return record;
  };
  // #region Methods

  // #region Watchers
  watchEffect(async () => {
    if (userContextIsLoaded.value) {
      setFormData();
      await setTwoFactorAuthStatus();

      loading.value = false;
    }
  });
  // #endregion
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <TitleLayout>
        <template #title>
          {{ t('views.profile.title') }}
        </template>
      </TitleLayout>

      <AlertPlaceholder
        :alert-type="alertType"
        :title="alertTitle"
        :alert-list="alertList"
        @dismiss="resetAlertPlaceholder"
      />

      <TwoColumnsLayout
        :cards="cards"
        :show-side-tab="true"
        :using-side-tab="true"
        @handleExpandCard="handleExpandCard"
      >
        <template #side-menu-title>
          {{ userContext.name }}
        </template>
        <template #card-items-0>
          <div class="p-5">
            <form id="updateUserProfileForm" @submit.prevent="onSubmitUpdateUserProfile">
              <div class="pb-4">
                <FormLabel
                  :class="{
                    'text-danger': updateUserProfileForm.invalid('name'),
                  }"
                >
                  {{ t('views.profile.fields.name') }}
                </FormLabel>
                <FormInput
                  v-model="updateUserProfileForm.name"
                  type="text"
                  :class="{
                    'border-danger': updateUserProfileForm.invalid('name'),
                  }"
                  :placeholder="t('views.profile.fields.name')"
                  @change="updateUserProfileForm.validate('name')"
                />
                <FormErrorMessages :messages="updateUserProfileForm.errors.name" />
              </div>
              <div class="pb-4">
                <FormLabel>
                  {{ t('views.profile.fields.email') }}
                </FormLabel>
                <FormInput
                  v-model="userContext.email"
                  type="text"
                  :placeholder="t('views.profile.fields.email')"
                  disabled
                />
              </div>
              <div>
                <Button
                  type="submit"
                  size="sm"
                  href="#"
                  variant="primary"
                  class="w-28 shadow-md"
                  :disabled="updateUserProfileForm.validating || updateUserProfileForm.hasErrors"
                >
                  <Lucide v-if="updateUserProfileForm.validating" icon="Loader" class="animate-spin" />
                  <template v-else>
                    {{ t('components.buttons.update') }}
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
              <Button
                type="button"
                size="sm"
                href="#"
                variant="primary"
                class="w-42 shadow-md"
                @click="sendEmailVerification"
              >
                {{ t('components.buttons.send_verification_email') }}
              </Button>
            </div>
            <Notification ref-key="sendVerificationEmailNotification" :options="{ duration: 3000 }" class="flex">
              <Lucide icon="CheckCircle" class="text-success" />
              <div class="ml-4 mr-4">
                <div class="font-medium">
                  {{ t('views.profile.alert.verification_email_sent.title') }}
                </div>
                <div class="mt-1 text-slate-500">
                  {{ t('views.profile.alert.verification_email_sent.content') }}
                </div>
              </div>
            </Notification>
          </div>
        </template>
        <template #card-items-2>
          <div class="p-5">
            <form id="updatePersonalInfoForm" @submit.prevent="onSubmitUpdatePersonalInfo">
              <div class="pb-4">
                <FormLabel>
                  {{ t('views.profile.fields.first_name') }}
                </FormLabel>
                <FormInput
                  v-model="updatePersonalInfoForm.first_name"
                  type="text"
                  :placeholder="t('views.profile.fields.first_name')"
                />
              </div>
              <div class="pb-4">
                <FormLabel>
                  {{ t('views.profile.fields.last_name') }}
                </FormLabel>
                <FormInput
                  v-model="updatePersonalInfoForm.last_name"
                  type="text"
                  :placeholder="t('views.profile.fields.last_name')"
                />
              </div>
              <div class="pb-4">
                <FormLabel>
                  {{ t('views.profile.fields.address') }}
                </FormLabel>
                <FormTextarea
                  v-model="updatePersonalInfoForm.address"
                  rows="5"
                  :placeholder="t('views.profile.fields.address')"
                />
              </div>
              <div class="flex gap-2">
                <div class="pb-4 w-full">
                  <FormLabel>
                    {{ t('views.profile.fields.city') }}
                  </FormLabel>
                  <FormInput
                    v-model="updatePersonalInfoForm.city"
                    type="text"
                    class="w-full"
                    :placeholder="t('views.profile.fields.city')"
                  />
                </div>
                <div class="pb-4">
                  <FormLabel>
                    {{ t('views.profile.fields.postal_code') }}
                  </FormLabel>
                  <FormInput
                    v-model="updatePersonalInfoForm.postal_code"
                    type="number"
                    :placeholder="t('views.profile.fields.postal_code')"
                  />
                </div>
              </div>
              <div class="pb-4">
                <FormLabel>
                  {{ t('views.profile.fields.country') }}
                </FormLabel>
                <FormSelect
                  v-model="updatePersonalInfoForm.country"
                  :class="{
                    'border-danger': updatePersonalInfoForm.invalid('country'),
                  }"
                  :placeholder="t('views.profile.fields.country')"
                  @change="
                    updatePersonalInfoForm.validate('country');
                    updatePersonalInfoForm.submit();
                  "
                >
                  <option>Singapore</option>
                  <option>Indonesia</option>
                </FormSelect>
                <FormErrorMessages :messages="updatePersonalInfoForm.errors.country" />
              </div>
              <div class="pb-4">
                <FormLabel
                  :class="{
                    'text-danger': updatePersonalInfoForm.invalid('tax_id'),
                  }"
                >
                  {{ t('views.profile.fields.tax_id') }}
                </FormLabel>
                <FormInput
                  v-model="updatePersonalInfoForm.tax_id"
                  type="text"
                  :class="{
                    'border-danger': updatePersonalInfoForm.invalid('tax_id'),
                  }"
                  :placeholder="t('views.profile.fields.tax_id')"
                  @change="
                    updatePersonalInfoForm.validate('tax_id');
                    updatePersonalInfoForm.submit();
                  "
                />
                <FormErrorMessages :messages="updatePersonalInfoForm.errors.tax_id" />
              </div>
              <div class="pb-4">
                <FormLabel
                  :class="{
                    'text-danger': updatePersonalInfoForm.invalid('ic_num'),
                  }"
                >
                  {{ t('views.profile.fields.ic_num') }}
                </FormLabel>
                <FormInput
                  v-model="updatePersonalInfoForm.ic_num"
                  type="text"
                  :class="{
                    'border-danger': updatePersonalInfoForm.invalid('ic_num'),
                  }"
                  :placeholder="t('views.profile.fields.ic_num')"
                  @change="updatePersonalInfoForm.validate('ic_num')"
                />
              </div>
              <div class="pb-4">
                <FormLabel>
                  {{ t('views.profile.fields.remarks') }}
                </FormLabel>
                <FormTextarea
                  v-model="updatePersonalInfoForm.remarks"
                  rows="5"
                  :placeholder="t('views.profile.fields.remarks')"
                />
              </div>
              <div>
                <Button
                  type="submit"
                  size="sm"
                  href="#"
                  variant="primary"
                  class="w-28 shadow-md"
                  :disabled="updateUserProfileForm.validating || updateUserProfileForm.hasErrors"
                >
                  <Lucide v-if="updateUserProfileForm.validating" icon="Loader" class="animate-spin" />
                  <template v-else>
                    {{ t('components.buttons.update') }}
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
                <FormLabel>
                  {{ t('views.profile.fields.settings.theme') }}
                </FormLabel>
                <FormSelect v-model="updateAccountSettingsForm.theme">
                  <option value="side-menu-light-full">Menu Light</option>
                  <option value="side-menu-light-mini">Mini Menu Light</option>
                  <option value="side-menu-dark-full">Menu Dark</option>
                  <option value="side-menu-dark-mini">Mini Menu Dark</option>
                </FormSelect>
              </div>
              <div class="pb-4">
                <FormLabel>
                  {{ t('views.profile.fields.settings.date_format') }}
                </FormLabel>
                <FormSelect v-model="updateAccountSettingsForm.date_format">
                  <option value="yyyy_MM_dd">
                    {{ formatDate(new Date().toString(), 'YYYY-MM-DD') }}
                  </option>
                  <option value="dd_MMM_yyyy">
                    {{ formatDate(new Date().toString(), 'DD-MMM-YYYY') }}
                  </option>
                </FormSelect>
              </div>
              <div class="pb-4">
                <FormLabel>
                  {{ t('views.profile.fields.settings.time_format') }}
                </FormLabel>
                <FormSelect v-model="updateAccountSettingsForm.time_format">
                  <option value="hh_mm_ss">
                    {{ formatDate(new Date().toString(), 'HH:mm:ss') }}
                  </option>
                  <option value="h_m_A">
                    {{ formatDate(new Date().toString(), 'H:m A') }}
                  </option>
                </FormSelect>
              </div>
              <div>
                <Button
                  type="submit"
                  size="sm"
                  href="#"
                  variant="primary"
                  class="w-28 shadow-md"
                  :disabled="updateUserProfileForm.validating || updateUserProfileForm.hasErrors"
                >
                  <Lucide v-if="updateUserProfileForm.validating" icon="Loader" class="animate-spin" />
                  <template v-else>
                    {{ t('components.buttons.update') }}
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
                  <div v-for="(item, index) in roleSelection" :key="index" class="flex flex-col items-center">
                    <div
                      :class="{
                        'cursor-pointer': item.state == 'selectable',
                        'flex flex-col items-center justify-center': true,
                      }"
                      @click="handleChangeRole(index)"
                    >
                      <img alt="" :src="item.images" width="100" height="100" />
                      <div v-if="item.state == 'checked'" class="grid grid-cols-1 place-items-center">
                        <Lucide icon="Check" class="text-success" />
                      </div>
                      <Button
                        v-else-if="item.state == 'selectable'"
                        type="submit"
                        variant="primary"
                        size="sm"
                        class="w-28 shadow-md"
                      >
                        {{ t('components.buttons.activate') }}
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
                <FormLabel
                  :class="{
                    'text-danger': updatePasswordForm.invalid('current_password'),
                  }"
                >
                  {{ t('views.profile.fields.change_password.current_password') }}
                </FormLabel>
                <FormInput
                  v-model="updatePasswordForm.current_password"
                  type="password"
                  :class="{
                    'border-danger': updatePasswordForm.invalid('current_password'),
                  }"
                  :placeholder="t('views.profile.fields.change_password.current_password')"
                  @change="updatePasswordForm.validate('current_password')"
                />
                <FormErrorMessages :messages="updatePasswordForm.errors.current_password" />
              </div>
              <div class="pb-4">
                <FormLabel
                  :class="{
                    'text-danger': updatePasswordForm.invalid('password'),
                  }"
                >
                  {{ t('views.profile.fields.change_password.password') }}
                </FormLabel>
                <FormInput
                  v-model="updatePasswordForm.password"
                  type="password"
                  :class="{
                    'border-danger': updatePasswordForm.invalid('password'),
                  }"
                  :placeholder="t('views.profile.fields.change_password.password')"
                />
                <FormErrorMessages :messages="updatePasswordForm.errors.password" />
              </div>
              <div class="pb-4">
                <FormLabel>
                  {{ t('views.profile.fields.change_password.password_confirmation') }}
                </FormLabel>
                <FormInput
                  v-model="updatePasswordForm.password_confirmation"
                  type="password"
                  :placeholder="t('views.profile.fields.change_password.password_confirmation')"
                />
              </div>
              <div>
                <Button
                  type="submit"
                  size="sm"
                  href="#"
                  variant="primary"
                  class="w-28 shadow-md"
                  :disabled="updateUserProfileForm.validating || updateUserProfileForm.hasErrors"
                >
                  <Lucide v-if="updateUserProfileForm.validating" icon="Loader" class="animate-spin" />
                  <template v-else>
                    {{ t('components.buttons.update') }}
                  </template>
                </Button>
              </div>
            </form>
          </div>
        </template>
        <template #card-items-6>
          <div class="p-5">
            <form id="updateTokenForm" @submit.prevent="onSubmitUpdateToken">
              <div class="pb-4">
                <FormLabel>
                  {{ t('views.profile.fields.api_token.total_token_generated') }}&nbsp;:&nbsp;{{
                    userContext.personal_access_tokens
                  }}
                </FormLabel>
              </div>
              <input id="resetToken" type="hidden" v-model="updateTokensForm.reset_tokens" />
              <div>
                <Button
                  type="submit"
                  size="sm"
                  href="#"
                  variant="primary"
                  class="w-28 shadow-md"
                  :disabled="updateTokensForm.validating || updateTokensForm.hasErrors"
                >
                  <Lucide v-if="updateTokensForm.validating" icon="Loader" class="animate-spin" />
                  <template v-else>
                    {{ t('components.buttons.reset') }}
                  </template>
                </Button>
              </div>
            </form>
          </div>
        </template>
        <template #card-items-7>
          <div class="p-5">
            <div class="pb-4">
              <FormLabel>
                {{ t('views.profile.fields.2fa.status') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input type="checkbox" @change="setTwoFactor" v-model="twoFactorAuthStatus" />
              </FormSwitch>
            </div>
            <div v-if="showQRCodeField" class="pb-4">
              <img v-html="qrCode.svg" alt="QR Code" />
              <br />
              {{ t('views.profile.fields.2fa.qr-code_description_1') }}
              <br />
              {{ t('views.profile.fields.2fa.qr-code_description_2') }}
              <br />
              <img :src="googlePlayBadge" alt="Google Play" width="120" height="120" />
              <br />
              {{ t('views.profile.fields.2fa.confirm_2fa_auth_description_1') }}
              <br />
              {{ t('views.profile.fields.2fa.confirm_2fa_auth_description_2') }}
              <br />
              <br />
              <FormLabel>
                {{ t('views.profile.fields.2fa.confirm_2fa_auth') }}
              </FormLabel>
              <FormInput v-model="twoFactorCode" />
              <FormErrorMessages v-if="twoFactorCodeErrorText != ''" :messages="twoFactorCodeErrorText" />
              <br />
              <Button
                type="button"
                variant="primary"
                @click="
                  () => {
                    doConfirmTwoFactorAuthentication();
                  }
                "
                class="mt-2 w-24"
              >
                {{ t('components.buttons.submit') }}
              </Button>
            </div>
            <div v-if="showRecoveryCodesField" class="pb-4">
              {{ t('views.profile.fields.2fa.recovery-codes_description_1') }}
              <br />
              {{ t('views.profile.fields.2fa.recovery-codes_description_2') }}
              <br />
              <br />
              <FormLabel>
                {{ t('views.profile.fields.2fa.recovery-codes') }}
              </FormLabel>
              <div>
                <template v-for="(rc, rcIdx) in twoFactorRecoveryCodes">
                  <span class="italic">{{ rcIdx + 1 }}. {{ rc }}</span>
                  <br />
                </template>
              </div>
            </div>
            <div v-if="showSecretKeyField" class="pb-4">
              {{ t('views.profile.fields.2fa.secret-key_description_1') }}
              <br />
              {{ t('views.profile.fields.2fa.secret-key_description_2') }}
              <br />
              <br />
              <FormLabel>
                {{ t('views.profile.fields.2fa.secret-key') }}
              </FormLabel>
              <div class="italic">
                {{ twoFactorSecretKey }}
              </div>
            </div>
            <Dialog
              staticBackdrop
              :open="showConfirmPasswordDialog"
              @close="
                () => {
                  closeConfirmPasswordDialog();
                }
              "
            >
              <Dialog.Panel class="px-5 py-10">
                <div class="text-center">
                  <div class="mb-5">
                    <FormLabel>
                      {{ t('views.profile.fields.2fa.confirm_password') }}
                    </FormLabel>
                    <FormInput
                      v-model="confirmPasswordText"
                      type="password"
                      :placeholder="t('views.profile.fields.2fa.confirm_password')"
                    />
                    <FormErrorMessages v-if="confirmPasswordErrorText != ''" :messages="confirmPasswordErrorText" />
                  </div>
                  <div class="flex gap-2 justify-center items-center">
                    <Button
                      type="button"
                      variant="primary"
                      @click="
                        () => {
                          submitConfirmPassword();
                        }
                      "
                      class="w-24"
                    >
                      {{ t('components.buttons.submit') }}
                    </Button>
                    <Button
                      type="button"
                      variant="primary"
                      @click="
                        () => {
                          closeConfirmPasswordDialog();
                        }
                      "
                      class="w-24"
                    >
                      {{ t('components.buttons.cancel') }}
                    </Button>
                  </div>
                </div>
              </Dialog.Panel>
            </Dialog>
          </div>
        </template>
      </TwoColumnsLayout>
    </LoadingOverlay>
  </div>
</template>
