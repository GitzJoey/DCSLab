<script setup lang="ts">
//#region Imports
import { onMounted, ref, computed } from "vue";
import router from "vue-router";
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
const loading = ref(false);
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
        <div class="col-span-4">{{ t('views.profile.field_groups.personal_information') }}</div>
        <div class="col-span-8">
          <div class="pb-4">
            <FormLabel htmlFor="name">{{ t('views.profile.fields.name') }}</FormLabel>
            <FormInput
              id="name"
              name="name"
              type="text"
              class="w-full"
              v-model="userContext.name"
              :placeholder="t('views.profile.fields.name')"
              readonly
            />
          </div>
          <div class="pb-4">
            <FormLabel htmlFor="email">{{ t('views.profile.fields.email') }}</FormLabel>
            <FormInput
              id="email"
              name="email"
              type="text"
              class="w-full"
              v-model="userContext.email"
              :placeholder="t('views.profile.fields.email')"
              readonly
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>