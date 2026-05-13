<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import AssetUnitService from '@/services/AssetUnitService';
import CacheService from '@/services/CacheService';
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

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();
const assetUnitService = new AssetUnitService();
const cacheService = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.asset_unit.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.asset_unit.field_groups.asset_unit_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const assetUnitForm = assetUnitService.useAssetUnitCreateForm();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

onMounted(async () => {
  emits('mode-state', ViewMode.FORM_CREATE);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  loadFromCache();
  setCompanyIdData();
});

watch(
  assetUnitForm,
  debounce((newValue): void => {
    cacheService.setLastEntity('ASSET_UNIT_CREATE', newValue.data());
  }, 500),
  { deep: true },
);

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

const setCompanyIdData = () => {
  assetUnitForm.setData({
    company_id: selectedUserLocation.value.company.id,
  });
};

const loadFromCache = () => {
  const data = cacheService.getLastEntity('ASSET_UNIT_CREATE') as Record<string, unknown> | null;
  if (!data) return;

  if (!data.code) data.code = '_AUTO_';
  if (data.description === undefined || data.description === null) data.description = '';

  assetUnitForm.setData(data);
};

const setCode = () => {
  assetUnitForm.forgetError('code');
  if (assetUnitForm.code == '_AUTO_') {
    assetUnitForm.setData({ code: '' });
  } else {
    assetUnitForm.setData({ code: '_AUTO_' });
  }
};

const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const resetForm = async () => {
  assetUnitForm.reset();
  assetUnitForm.setErrors({});
  assetUnitForm.setData({
    code: '_AUTO_',
    name: '',
    description: '',
  });
  setCompanyIdData();
};

const onSubmit = async () => {
  if (assetUnitForm.hasErrors) {
    const firstErrorKey = Object.keys(assetUnitForm.errors)[0];
    if (firstErrorKey) scrollToError(firstErrorKey);
    return;
  }

  emits('loading-state', true);
  await assetUnitForm
    .submit()
    .then(() => {
      resetForm();
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-asset-unit-list' });
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
</script>

<template>
  <form id="assetUnitForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="assetUnitForm.company_id" />
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 sm:col-span-4">
              <FormLabel :class="{ 'text-danger': assetUnitForm.invalid('code') }">
                {{ t('views.asset_unit.fields.code') }}
              </FormLabel>
              <FormInputCode
                id="code"
                v-model="assetUnitForm.code"
                :class="{ 'border-danger': assetUnitForm.invalid('code') }"
                :placeholder="t('views.asset_unit.fields.code')"
                @set-auto="setCode"
                @change="assetUnitForm.validate('code')"
              />
              <FormErrorMessages :messages="assetUnitForm.errors.code" />
            </div>

            <div class="col-span-12 sm:col-span-8">
              <FormLabel :class="{ 'text-danger': assetUnitForm.invalid('name') }">
                {{ t('views.asset_unit.fields.name') }}
              </FormLabel>
              <FormInput
                id="name"
                v-model="assetUnitForm.name"
                type="text"
                :class="{ 'border-danger': assetUnitForm.invalid('name') }"
                :placeholder="t('views.asset_unit.fields.name')"
                @change="assetUnitForm.validate('name')"
              />
              <FormErrorMessages :messages="assetUnitForm.errors.name" />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': assetUnitForm.invalid('description') }">
                {{ t('views.asset_unit.fields.description') }}
              </FormLabel>
              <FormTextarea
                id="description"
                v-model="assetUnitForm.description"
                :class="{ 'border-danger': assetUnitForm.invalid('description') }"
                :placeholder="t('views.asset_unit.fields.description')"
                @change="assetUnitForm.validate('description')"
              />
              <FormErrorMessages :messages="assetUnitForm.errors.description" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-button>
        <div class="flex gap-4">
          <Button type="submit" href="#" variant="primary" class="w-28 shadow-md" :disabled="assetUnitForm.validating">
            <Lucide v-if="assetUnitForm.validating" icon="Loader" class="animate-spin" />
            <template v-else>{{ t('components.buttons.submit') }}</template>
          </Button>
          <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md" @click="resetForm">
            {{ t('components.buttons.reset') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>
</template>
