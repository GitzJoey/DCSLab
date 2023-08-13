<script setup lang="ts">
//#region Imports
import { computed, ref } from "vue";
import { useI18n } from "vue-i18n";
import {
  FormInput,
  FormLabel,
  FormTextarea,
  FormSelect,
} from "../../base-components/Form";
import { useUserContextStore } from "../../stores/user-context";
import {
  TitleLayout,
  TwoColumnsLayout,
} from "../../base-components/Form/FormLayout";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import AlertPlaceholder from "../../base-components/AlertPlaceholder";
import { CardState } from "../../types/enums/CardState";
import posSystemImage from "../../assets/images/pos_system.png";
import wareHouseImage from "../../assets/images/warehouse_system.png";
import accountingImage from "../../assets/images/accounting_system.jpg";
import { Check } from "lucide-vue-next";
import Button from "../../base-components/Button";
import { formatDate } from "../../utils/helper";
import ProfileService from "../../services/ProfileService";
//#endregion

//#region Declarations
const { t } = useI18n();
const userContextStore = useUserContextStore();
type Roles = {
  active: boolean;
  images: string;
  value: string;
};
const profileServices = new ProfileService();
//#endregion

//#region Data - Pinia
const userContext = computed(() => userContextStore.getUserContext);
//#endregion

//#region Data - UI
const loading = ref<boolean>(false);
const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: "User Profile", state: CardState.Expanded },
  { title: "Account Settings", state: CardState.Expanded },
  { title: "Change Password", state: CardState.Expanded },
  { title: "User Setting", state: CardState.Expanded },
  { title: "Roles", state: CardState.Expanded, id: "roles" },
]);

const roles = ref<Array<Roles>>([
  {
    images: posSystemImage,
    active: false,
    value: "pos",
  },
  {
    images: wareHouseImage,
    active: false,
    value: "wh",
  },
  {
    images: accountingImage,
    active: false,
    value: "wh",
  },
]);
//#endregion

//#region Data - Views
const passwordSetting = ref({
  currentPassword: "",
  new_password: "",
  confirm_password: "",
});
//#endregion

//#region onMounted
//#endregion

//#region Computed
//#endregion

// Region Method
const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

async function handleChangeRole(index: number) {
  loading.value = true;
  roles.value[index].active = !roles.value[index].active;
  for (let i = 0; i < roles.value.length; i++) {
    if (roles.value[index].active && i !== index) {
      roles.value[i].active = false;
    }
  }

  if (roles.value[index].active) {
    const role = roles.value[index].value;
    await profileServices.updateRoles(role);
  }
  loading.value = false;
}

async function handleUpdateAccountSettings(value: {
  first_name: string;
  last_name: string;
  address: string;
  city: string;
  postal_code: string;
  country: string;
  tax_id: number;
  ic_num: number;
  remarks: string;
}): Promise<void> {
  loading.value = true;
  await profileServices.updateProfile(value);
  loading.value = false;
}

async function handleChangePassword(value: {
  password: string;
  confirm_password: string;
  current_password: string;
}): Promise<void> {
  loading.value = true;
  await profileServices.updatePassword(value);
  loading.value = false;
}

async function handleSubmitUserSetting(value: {
  theme: string;
  date_format: string;
  time_format: string;
}): Promise<void> {
  loading.value = true;
  await profileServices.updateUserSetting(value);
  loading.value = false;
}

async function handleSubmitUserProfile(values: {
  name: string;
}): Promise<void> {
  loading.value = true;
  await profileServices.updateUser(values);
  loading.value = false;
}
// const onSubmit = async () => {
//   loading.value = true;
//   loading.value = false;
// };
// End Region Method

