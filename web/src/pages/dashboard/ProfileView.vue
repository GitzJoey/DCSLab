<script setup lang="ts">
//#region Imports
import { onMounted, computed } from "vue";
import { useI18n } from "vue-i18n";
import {
  FormInput,
  FormLabel,
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
onMounted(() => {

});
//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>

<template>
  <div class="flex items-center mt-8 intro-y">
    <h2 class="mr-auto text-lg font-medium">{{ t('views.profile.title') }}</h2>
  </div>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12 intro-y p-5 box">
      <div class="grid grid-cols-12">
        <div class="hidden block md:block lg:block md:col-span-4 lg:col-span-4">{{ t('views.profile.field_groups.personal_information') }}</div>
        <div class="col-span-12 md:col-span-8 lg:col-span-8">
          <div class="pb-4">
            <FormLabel htmlFor="name">{{ t('views.profile.fields.name') }}</FormLabel>
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
            <FormLabel htmlFor="email">{{ t('views.profile.fields.email') }}</FormLabel>
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
          <div class="pb-4">
            <VeeField v-slot="{ field }" name="first_name" rules="required" :label="t('views.profile.fields.first_name')">
              <FormLabel htmlFor="firstName" class="flex flex-col w-full sm:flex-row">
                {{ t('views.profile.fields.first_name') }}
              </FormLabel>
              <FormInput
                id="firstName"
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
            <FormLabel htmlFor="lastName">{{ t('views.profile.fields.last_name') }}</FormLabel>
            <FormInput
              id="lastName"
              v-model="userContext.profile.last_name"
              name="last_name"
              type="text"
              class="w-full"
              :placeholder="t('views.profile.fields.last_name')"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>