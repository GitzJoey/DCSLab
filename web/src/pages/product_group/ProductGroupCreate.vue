<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import ProductGroupService from "../../services/ProductGroupService";
import DashboardService from "../../services/DashboardService";
import CacheService from "../../services/CacheService";
import { DropDownOption } from "../../types/models/DropDownOption";
import { TwoColumnsLayout } from "../../base-components/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormSelect,
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
const productGroupServices = new ProductGroupService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();

const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'views.product_group.field_groups.company_info', state: CardState.Expanded, },
    { title: 'views.product_group.field_groups.product_group_data', state: CardState.Expanded, },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const categoryDDL = ref<Array<DropDownOption> | null>(null);

const productGroupForm = productGroupServices.useProductGroupCreateForm();
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
    productGroupForm.setData({
        company_id: selectedUserLocation.value.company.id,
    });
}
const loadFromCache = () => {
    let data = cacheServices.getLastEntity('PRODUCT_GROUP_CREATE') as Record<string, unknown>;
    if (!data) return;
    productGroupForm.setData(data);
}

const getDDL = (): void => {
    dashboardServices.getProductGroupCategoryDDL().then((result: Array<DropDownOption> | null) => {
        categoryDDL.value = result;
    });
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
    if (productGroupForm.hasErrors) {
        scrollToError(Object.keys(productGroupForm.errors)[0]);
    }

    emits('loading-state', true);
    await productGroupForm.submit().then(() => {
        resetForm();
        router.push({ name: 'side-menu-product-product_group-list' });
    }).catch(() => {
        console.log('error');
    }).finally(() => {
        emits('loading-state', false);
    });
};

const resetForm = () => {
    productGroupForm.reset();
    productGroupForm.setErrors({});
}
// #endregion

// #region Watchers
watch(
    productGroupForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('PRODUCT_GROUP_CREATE', newValue.data())
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="productGroupForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
                <div class="p-5">
                    <FormLabel>
                        {{ selectedUserLocation.company.code }}
                        <br />
                        {{ selectedUserLocation.company.name }}
                    </FormLabel>
                    <FormInput type="hidden" v-model="productGroupForm.company_id" />
                </div>
            </template>
            <template #card-items-1>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel html-for="code" :class="{ 'text-danger': productGroupForm.invalid('code') }">
                            {{ t('views.product_group.fields.code') }}
                        </FormLabel>
                        <FormInputCode id="code" v-model="productGroupForm.code" name="code" type="text"
                            :class="{ 'border-danger': productGroupForm.invalid('code') }"
                            :placeholder="t('views.product_group.fields.code')"
                            @change="productGroupForm.validate('code')" />
                        <FormErrorMessages :messages="productGroupForm.errors.code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="name" :class="{ 'text-danger': productGroupForm.invalid('name') }">
                            {{ t('views.product_group.fields.name') }}
                        </FormLabel>
                        <FormInput id="name" v-model="productGroupForm.name" name="name" type="text"
                            :class="{ 'border-danger': productGroupForm.invalid('name') }"
                            :placeholder="t('views.product_group.fields.name')"
                            @change="productGroupForm.validate('name')" />
                        <FormErrorMessages :messages="productGroupForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="category" :class="{ 'text-danger': productGroupForm.invalid('category') }">
                            {{ t('views.product_group.fields.category') }}
                        </FormLabel>
                        <FormSelect id="category" v-model="productGroupForm.category" name="category"
                            :class="{ 'border-danger': productGroupForm.invalid('category') }"
                            @change="productGroupForm.validate('category')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in categoryDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="productGroupForm.errors.category" />
                    </div>
                </div>
            </template>
            <template #card-items-button>
                <div class="flex gap-4">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
                        :disabled="productGroupForm.validating || productGroupForm.hasErrors">
                        <Lucide v-if="productGroupForm.validating" icon="Loader" class="animate-spin" />
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