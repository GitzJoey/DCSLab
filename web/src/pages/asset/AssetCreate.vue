<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import AssetService from '@/services/AssetService';
import AssetCategoryService from '@/services/AssetCategoryService';
import AssetUnitService from '@/services/AssetUnitService';
import DashboardService from '@/services/DashboardService';
import CacheService from '@/services/CacheService';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import {
  FormInput,
  FormLabel,
  FormErrorMessages,
  FormInputCode,
  FormTextarea,
  FormSelect,
} from '@/components/Base/Form';
import { CardState } from '@/types/enums/CardState';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { DropDownOption } from '@/types/models/DropDownOption';

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();
const assetService = new AssetService();
const assetCategoryService = new AssetCategoryService();
const assetUnitService = new AssetUnitService();
const dashboardService = new DashboardService();
const cacheService = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.asset.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.asset.field_groups.asset_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const assetForm = assetService.useAssetCreateForm();
const assetCategoryDDL = ref<Array<DropDownOption>>([]);
const assetUnitDDL = ref<Array<DropDownOption>>([]);
const statusDDL = ref<Array<DropDownOption>>([]);

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
  await Promise.all([loadAssetCategories(), loadAssetUnits(), loadStatuses()]);
});

watch(
  assetForm,
  debounce((newValue): void => {
    cacheService.setLastEntity('ASSET_CREATE', newValue.data());
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
  assetForm.setData({
    company_id: selectedUserLocation.value.company.id,
  });
};

const loadAssetCategories = async () => {
  const result = await assetCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search: '',
    refresh: false,
    limit: 50,
  });

  if (result.success && result.data) {
    assetCategoryDDL.value = result.data.data.map((item) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadAssetUnits = async () => {
  const result = await assetUnitService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search: '',
    refresh: false,
    limit: 50,
  });

  if (result.success && result.data) {
    assetUnitDDL.value = result.data.data.map((item) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadStatuses = async () => {
  const result = await dashboardService.getStatusDDL(false);
  if (result) {
    statusDDL.value = result;
  }
};

const loadFromCache = () => {
  const data = cacheService.getLastEntity('ASSET_CREATE') as Record<string, unknown> | null;
  if (!data) return;

  if (!data.code) data.code = '_AUTO_';
  if (!data.status) data.status = 1;
  if (data.remarks === undefined || data.remarks === null) data.remarks = '';

  assetForm.setData(data);
};

const setCode = () => {
  assetForm.forgetError('code');
  if (assetForm.code == '_AUTO_') {
    assetForm.setData({ code: '' });
  } else {
    assetForm.setData({ code: '_AUTO_' });
  }
};

const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const resetForm = async () => {
  assetForm.reset();
  assetForm.setErrors({});
  assetForm.setData({
    asset_category_id: '',
    code: '_AUTO_',
    name: '',
    asset_unit_id: '',
    status: 1,
    remarks: '',
  });
  setCompanyIdData();
};

const onSubmit = async () => {
  if (assetForm.hasErrors) {
    const firstErrorKey = Object.keys(assetForm.errors)[0];
    if (firstErrorKey) scrollToError(firstErrorKey);
    return;
  }

  emits('loading-state', true);
  await assetForm
    .submit()
    .then(() => {
      resetForm();
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-asset-list' });
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
  <form id="assetForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="assetForm.company_id" />
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 sm:col-span-6">
              <FormLabel :class="{ 'text-danger': assetForm.invalid('asset_category_id') }">
                {{ t('views.asset.fields.asset_category_id') }}
              </FormLabel>
              <FormSelect
                id="asset_category_id"
                v-model="assetForm.asset_category_id"
                :class="{ 'border-danger': assetForm.invalid('asset_category_id') }"
                @change="assetForm.validate('asset_category_id')"
              >
                <option value="" disabled>{{ t('components.dropdown.placeholder') }}</option>
                <option v-for="item in assetCategoryDDL" :key="item.code" :value="item.code">
                  {{ item.name }}
                </option>
              </FormSelect>
              <FormErrorMessages :messages="assetForm.errors.asset_category_id" />
            </div>

            <div class="col-span-12 sm:col-span-3">
              <FormLabel :class="{ 'text-danger': assetForm.invalid('code') }">
                {{ t('views.asset.fields.code') }}
              </FormLabel>
              <FormInputCode
                id="code"
                v-model="assetForm.code"
                :class="{ 'border-danger': assetForm.invalid('code') }"
                :placeholder="t('views.asset.fields.code')"
                @set-auto="setCode"
                @change="assetForm.validate('code')"
              />
              <FormErrorMessages :messages="assetForm.errors.code" />
            </div>

            <div class="col-span-12 sm:col-span-5">
              <FormLabel :class="{ 'text-danger': assetForm.invalid('name') }">
                {{ t('views.asset.fields.name') }}
              </FormLabel>
              <FormInput
                id="name"
                v-model="assetForm.name"
                type="text"
                :class="{ 'border-danger': assetForm.invalid('name') }"
                :placeholder="t('views.asset.fields.name')"
                @change="assetForm.validate('name')"
              />
              <FormErrorMessages :messages="assetForm.errors.name" />
            </div>

            <div class="col-span-12 sm:col-span-6">
              <FormLabel :class="{ 'text-danger': assetForm.invalid('asset_unit_id') }">
                {{ t('views.asset.fields.asset_unit_id') }}
              </FormLabel>
              <FormSelect
                id="asset_unit_id"
                v-model="assetForm.asset_unit_id"
                :class="{ 'border-danger': assetForm.invalid('asset_unit_id') }"
                @change="assetForm.validate('asset_unit_id')"
              >
                <option value="" disabled>{{ t('components.dropdown.placeholder') }}</option>
                <option v-for="item in assetUnitDDL" :key="item.code" :value="item.code">
                  {{ item.name }}
                </option>
              </FormSelect>
              <FormErrorMessages :messages="assetForm.errors.asset_unit_id" />
            </div>

            <div class="col-span-12 sm:col-span-4">
              <FormLabel :class="{ 'text-danger': assetForm.invalid('status') }">
                {{ t('views.asset.fields.status') }}
              </FormLabel>
              <FormSelect
                id="status"
                v-model="assetForm.status"
                :class="{ 'border-danger': assetForm.invalid('status') }"
                @change="assetForm.validate('status')"
              >
                <option value="" disabled>{{ t('components.dropdown.placeholder') }}</option>
                <option v-for="item in statusDDL" :key="item.code" :value="item.code">
                  {{ item.name }}
                </option>
              </FormSelect>
              <FormErrorMessages :messages="assetForm.errors.status" />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': assetForm.invalid('remarks') }">
                {{ t('views.asset.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                id="remarks"
                v-model="assetForm.remarks"
                :class="{ 'border-danger': assetForm.invalid('remarks') }"
                :placeholder="t('views.asset.fields.remarks')"
                @change="assetForm.validate('remarks')"
              />
              <FormErrorMessages :messages="assetForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-button>
        <div class="flex gap-4">
          <Button type="submit" href="#" variant="primary" class="w-28 shadow-md" :disabled="assetForm.validating">
            <Lucide v-if="assetForm.validating" icon="Loader" class="animate-spin" />
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
