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
import { TitleLayout, TwoColumnsLayout, } from "../../base-components/Form/FormLayout";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import AlertPlaceholder from "../../base-components/AlertPlaceholder";
//#endregion

//#region Declarations
const { t } = useI18n();
const userContextStore = useUserContextStore();
//#endregion

//#region Data - Pinia
const userContext = computed(() => userContextStore.getUserContext);
//#endregion

//#region Data - UI
const loading = ref<boolean>(false);
const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: "User Profile", active: true, },
  { title: "Account Settings", active: false, },
  { title: "Change Password", active: false, },
  { title: "User Setting", active: false, },
]);
//#endregion

//#region Data - Views
const userProfileForm = ref();
//#endregion

//#region onMounted
//#endregion

//#region Computed
//#endregion

// Region Method
const handleExpandCard = (index: number) => {
  cards.value[index].active = !cards.value[index].active;
}

const onSubmit = async () => {
  loading.value = true;

  loading.value = false;
};
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

      <VeeForm id="profileForm" v-slot="{ errors }" @submit="onSubmit">
        <AlertPlaceholder :messages="errors" />
        <TwoColumnsLayout :cards="cards" :show-side-tab="false" @handleExpandCard="handleExpandCard">
          <template #side-menu-title>
            {{ userContext.name }}
          </template>
          <template #side-menu-link="linkProps">
            {{ t(linkProps.link.title) }}
          </template>

          <template #card-items-0>
            <div class="p-5">
              <div class="pb-4">
                <FormLabel html-for="name">
                  {{ t("views.profile.fields.name") }}
                </FormLabel>
                <FormInput id="name" v-model="userContext.name" name="name" type="text" class="w-full"
                  :placeholder="t('views.profile.fields.name')" readonly />
              </div>
              <div class="pb-4">
                <FormLabel html-for="email">
                  {{ t("views.profile.fields.email") }}
                </FormLabel>
                <FormInput id="email" v-model="userContext.email" name="email" type="text" class="w-full"
                  :placeholder="t('views.profile.fields.email')" readonly />
              </div>
            </div>
          </template>

          <template #card-items-1>
            <div class="p-5">
              <div class="pb-4">
                <VeeField v-slot="{ field }" name="first_name" rules="required"
                  :label="t('views.profile.fields.first_name')">
                  <FormLabel html-for="first_name" class="flex flex-col w-full sm:flex-row">
                    {{ t("views.profile.fields.first_name") }}
                  </FormLabel>
                  <FormInput id="first_name" v-model="userContext.profile.first_name" name="first_name" type="text"
                    class="w-full" :placeholder="t('views.profile.fields.first_name')" v-bind="field" />
                </VeeField>
              </div>
              <div class="pb-4">
                <FormLabel html-for="last_name">
                  {{ t("views.profile.fields.last_name") }}
                </FormLabel>
                <FormInput id="last_name" v-model="userContext.profile.last_name" name="last_name" type="text"
                  class="w-full" :placeholder="t('views.profile.fields.last_name')" />
              </div>
              <div class="pb-4">
                <FormLabel html-for="address">
                  {{ t("views.profile.fields.address") }}
                </FormLabel>
                <FormTextarea id="address" v-model="userContext.profile.address" name="address" class="w-full" rows="5"
                  :placeholder="t('views.profile.fields.address')" />
              </div>
              <div class="flex gap-2">
                <div class="pb-4 w-full">
                  <FormLabel html-for="city">
                    {{ t("views.profile.fields.city") }}</FormLabel>
                  <FormInput id="address" v-model="userContext.profile.city" name="city" type="text" class="w-full"
                    :placeholder="t('views.profile.fields.city')" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="postal_code">
                    {{ t("views.profile.fields.postal_code") }}
                  </FormLabel>
                  <FormInput id="postal_code" v-model="userContext.profile.postal_code" name="postal_code" type="text"
                    class="w-full" :placeholder="t('views.profile.fields.postal_code')" />
                </div>
              </div>
              <div class="pb-4">
                <FormLabel html-for="country">
                  {{ t("views.profile.fields.country") }}
                </FormLabel>
                <FormSelect id="country" v-model="userContext.profile.country" name="country" class="w-full"
                  :placeholder="t('views.profile.fields.country')">
                  <option>Singapore</option>
                  <option>Indonesia</option>
                </FormSelect>
              </div>
              <div class="pb-4">
                <FormLabel html-for="tax_id">
                  {{ t("views.profile.fields.tax_id") }}
                </FormLabel>
                <FormInput id="tax_id" v-model="userContext.profile.tax_id" name="tax_id" type="text" class="w-full"
                  :placeholder="t('views.profile.fields.tax_id')" />
              </div>
              <div class="pb-4">
                <FormLabel html-for="ic_num">
                  {{ t("views.profile.fields.ic_num") }}
                </FormLabel>
                <FormInput id="ic_num" v-model="userContext.profile.ic_num" name="ic_num" type="text" class="w-full"
                  :placeholder="t('views.profile.fields.ic_num')" />
              </div>
              <div class="pb-4">
                <FormLabel html-for="remarks">
                  {{ t("views.profile.fields.remarks") }}
                </FormLabel>
                <FormTextarea id="remarks" v-model="userContext.profile.remarks" name="remarks" class="w-full" rows="5"
                  :placeholder="t('views.profile.fields.remarks')" />
              </div>
            </div>
          </template>

          <template #card-items-2>
            <div class="p-5">
            </div>
          </template>

          <template #card-items-3>
            <div class="p-5">
            </div>
          </template>
        </TwoColumnsLayout>
      </VeeForm>
    </LoadingOverlay>
  </div>
</template>
