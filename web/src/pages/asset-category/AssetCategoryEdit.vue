<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import AssetCategoryService from '@/services/AssetCategoryService';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import { FormInput, FormLabel, FormErrorMessages, FormInputCode, FormTextarea } from '@/components/Base/Form';
import { CardState } from '@/types/enums/CardState';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const selectedUserLocationStore = useSelectedUserLocationStore();
const assetCategoryService = new AssetCategoryService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.asset_category.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.asset_category.field_groups.asset_category_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const assetCategoryForm = assetCategoryService.useAssetCategoryEditForm(route.params.ulid.toString());
const companyCode = ref<string>('');
const companyName = ref<string>('');
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
// #endregion

// #region Vue Core
onMounted(async () => {
  emits('mode-state', ViewMode.FORM_EDIT);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  await loadData();
});

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};
// #endregion

// #region Methods - AssetCategory
const loadData = async () => {
  emits('loading-state', true);
  const result = await assetCategoryService.read(route.params.ulid.toString());
  emits('loading-state', false);

  if (result.success && result.data) {
    companyCode.value = result.data.company?.code ?? '';
    companyName.value = result.data.company?.name ?? '';

    assetCategoryForm.setData({
      company_id: result.data.company?.id ?? '',
      code: result.data.code,
      name: result.data.name,
      estimated_useful_life_months: result.data.estimated_useful_life_months ?? '',
      remarks: result.data.remarks ?? '',
    } as any);
  } else {
    router.push({ name: 'side-menu-asset-category-list' });
  }
};

const setCode = () => {
  assetCategoryForm.forgetError('code');
  if (assetCategoryForm.code == '_AUTO_') {
    assetCategoryForm.setData({ code: '' });
  } else {
    assetCategoryForm.setData({ code: '_AUTO_' });
  }
};
// #endregion

// #region Actions
const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const resetForm = async () => {
  assetCategoryForm.reset();
  assetCategoryForm.setErrors({});
  await loadData();
};

const onSubmit = async () => {
  if (assetCategoryForm.hasErrors) {
    const firstErrorKey = Object.keys(assetCategoryForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  emits('loading-state', true);
  await assetCategoryForm
    .submit()
    .then(() => {
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-asset-category-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const showAlertPlaceholder = (
  pAlertType: 'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark',
  pTitle: string,
  pAlertList: Record<string, Array<string>> | null,
) => {
  const ap: AlertPlaceholderProps = {
    alertType: pAlertType,
    title: pTitle,
    alertList: pAlertList,
  };

  emits('show-alertplaceholder', ap);
};
// #endregion
</script>

<template>
  <form id="assetCategoryForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ companyCode }}
            <br />
            {{ companyName }}
          </FormLabel>
          <FormInput type="hidden" v-model="assetCategoryForm.company_id" />
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 sm:col-span-4">
              <FormLabel
                :class="{
                  'text-danger': assetCategoryForm.invalid('code'),
                }"
              >
                {{ t('views.asset_category.fields.code') }}
              </FormLabel>
              <FormInputCode
                id="code"
                v-model="assetCategoryForm.code"
                :class="{
                  'border-danger': assetCategoryForm.invalid('code'),
                }"
                :placeholder="t('views.asset_category.fields.code')"
                @set-auto="setCode"
                @change="assetCategoryForm.validate('code')"
              />
              <FormErrorMessages :messages="assetCategoryForm.errors.code" />
            </div>

            <div class="col-span-12 sm:col-span-5">
              <FormLabel
                :class="{
                  'text-danger': assetCategoryForm.invalid('name'),
                }"
              >
                {{ t('views.asset_category.fields.name') }}
              </FormLabel>
              <FormInput
                id="name"
                v-model="assetCategoryForm.name"
                type="text"
                :class="{
                  'border-danger': assetCategoryForm.invalid('name'),
                }"
                :placeholder="t('views.asset_category.fields.name')"
                @change="assetCategoryForm.validate('name')"
              />
              <FormErrorMessages :messages="assetCategoryForm.errors.name" />
            </div>

            <div class="col-span-12 sm:col-span-3">
              <FormLabel
                :class="{
                  'text-danger': assetCategoryForm.invalid('estimated_useful_life_months'),
                }"
              >
                {{ t('views.asset_category.fields.estimated_useful_life_months') }}
              </FormLabel>
              <FormInput
                id="estimated_useful_life_months"
                v-model.number="assetCategoryForm.estimated_useful_life_months"
                type="number"
                min="1"
                :class="{
                  'border-danger': assetCategoryForm.invalid('estimated_useful_life_months'),
                }"
                :placeholder="t('views.asset_category.fields.estimated_useful_life_months')"
                @change="assetCategoryForm.validate('estimated_useful_life_months')"
              />
              <FormErrorMessages :messages="assetCategoryForm.errors.estimated_useful_life_months" />
            </div>

            <div class="col-span-12">
              <FormLabel
                :class="{
                  'text-danger': assetCategoryForm.invalid('remarks'),
                }"
              >
                {{ t('views.asset_category.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                id="remarks"
                v-model="assetCategoryForm.remarks"
                :class="{
                  'border-danger': assetCategoryForm.invalid('remarks'),
                }"
                :placeholder="t('views.asset_category.fields.remarks')"
                @change="assetCategoryForm.validate('remarks')"
              />
              <FormErrorMessages :messages="assetCategoryForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-button>
        <div class="flex gap-4">
          <Button
            type="submit"
            href="#"
            variant="primary"
            class="w-28 shadow-md"
            :disabled="assetCategoryForm.validating || assetCategoryForm.hasErrors"
          >
            <Lucide v-if="assetCategoryForm.validating" icon="Loader" class="animate-spin" />
            <template v-else>
              {{ t('components.buttons.submit') }}
            </template>
          </Button>
          <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md" @click="resetForm">
            {{ t('components.buttons.reset') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>
</template>
