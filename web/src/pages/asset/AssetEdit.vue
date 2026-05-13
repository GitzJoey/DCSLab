<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import AssetService from '@/services/AssetService';
import AssetCategoryService from '@/services/AssetCategoryService';
import AssetUnitService from '@/services/AssetUnitService';
import DashboardService from '@/services/DashboardService';
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
const route = useRoute();
const selectedUserLocationStore = useSelectedUserLocationStore();
const assetService = new AssetService();
const assetCategoryService = new AssetCategoryService();
const assetUnitService = new AssetUnitService();
const dashboardService = new DashboardService();

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

const assetForm = assetService.useAssetEditForm(route.params.ulid.toString());
const companyCode = ref<string>('');
const companyName = ref<string>('');
const assetCategoryDDL = ref<Array<DropDownOption>>([]);
const assetUnitDDL = ref<Array<DropDownOption>>([]);
const statusDDL = ref<Array<DropDownOption>>([]);

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);

onMounted(async () => {
  emits('mode-state', ViewMode.FORM_EDIT);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  await Promise.all([loadAssetCategories(), loadAssetUnits(), loadStatuses()]);
  await loadData();
});

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

const loadAssetCategories = async () => {
  const result = await assetCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocationStore.selectedUserLocation.company.id,
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
    company_id: selectedUserLocationStore.selectedUserLocation.company.id,
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

const loadData = async () => {
  emits('loading-state', true);
  const result = await assetService.read(route.params.ulid.toString());
  emits('loading-state', false);

  if (result.success && result.data) {
    companyCode.value = result.data.company?.code ?? '';
    companyName.value = result.data.company?.name ?? '';

    assetForm.setData({
      company_id: result.data.company?.id ?? '',
      asset_category_id: result.data.asset_category?.id ?? '',
      code: result.data.code,
      name: result.data.name,
      asset_unit_id: result.data.asset_unit?.id ?? '',
      status: result.data.status,
      remarks: result.data.remarks ?? '',
    } as any);
  } else {
    router.push({ name: 'side-menu-asset-list' });
  }
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
  await loadData();
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
            {{ companyCode }}
            <br />
            {{ companyName }}
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
          <Button
            type="submit"
            href="#"
            variant="primary"
            class="w-28 shadow-md"
            :disabled="assetForm.validating || assetForm.hasErrors"
          >
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
