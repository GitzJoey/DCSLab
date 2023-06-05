<script setup lang="ts">
//#region Imports
import { onMounted, computed } from "vue";
import { useI18n } from "vue-i18n";
import {
  FormInput,
  FormLabel,
  FormTextarea,
  FormSelect,
} from "../../base-components/Form";
import { ThreeColsLayout } from "../../base-components/FormLayout";
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
onMounted(() => {});
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
    <!-- Begin Profile Menu -->
    <div
      class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse"
    >
      <div class="intro-y box mt-5 lg:mt-0">
        <div class="relative flex items-center p-5">
          <div class="ml-4 mr-auto">
            <div class="font-medium text-base">
              {{ userContext.name }}
            </div>

          </div>
        </div>
        <div class="p-5 border-t border-slate-200/60 dark:border-darkmode-400">
          <a href="" class="flex items-center text-primary font-medium">
            <i data-lucide="activity" class="w-4 h-4 mr-2"></i>
            User Profile
          </a>
          <a class="flex items-center mt-5" href="">
            <i data-lucide="box" class="w-4 h-4 mr-2"></i> Account Settings
          </a>
          <a class="flex items-center mt-5" href="">
            <i data-lucide="lock" class="w-4 h-4 mr-2"></i> Change Password
          </a>
          <a class="flex items-center mt-5" href="">
            <i data-lucide="settings" class="w-4 h-4 mr-2"></i> User Settings
          </a>
        </div>
      </div>
    </div>
    <!-- End Profile Menu -->
    <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
      <div class="grid grid-cols-12 gap-6">
        <!-- Begin Personal Informations -->
        <div class="intro-y box col-span-12 2xl:col-span-12">
          <div
            class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400"
          >
            <h2 class="font-medium text-base mr-auto">User Profile</h2>
          </div>
          <div class="p-5">
            <!-- Start Body dari Card -->
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
        </div>
        <!-- End Personal Informations -->

        <!-- Begin Account Settings -->
        <div class="intro-y box col-span-12 2xl:col-span-12">
          <div
            class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400"
          >
            <h2 class="font-medium text-base mr-auto">Personal Informations</h2>
          </div>
          <div class="p-5">
            <!-- Body dari Card -->
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
        </div>
        <!-- End Account Settings -->


        <!-- Begin Change  Password -->
        <div class="intro-y box col-span-12 2xl:col-span-12">
          <div
            class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400"
          >
            <h2 class="font-medium text-base mr-auto">Change Password</h2>
          </div>
          <div class="p-5">
            <!-- Body dari Card -->            
          </div>
        </div>
        <!-- End Change  Password -->

        <!-- Begin User Setting -->
        <div class="intro-y box col-span-12 2xl:col-span-12">
          <div
            class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400"
          >
            <h2 class="font-medium text-base mr-auto">User Setting</h2>
          </div>
          <div class="p-5">
            <!--Start Body dari Card -->            
            <!--End Body dari Card -->            
          </div>
        </div>
        <!-- End User Setting -->
      </div>
    </div>
  </div>


</template>
