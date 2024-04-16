<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import BrandService from "../../services/BrandService";
import DashboardService from "../../services/DashboardService";
import CacheService from "../../services/CacheService";
import { TwoColumnsLayout } from "../../base-components/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormInputCode,
    FormErrorMessages
} from "../../base-components/Form";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "../../types/enums/CardState";
import Button from "../../base-components/Button";
import { ViewMode } from "../../types/enums/ViewMode";
import { debounce } from "lodash";
import Lucide from "../../base-components/Lucide";
import { useSelectedUserLocationStore } from "../../stores/user-location";
import { useRouter } from "vue-router";
import { ErrorCode } from "../../types/enums/ErrorCode";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const brandServices = new BrandService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();

const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'views.brand.field_groups.company_info', state: CardState.Expanded, },
    { title: 'views.brand.field_groups.brand_data', state: CardState.Expanded, },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const brandForm = brandServices.useBrandCreateForm();
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emits('mode-state', ViewMode.FORM_CREATE);

    if (!isUserLocationSelected.value) {
        router.push({ name: 'side-menu-error-code', params: { code: ErrorCode.USERLOCATION_REQUIRED } });
    }

    getDDL();

    setCompanyIdData();
    loadFromCache();
});
// #endregion

// #region Methods
const setCompanyIdData = () => {
    brandForm.setData({
        company_id: selectedUserLocation.value.company.id,
    });
}
const loadFromCache = () => {
    let data = cacheServices.getLastEntity('BRAND_CREATE') as Record<string, unknown>;
    if (!data) return;
    brandForm.setData(data);
}

const getDDL = (): void => {

}

const handleExpandCard = (index: number) => {
    if (cards.value[index].state === CardState.Collapsed) {
        cards.value[index].state = CardState.Expanded
    } else if (cards.value[index].state === CardState.Expanded) {
        cards.value[index].state = CardState.Collapsed
    }
}

const scrollToError = (id: string): void => {
    let el = document.getElementById(id);

    if (!el) return;

    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

const onSubmit = async () => {
    if (brandForm.hasErrors) {
        scrollToError(Object.keys(brandForm.errors)[0]);
    }

    emits('loading-state', true);
    await brandForm.submit().then((response) => {
        if (response) {
            router.push({ name: 'side-menu-product-brand-list' });
        }
    }).catch((error) => {
        console.log(error);
    }).finally(() => {
        emits('loading-state', false);
    });
};

const resetForm = () => {
    brandForm.reset();
    brandForm.setErrors({});
}
// #endregion

// #region Watchers
watch(
    brandForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('BRAND_CREATE', newValue.data())
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="brandForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
                <div class="p-5">
                    <FormLabel>
                        {{ selectedUserLocation.company.code }}
                        <br />
                        {{ selectedUserLocation.company.name }}
                    </FormLabel>
                    <FormInput type="hidden" v-model="brandForm.company_id" />
                </div>
            </template>
            <template #card-items-1>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel html-for="code" :class="{ 'text-danger': brandForm.invalid('code') }">
                            {{ t('views.brand.fields.code') }}
                        </FormLabel>
                        <FormInputCode id="code" v-model="brandForm.code" name="code" type="text"
                            :class="{ 'border-danger': brandForm.invalid('code') }"
                            :placeholder="t('views.brand.fields.code')" @change="brandForm.validate('code')" />
                        <FormErrorMessages :messages="brandForm.errors.code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="name" :class="{ 'text-danger': brandForm.invalid('name') }">
                            {{ t('views.brand.fields.name') }}
                        </FormLabel>
                        <FormInput id="name" v-model="brandForm.name" name="name" type="text"
                            :class="{ 'border-danger': brandForm.invalid('name') }"
                            :placeholder="t('views.brand.fields.name')" @change="brandForm.validate('name')" />
                        <FormErrorMessages :messages="brandForm.errors.name" />
                    </div>
                </div>
            </template>
            <template #card-items-button>
                <div class="flex gap-4">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
                        :disabled="brandForm.validating || brandForm.hasErrors">
                        <Lucide v-if="brandForm.validating" icon="Loader" class="animate-spin" />
                        <template v-else>
                            {{ t("components.buttons.submit") }}
                        </template>
                    </Button>
                    <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md" @click="resetForm">
                        {{ t("components.buttons.reset") }}
                    </Button>
                </div>
            </template>
        </TwoColumnsLayout>
    </form>
</template>