//#region Watcher
//#endregion
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <TitleLayout>
        <template #title>
          {{ t("views.profile.title") }}
        </template>
      </TitleLayout>
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
          <VeeForm
            id="userProfie"
            v-slot="{ errors }"
            @submit="handleSubmitUserProfile"
          >
            <AlertPlaceholder :errors="errors" />
            <div class="p-5">
              <div class="pb-4">
                <FormLabel html-for="name" :class="{'text-danger' : errors['name']}">
                  {{ t("views.profile.fields.name") }}
                </FormLabel>
                <VeeField
                  v-slot="{ field }"
                  v-model="userContext.name"
                  name="name"
                  rules="required"
                  :label="t('views.profile.fields.name')"
                >
                  <FormInput
                    id="name"
                    name="name"
                    type="text"
                    :class="{'w-full' : true, 'border-danger': errors['status']}"
                    v-bind="field"
                    :placeholder="t('views.profile.fields.name')"
                  />
                </VeeField>
              </div>
              <div class="pb-4">
                <FormLabel html-for="email">
                  {{ t("views.profile.fields.email") }}
                </FormLabel>
                <FormInput
                  id="email"
                  v-model="userContext.email"
                  name="email"
                  type="text"
                  class="w-full"
                  :placeholder="t('views.profile.fields.email')"
                  readonly
                />
              </div>

              <div class="flex gap-4">
                <Button variant="primary" class="w-28 shadow-md">
                  {{ t("components.buttons.submit") }}
                </Button>
              </div>
            </div>
          </VeeForm>
        </template>

        <template #card-items-1>
          <VeeForm
            id="accountSetting"
            v-slot="{ errors }"
            @submit="handleUpdateAccountSettings"
          >
            <AlertPlaceholder :errors="errors" />
            <div class="p-5">
              <div class="pb-4">
                <VeeField
                  v-slot="{ field }"
                  v-model="userContext.profile.first_name"
                  name="first_name"
                  :label="t('views.profile.fields.first_name')"
                >
                  <FormLabel
                    v-bind="field"
                    html-for="first_name"
                    :class="{
                      'flex flex-col w-full sm:flex-row': true,
                      'text-danger': errors['first_name'],
                    }"
                  >
                    {{ t("views.profile.fields.first_name") }}
                  </FormLabel>
                  <FormInput
                    id="first_name"
                    v-model="userContext.profile.first_name"
                    name="first_name"
                    type="text"
                    class="w-full"
                    :placeholder="t('views.profile.fields.first_name')"
                  />
                </VeeField>
              </div>
              <div class="pb-4">
                <FormLabel html-for="last_name">
                  {{ t("views.profile.fields.last_name") }}
                </FormLabel>
                <VeeField
                  v-slot="{ field }"
                  v-model="userContext.profile.last_name"
                  name="last_name"
                >
                  <FormInput
                    id="last_name"
                    v-bind="field"
                    name="last_name"
                    type="text"
                    class="w-full"
                    :placeholder="t('views.profile.fields.last_name')"
                  />
                </VeeField>
              </div>
              <div class="pb-4">
                <FormLabel html-for="address">
                  {{ t("views.profile.fields.address") }}
                </FormLabel>
                <VeeField
                  v-slot="{ field }"
                  v-model="userContext.profile.address"
                  name="address"
                >
                  <FormTextarea
                    id="address"
                    v-bind="field"
                    name="address"
                    class="w-full"
                    rows="5"
                    :placeholder="t('views.profile.fields.address')"
                  />
                </VeeField>
              </div>
              <div class="flex gap-2">
                <div class="pb-4 w-full">
                  <FormLabel html-for="city">
                    {{ t("views.profile.fields.city") }}
                  </FormLabel>
                  <VeeField
                    v-slot="{ field }"
                    v-model="userContext.profile.city"
                    name="city"
                  >
                    <FormInput
                      id="address"
                      v-bind="{ field }"
                      name="city"
                      type="text"
                      class="w-full"
                      :placeholder="t('views.profile.fields.city')"
                    />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="postal_code">
                    {{ t("views.profile.fields.postal_code") }}
                  </FormLabel>
                  <VeeField
                    v-slot="{ field }"
                    v-model="userContext.profile.postal_code"
                    name="postal_code"
                  >
                    <FormInput
                      id="postal_code"
                      v-bind="field"
                      name="postal_code"
                      type="number"
                      class="w-full"
                      :placeholder="t('views.profile.fields.postal_code')"
                    />
                  </VeeField>
                </div>
              </div>
              <div class="pb-4">
                <FormLabel html-for="country">
                  {{ t("views.profile.fields.country") }}
                </FormLabel>
                <VeeField
                  v-slot="{ field }"
                  v-model="userContext.profile.country"
                  name="country"
                >
                  <FormSelect
                    id="country"
                    v-bind="field"
                    name="country"
                    :class="{'w-full' : true, 'border-danger':errors['country']}"
                    :placeholder="t('views.profile.fields.country')"
                  >
                    <option>Singapore</option>
                    <option>Indonesia</option>
                  </FormSelect>
                </VeeField>
              </div>
              <div class="pb-4">
                <FormLabel html-for="tax_id">
                  {{ t("views.profile.fields.tax_id") }}
                </FormLabel>
                <VeeField
                  v-slot="{ field }"
                  v-model="userContext.profile.tax_id"
                  name="tax_id"
                  rules="required"
                  :label="t('views.profile.fields.tax_id')"
                >
                  <FormInput
                    id="tax_id"
                    v-bind="field"
                    name="tax_id"
                    type="text"
                    :class="{'w-full' : true, 'border-danger': errors['tax_id']}"
                    :placeholder="t('views.profile.fields.tax_id')"
                  />
                </VeeField>
              </div>
              <div class="pb-4">
                <FormLabel html-for="ic_num">
                  {{ t("views.profile.fields.ic_num") }}
                </FormLabel>
                <VeeField
                  v-slot="{ field }"
                  v-model="userContext.profile.ic_num"
                  name="ic_num"
                  rules="required"
                >
                  <FormInput
                    id="ic_num"
                    v-bind="field"
                    name="ic_num"
                    type="text"
                    :class="{'w-full': true, 'border-danger': errors['ic_num']}"
                    :placeholder="t('views.profile.fields.ic_num')"
                  />
                </VeeField>
              </div>
              <div class="pb-4">
                <FormLabel html-for="remarks">
                  {{ t("views.profile.fields.remarks") }}
                </FormLabel>
                <VeeField
                  v-slot="{ field }"
                  v-model="userContext.profile.remarks"
                  name="remarks"
                >
                  <FormTextarea
                    id="remarks"
                    v-bind="field"
                    name="remarks"
                    class="w-full"
                    rows="5"
                    :placeholder="t('views.profile.fields.remarks')"
                  />
                </VeeField>
              </div>

              <div class="flex gap-4">
                <Button variant="primary" class="w-28 shadow-md">
                  {{ t("components.buttons.submit") }}
                </Button>
              </div>
            </div>
          </VeeForm>
        </template>

        <template #card-items-2>
          <VeeForm
            id="password"
            v-slot="{ errors }"
            @submit="handleChangePassword"
          >
            <AlertPlaceholder :errors="errors" />
            <div class="p-5">
              <div class="pb-4">
                <FormLabel html-for="currentPassword">
                  {{
                    t("views.profile.fields.change_password.current_password")
                  }}
                </FormLabel>
                <VeeField
                  v-slot="{ field }"
                  v-model="passwordSetting.currentPassword"
                  name="currentPassword"
                  rules="required"
                >
                  <FormInput
                    id="currentPassword"
                    v-bind="field"
                    name="currentPassword"
                    type="password"
                    class="w-full"
                    :placeholder="
                      t('views.profile.fields.change_password.current_password')
                    "
                  />
                </VeeField>
              </div>

              <div class="pb-4">
                <FormLabel html-for="new_password">
                  {{ t("views.profile.fields.change_password.new_password") }}
                </FormLabel>
                <VeeField
                  v-slot="{ field }"
                  v-model="passwordSetting.new_password"
                  name="new_password"
                >
                  <FormInput
                    id="new_password"
                    v-bind="field"
                    name="new_password"
                    type="password"
                    class="w-full"
                    :placeholder="
                      t('views.profile.fields.change_password.new_password')
                    "
                  />
                </VeeField>
              </div>

              <div class="pb-4">
                <FormLabel html-for="confirm_password">
                  {{
                    t("views.profile.fields.change_password.confirm_password")
                  }}
                </FormLabel>
                <VeeField
                  v-slot="{ field }"
                  v-model="passwordSetting.confirm_password"
                  name="confirm_password"
                >
                  <FormInput
                    id="confirm_password"
                    v-bind="field"
                    name="confirm_password"
                    type="password"
                    class="w-full"
                    :placeholder="
                      t('views.profile.fields.change_password.confirm_password')
                    "
                  />
                </VeeField>
              </div>

              <div class="flex gap-4">
                <Button variant="primary" class="w-28 shadow-md">
                  {{ t("components.buttons.submit") }}
                </Button>
              </div>
            </div>
          </VeeForm>
        </template>

        <template #card-items-3>
          <VeeForm
            id="userSetting"
            v-slot="{ errors }"
            @submit="handleSubmitUserSetting"
          >
            <AlertPlaceholder :errors="errors" />
            <div class="p-5">
              <div class="pb-4">
                <FormLabel html-for="themes">
                  {{ t("views.profile.fields.settings.theme") }}
                </FormLabel>
                <VeeField
                  v-slot="{ field }"
                  v-model="userContext.settings.theme"
                  name="theme"
                  rules="required"
                  :label="t(t('views.profile.fields.settings.theme'))"
                >
                  <FormSelect id="themes" name="themes" v-bind="field">
                    <option value="side-menu-light-full">Menu Light</option>
                    <option value="side-menu-light-mini">
                      Mini Menu Light
                    </option>
                    <option value="side-menu-dark-full">Menu Dark</option>
                    <option value="side-menu-dark-mini">Mini Menu Dark</option>
                  </FormSelect>
                </VeeField>
              </div>

              <div class="pb-4">
                <FormLabel html-for="date_format">
                  {{ t("views.profile.fields.settings.date_format") }}
                </FormLabel>
                <VeeField
                  v-slot="{ field }"
                  v-model="userContext.settings.date_format"
                  name="date_format"
                  rules="required"
                  :label="t('views.profile.fields.settings.date_format')"
                >
                  <FormSelect
                    id="date_format"
                    name="date_format"
                    v-bind="field"
                  >
                    <option value="yyyy_MM_dd">
                      {{ formatDate(new Date().toString(), "YYYY-MM-DD") }}
                    </option>
                    <option value="dd_MMM_yyyy">
                      {{ formatDate(new Date().toString(), "DD-MMM-YYYY") }}
                    </option>
                  </FormSelect>
                </VeeField>
              </div>

              <div class="pb-4">
                <FormLabel html-for="time_format">
                  {{ t("views.profile.fields.settings.time_format") }}
                </FormLabel>
                <VeeField
                  v-slot="{ field }"
                  v-model="userContext.settings.time_format"
                  name="time_format"
                  rules="required"
                  :label="t('views.profile.fields.settings.time_format')"
                >
                  <FormSelect
                    id="time_format"
                    name="time_format"
                    v-bind="field"
                  >
                    <option value="hh_mm_ss">
                      {{ formatDate(new Date().toString(), "HH:mm:ss") }}
                    </option>
                    <option value="h_m_A">
                      {{ formatDate(new Date().toString(), "H:m A") }}
                    </option>
                  </FormSelect>
                </VeeField>
              </div>
              <div class="flex gap-4">
                <Button variant="primary" class="w-28 shadow-md">
                  {{ t("components.buttons.submit") }}
                </Button>
              </div>
            </div>
          </VeeForm>
        </template>

        <template #card-items-roles>
          <div class="p-5">
            <div class="pb-4">
              <div class="grid grid-cols-3 gap-2 place-items center">
                <div
                  v-for="(item, index) in roles"
                  :key="index"
                  class="flex flex-col items-center"
                >
                  <div
                    class="cursor-pointer flex flex-col items-center justify-center"
                    @click="handleChangeRole(index)"
                  >
                    <img alt="" :src="item.images" width="100" height="100" />
                    <div
                      v-if="item.active"
                      class="grid grid-cols-1 place-items-center"
                    >
                      <Check class="text-success" />
                    </div>
                    <button
                      v-else
                      class="btn btn-sm btn-secondary hover:btn-primary"
                    >
                      {{ t("components.buttons.activate") }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </TwoColumnsLayout>
      <!-- </VeeForm> -->
    </LoadingOverlay>
  </div>
</template>
