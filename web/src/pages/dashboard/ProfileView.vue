<script setup lang="ts">
//#region Imports
import { useI18n } from "vue-i18n";
import {
  FormInput,
  FormLabel,
  FormTextarea,
  FormSelect,
} from "../../base-components/Form";
import { ThreeColsLayout } from "../../base-components/FormLayout";
import { useUserContextStore } from "../../stores/user-context";
import Card from "../../components/Card";
import { PropType, computed, onMounted, ref, toRef, watch } from "vue";

//#endregion

//#region Declarations
const { t } = useI18n();
const userContextStore = useUserContextStore();
//#endregion

//#region Data - Pinia
const userContext = computed(() => userContextStore.getUserContext);
//#endregion

//#region Data - UI
const tabs=ref(['User Profile', 'Account Settings', 'Change Password', 'User Setting'])
//#endregion

//#region Data - Views
//#endregion

//#region onMounted
onMounted(() => {});
//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>

<template>
  <Card :cards="tabs"  >
    <template #title>
      <h2 class="mr-auto text-xl font-medium">{{ t("views.profile.title") }}</h2>
    </template>

    <template #side-menu-title >
      <div class="relative flex items-center p-5">
          <div class="ml-4 mr-auto">
            <div class="font-medium text-base">
              {{ userContext.name }}
            </div>

          </div>
        </div>
    </template>

    <template #side-menu-link="linkProps" >  
      {{ linkProps.link }}
    </template>

    <template #card-items-0="cardProps" >
          <div
            class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400"
          >
            <h2 class="font-medium text-base mr-auto">{{ cardProps.card }}</h2>
          </div>

          <div class="p-5">
            <div class="pb-4">
              <FormLabel htmlFor="name">
                {{ t("views.profile.fields.name") }}
              </FormLabel>
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
            <FormLabel htmlFor="email">{{
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
    </template>

    <template #card-items-1="cardProps" >
      
      <div
            class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400"
          >
            <h2 class="font-medium text-base mr-auto">{{ cardProps.card }}</h2>
          </div>
          <div class="p-5">
            <div class="pb-4">
            <VeeField
              v-slot="{ field }"
              name="first_name"
              rules="required"
              :label="t('views.profile.fields.first_name')"
            >
              <FormLabel
                htmlFor="first_name"
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
            <FormLabel htmlFor="last_name">{{
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
            <FormLabel htmlFor="address">{{
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
              <FormLabel htmlFor="city">{{
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
              <FormLabel htmlFor="postal_code">{{
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
            <FormLabel htmlFor="country">{{
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
            <FormLabel htmlFor="tax_id">{{
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
            <FormLabel htmlFor="ic_num">{{
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
            <FormLabel htmlFor="remarks">{{
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
      
    </template>

    <template #card-items-2="cardProps" >
      <div
            class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400"
          >
            <h2 class="font-medium text-base mr-auto">{{ cardProps.card }}</h2>
          </div>
          <div class="p-5">
          </div>
    </template>

    <template #card-items-3="cardProps" >
      <div  
            class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400"
          >
            <h2 class="font-medium text-base mr-auto">{{ cardProps.card }}</h2>
          </div>
          <div class="p-5">
          </div>
    </template>
  </Card>

</template>
