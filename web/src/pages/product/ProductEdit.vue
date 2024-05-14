<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";
import ProductService from "../../services/ProductService";
import DashboardService from "../../services/DashboardService";
import CacheService from "../../services/CacheService";
import { TwoColumnsLayout } from "../../base-components/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormTextarea,
    FormSelect,
    FormSwitch,
    FormInputCode,
    FormErrorMessages,
} from "../../base-components/Form";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "../../types/enums/CardState";
import { DropDownOption } from "../../types/models/DropDownOption";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { ViewMode } from "../../types/enums/ViewMode";
import { Product } from "../../types/models/Product";
import Button from "../../base-components/Button";
import { debounce } from "lodash";
import Lucide from "../../base-components/Lucide";
import { useSelectedUserLocationStore } from "../../stores/user-location";
import { ErrorCode } from "../../types/enums/ErrorCode";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const route = useRoute();

const productServices = new ProductService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();

const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'views.product.field_groups.company_info', state: CardState.Expanded, },
    { title: 'views.product.field_groups.product_data', state: CardState.Expanded, },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const statusDDL = ref<Array<DropDownOption> | null>(null);

const productForm = productServices.useProductEditForm(route.params.ulid as string);
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emits('mode-state', ViewMode.FORM_EDIT);

    if (!isUserLocationSelected.value) {
        router.push({ name: 'side-menu-error-code', params: { code: ErrorCode.USERLOCATION_REQUIRED } });
    }

    getDDL();

    await loadData(route.params.ulid as string);
});
// #endregion

// #region Methods
const loadData = async (ulid: string) => {
    emits('loading-state', true);
    let response: ServiceResponse<Product | null> = await productServices.read(ulid);

    if (response && response.data) {
        productForm.setData({
            company_id: response.data.company.id,
            code: response.data.code,
            name: response.data.name,
            address: response.data.address,
            city: response.data.city,
            contact: response.data.contact,
            is_main: response.data.is_main,
            remarks: response.data.remarks,
            status: response.data.status,
        });
    }
    emits('loading-state', false);
}

const getDDL = (): void => {
    dashboardServices.getStatusDDL().then((result: Array<DropDownOption> | null) => {
        statusDDL.value = result;
    });
}

const handleExpandCard = (index: number) => {
    if (cards.value[index].state === CardState.Collapsed) {
        cards.value[index].state = CardState.Expanded
    } else if (cards.value[index].state === CardState.Expanded) {
        cards.value[index].state = CardState.Collapsed
    }
}

const onSubmit = async () => {

};

const resetForm = async () => {
    productForm.reset();
    productForm.setErrors({});
    await loadData(route.params.ulid as string);
}
// #endregion

// #region Watchers
watch(
    productForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('PRODUCT_EDIT', newValue.data())
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="productForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
                <div class="p-5">
                    <FormLabel>
                        {{ selectedUserLocation.company.code }}
                        <br />
                        {{ selectedUserLocation.company.name }}
                    </FormLabel>
                    <FormInput type="hidden" v-model="productForm.company_id" />
                </div>
            </template>
            <template #card-items-1>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel html-for="code" :class="{ 'text-danger': productForm.invalid('code') }">
                            {{ t('views.product.fields.code') }}
                        </FormLabel>
                        <FormInputCode id="code" v-model="productForm.code" name="code" type="text"
                            :class="{ 'border-danger': productForm.invalid('code') }"
                            :placeholder="t('views.product.fields.code')" @change="productForm.validate('code')" />
                        <FormErrorMessages :messages="productForm.errors.code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="name" :class="{ 'text-danger': productForm.invalid('name') }">
                            {{ t('views.product.fields.name') }}
                        </FormLabel>
                        <FormInput id="name" v-model="productForm.name" name="name" type="text"
                            :class="{ 'border-danger': productForm.invalid('name') }"
                            :placeholder="t('views.product.fields.name')" @change="productForm.validate('name')" />
                        <FormErrorMessages :messages="productForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="address">
                            {{ t('views.product.fields.address') }}
                        </FormLabel>
                        <FormTextarea id="address" v-model="productForm.address" name="address" type="text"
                            :placeholder="t('views.product.fields.address')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="city">
                            {{ t('views.product.fields.city') }}
                        </FormLabel>
                        <FormInput id="city" v-model="productForm.city" name="city" type="text"
                            :placeholder="t('views.product.fields.city')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="contact">
                            {{ t('views.product.fields.contact') }}
                        </FormLabel>
                        <FormInput id="contact" v-model="productForm.contact" name="contact" type="text"
                            :placeholder="t('views.product.fields.contact')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="is_main" :class="{ 'text-danger': productForm.invalid('is_main') }"
                            class="pr-5">
                            {{ t('views.product.fields.is_main') }}
                        </FormLabel>
                        <FormSwitch>
                            <FormSwitch.Input id="is_main" v-model="productForm.is_main" name="is_main" type="checkbox"
                                :class="{ 'border-danger': productForm.invalid('is_main') }"
                                :placeholder="t('views.product.fields.is_main')" @change="productForm.validate('is_main')" />
                        </FormSwitch>
                        <FormErrorMessages :messages="productForm.errors.is_main" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="remarks">
                            {{ t('views.product.fields.remarks') }}
                        </FormLabel>
                        <FormTextarea id="remarks" v-model="productForm.remarks" name="remarks" type="text"
                            :placeholder="t('views.product.fields.remarks')" rows="3" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="status" :class="{ 'text-danger': productForm.invalid('status') }">
                            {{ t('views.product.fields.status') }}
                        </FormLabel>
                        <FormSelect id="status" v-model="productForm.status" name="status"
                            :class="{ 'border-danger': productForm.invalid('status') }"
                            @change="productForm.validate('status')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="productForm.errors.status" />
                    </div>
                </div>
            </template>
            <template #card-items-button>
                <div class="flex gap-4">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
                        :disabled="productForm.validating || productForm.hasErrors">
                        <Lucide v-if="productForm.validating" icon="Loader" class="animate-spin" />
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