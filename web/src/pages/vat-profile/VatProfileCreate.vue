<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import VatProfileService from '@/services/VatProfileService';
import CacheService from '@/services/CacheService';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import {
  FormInput,
  FormLabel,
  FormInputCode,
  FormInputCurrency,
  FormErrorMessages,
  FormTextarea,
  FormSwitch,
} from '@/components/Base/Form';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import { CardState } from '@/types/enums/CardState';
import Button from '@/components/Base/Button';
import { ViewMode } from '@/types/enums/ViewMode';
import Lucide from '@/components/Base/Lucide';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { convertErrorTypeToAlertListType } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const vatProfileService = new VatProfileService();
const cacheServices = new CacheService();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.vat_profile.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.vat_profile.field_groups.vat_profile_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const vatProfileForm = vatProfileService.useVatProfileCreateForm();

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

onMounted(async () => {
  emits('mode-state', ViewMode.FORM_CREATE);
  loadFromCache();

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  setCompanyIdData();
});

const setCompanyIdData = () => {
  vatProfileForm.setData({
    company_id: selectedUserLocation.value.company.id,
  });
};

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const onSubmit = async () => {
  if (vatProfileForm.hasErrors) {
    scrollToError(Object.keys(vatProfileForm.errors)[0]);
  }

  emits('loading-state', true);
  await vatProfileForm
    .submit()
    .then(() => {
      resetForm();
      emits('update-profile');
      router.push({ name: 'side-menu-product-vat-profile-list' });
    })
    .catch((error: any) => {
      const errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error as Error);
      showAlertPlaceholder('danger', '', errorList);
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = () => {
  vatProfileForm.reset();
  vatProfileForm.setErrors({});
  setCompanyIdData();
};

const setCode = () => {
  vatProfileForm.forgetError('code');
  if (vatProfileForm.code == '_AUTO_') {
    vatProfileForm.setData({ code: '' });
  } else {
    vatProfileForm.setData({ code: '_AUTO_' });
  }
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

const loadFromCache = () => {
  const data = cacheServices.getLastEntity('VAT_PROFILE_CREATE') as Record<string, unknown>;
  if (data) {
    vatProfileForm.setData(data);
  }
};

watch(
  vatProfileForm,
  debounce((newValue): void => {
    cacheServices.setLastEntity('VAT_PROFILE_CREATE', newValue.data());
  }, 500),
  { deep: true },
);
</script>

<template>
  <form id="vatProfileForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="vatProfileForm.company_id" />
        </div>
      </template>
      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-4 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': vatProfileForm.invalid('code') }">
                {{ t('views.vat_profile.fields.code') }}
              </FormLabel>
              <FormInputCode
                id="code"
                v-model="vatProfileForm.code"
                :class="{ 'border-danger': vatProfileForm.invalid('code') }"
                :placeholder="t('views.vat_profile.fields.code')"
                @set-auto="setCode"
                @change="vatProfileForm.validate('code')"
              />
              <FormErrorMessages :messages="vatProfileForm.errors.code" />
            </div>
            <div class="col-span-12 md:col-span-8 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': vatProfileForm.invalid('name') }">
                {{ t('views.vat_profile.fields.name') }}
              </FormLabel>
              <FormInput
                id="name"
                v-model="vatProfileForm.name"
                type="text"
                :class="{ 'border-danger': vatProfileForm.invalid('name') }"
                :placeholder="t('views.vat_profile.fields.name')"
                @change="vatProfileForm.validate('name')"
              />
              <FormErrorMessages :messages="vatProfileForm.errors.name" />
            </div>
            <div class="col-span-12 md:col-span-2 lg:col-span-1">
              <FormLabel :class="{ 'text-danger': vatProfileForm.invalid('vat_rate') }">
                {{ t('views.vat_profile.fields.vat_rate') }}
              </FormLabel>
              <FormInputCurrency
                id="vat_rate"
                v-model="vatProfileForm.vat_rate"
                :class="{ 'border-danger': vatProfileForm.invalid('vat_rate') }"
                :placeholder="t('views.vat_profile.fields.vat_rate')"
                @change="vatProfileForm.validate('vat_rate')"
              />
              <FormErrorMessages :messages="vatProfileForm.errors.vat_rate" />
            </div>
            <div class="col-span-12 md:col-span-4 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': vatProfileForm.invalid('vat_base_numerator') }">
                {{ t('views.vat_profile.fields.vat_base_numerator') }}
              </FormLabel>
              <FormInput
                id="vat_base_numerator"
                v-model="vatProfileForm.vat_base_numerator"
                type="number"
                min="0"
                step="1"
                :class="{ 'border-danger': vatProfileForm.invalid('vat_base_numerator') }"
                :placeholder="t('views.vat_profile.fields.vat_base_numerator')"
                @change="vatProfileForm.validate('vat_base_numerator')"
              />
              <FormErrorMessages :messages="vatProfileForm.errors.vat_base_numerator" />
            </div>
            <div class="col-span-12 md:col-span-4 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': vatProfileForm.invalid('vat_base_denominator') }">
                {{ t('views.vat_profile.fields.vat_base_denominator') }}
              </FormLabel>
              <FormInput
                id="vat_base_denominator"
                v-model="vatProfileForm.vat_base_denominator"
                type="number"
                min="1"
                step="1"
                :class="{ 'border-danger': vatProfileForm.invalid('vat_base_denominator') }"
                :placeholder="t('views.vat_profile.fields.vat_base_denominator')"
                @change="vatProfileForm.validate('vat_base_denominator')"
              />
              <FormErrorMessages :messages="vatProfileForm.errors.vat_base_denominator" />
            </div>
            <div class="col-span-12 md:col-span-2 lg:col-span-1">
              <FormLabel :class="{ 'text-danger': vatProfileForm.invalid('is_active') }">
                {{ t('views.vat_profile.fields.is_active') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input
                  id="is_active"
                  v-model="vatProfileForm.is_active"
                  type="checkbox"
                  :class="{ 'border-danger': vatProfileForm.invalid('is_active') }"
                  @change="vatProfileForm.validate('is_active')"
                />
              </FormSwitch>
              <FormErrorMessages :messages="vatProfileForm.errors.is_active" />
            </div>
            <div class="col-span-12">
              <FormLabel>
                {{ t('views.vat_profile.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                id="remarks"
                v-model="vatProfileForm.remarks"
                :placeholder="t('views.vat_profile.fields.remarks')"
                rows="3"
                @change="vatProfileForm.validate('remarks')"
              />
              <FormErrorMessages :messages="vatProfileForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>
      <template #card-items-button>
        <div class="flex gap-4">
          <Button type="submit" href="#" variant="primary" class="w-28 shadow-md" :disabled="vatProfileForm.validating">
            <Lucide v-if="vatProfileForm.validating" icon="Loader" class="animate-spin" />
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
