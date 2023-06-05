<script setup lang="ts">
//#region Imports
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import {
  FormInput,
  FormLabel,
  FormTextarea,
  FormSelect,
} from "../../base-components/Form";
import { useUserContextStore } from "../../stores/user-context";
//#endregion

//#region Declarations
const { t } = useI18n();
const userContextStore = useUserContextStore();
//#endregion

//#region Data - Pinia
const userContext = computed(() => userContextStore.getUserContext);
//#endregion

//#region Data - UI
//#endregion

//#region Data - Views
//#endregion

//#region onMounted
//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>

<template>
  <div class="flex items-center mt-8 intro-y">
    <h2 class="mr-auto text-xl font-medium">{{ t("views.profile.title") }}</h2>
  </div>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12 intro-y p-5 box">
      <div class="grid grid-cols-12">
        <div class="hidden block md:block lg:block md:col-span-4 lg:col-span-4">
          <span class="text-lg">
            {{ t("views.profile.field_groups.user_profile") }}
          </span>
        </div>
        <div class="col-span-12 md:col-span-8 lg:col-span-8">
          <div class="pb-4">
            <FormLabel html-for="name">{{
              t("views.profile.fields.name")
            }}</FormLabel>
            <FormInput
              id="name"
              v-model="userContext.name"
              name="name"
              type="text"
              class="w-full"
              :placeholder="t('views.profile.fields.name')"
              readonly
            />
          </div>
          <div class="pb-4">
            <FormLabel html-for="email">{{
              t("views.profile.fields.email")
            }}</FormLabel>
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
        </div>
      </div>
      <div class="grid grid-cols-12">
        <div class="hidden block md:block lg:block md:col-span-4 lg:col-span-4">
          <span class="text-lg">{{
            t("views.profile.field_groups.personal_information")
          }}</span>
        </div>
        <div class="col-span-12 md:col-span-8 lg:col-span-8">
          <div class="pb-4">
            <VeeField
              v-slot="{ field }"
              name="first_name"
              rules="required"
              :label="t('views.profile.fields.first_name')"
            >
              <FormLabel
                html-for="first_name"
                class="flex flex-col w-full sm:flex-row"
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
                v-bind="field"
              />
            </VeeField>
          </div>
          <div class="pb-4">
            <FormLabel html-for="last_name">{{
              t("views.profile.fields.last_name")
            }}</FormLabel>
            <FormInput
              id="last_name"
              v-model="userContext.profile.last_name"
              name="last_name"
              type="text"
              class="w-full"
              :placeholder="t('views.profile.fields.last_name')"
            />
          </div>
          <div class="pb-4">
            <FormLabel html-for="address">{{
              t("views.profile.fields.address")
            }}</FormLabel>
            <FormTextarea
              id="address"
              v-model="userContext.profile.address"
              name="address"
              class="w-full"
              rows="5"
              :placeholder="t('views.profile.fields.address')"
            />
          </div>
          <div class="flex gap-2">
            <div class="pb-4 w-full">
              <FormLabel html-for="city">{{
                t("views.profile.fields.city")
              }}</FormLabel>
              <FormInput
                id="address"
                v-model="userContext.profile.city"
                name="city"
                type="text"
                class="w-full"
                :placeholder="t('views.profile.fields.city')"
              />
            </div>
            <div class="pb-4">
              <FormLabel html-for="postal_code">{{
                t("views.profile.fields.postal_code")
              }}</FormLabel>
              <FormInput
                id="postal_code"
                v-model="userContext.profile.postal_code"
                name="postal_code"
                type="text"
                class="w-full"
                :placeholder="t('views.profile.fields.postal_code')"
              />
            </div>
          </div>
          <div class="pb-4">
            <FormLabel html-for="country">{{
              t("views.profile.fields.country")
            }}</FormLabel>
            <FormSelect
              id="country"
              v-model="userContext.profile.country"
              name="country"
              class="w-full"
              :placeholder="t('views.profile.fields.country')"
            >
              <option>Singapore</option>
              <option>Indonesia</option>
            </FormSelect>
          </div>
          <div class="pb-4">
            <FormLabel html-for="tax_id">{{
              t("views.profile.fields.tax_id")
            }}</FormLabel>
            <FormInput
              id="tax_id"
              v-model="userContext.profile.tax_id"
              name="tax_id"
              type="text"
              class="w-full"
              :placeholder="t('views.profile.fields.tax_id')"
            />
          </div>
          <div class="pb-4">
            <FormLabel html-for="ic_num">{{
              t("views.profile.fields.ic_num")
            }}</FormLabel>
            <FormInput
              id="ic_num"
              v-model="userContext.profile.ic_num"
              name="ic_num"
              type="text"
              class="w-full"
              :placeholder="t('views.profile.fields.ic_num')"
            />
          </div>
          <div class="pb-4">
            <FormLabel html-for="remarks">{{
              t("views.profile.fields.remarks")
            }}</FormLabel>
            <FormTextarea
              id="remarks"
              v-model="userContext.profile.remarks"
              name="remarks"
              class="w-full"
              rows="5"
              :placeholder="t('views.profile.fields.remarks')"
            />
          </div>
        </div>
      </div>
      <hr class="py-2" />
      <div class="grid grid-cols-12">
        <div class="hidden block md:block lg:block md:col-span-4 lg:col-span-4">
          {{ t("views.profile.field_groups.account_settings") }}
        </div>
        <div class="col-span-12 md:col-span-8 lg:col-span-8"></div>
      </div>
    </div>
  </div>
</template>
