<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import UnitService from "../../services/UnitService";
import DashboardService from "../../services/DashboardService";
import CacheService from "../../services/CacheService";
import { DropDownOption } from "../../types/models/DropDownOption";
import { TwoColumnsLayout } from "../../base-components/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormTextarea,
    FormSelect,
    FormInputCode,
    FormSwitch,
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
const unitServices = new UnitService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();

const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'views.unit.field_groups.company_info', state: CardState.Expanded, },
    { title: 'views.unit.field_groups.unit_data', state: CardState.Expanded, },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const categoryDDL = ref<Array<DropDownOption> | null>(null);

const unitForm = unitServices.useUnitCreateForm();
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
    unitForm.setData({
        company_id: selectedUserLocation.value.company.id,
    });
}
const loadFromCache = () => {
    let data = cacheServices.getLastEntity('UNIT_CREATE') as Record<string, unknown>;
    if (!data) return;
    unitForm.setData(data);
}

const getDDL = (): void => {
    dashboardServices.getUnitCategoryDDL().then((result: Array<DropDownOption> | null) => {
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
    if (unitForm.hasErrors) {
        scrollToError(Object.keys(unitForm.errors)[0]);
    }

    emits('loading-state', true);
    await unitForm.submit().then(() => {
        resetForm();
        router.push({ name: 'side-menu-product-unit-list' });
    }).catch(() => {
        console.log('error');
    }).finally(() => {
        emits('loading-state', false);
    });
};

const resetForm = () => {
    unitForm.reset();
    unitForm.setErrors({});
}
// #endregion

// #region Watchers
watch(
    unitForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('UNIT_CREATE', newValue.data())
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="unitForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
                <div class="p-5">
                    <FormLabel>
                        {{ selectedUserLocation.company.code }}
                        <br />
                        {{ selectedUserLocation.company.name }}
                    </FormLabel>
                    <FormInput type="hidden" v-model="unitForm.company_id" />
                </div>
            </template>
            <template #card-items-1>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel html-for="code" :class="{ 'text-danger': unitForm.invalid('code') }">
                            {{ t('views.unit.fields.code') }}
                        </FormLabel>
                        <FormInputCode id="code" v-model="unitForm.code" name="code" type="text"
                            :class="{ 'border-danger': unitForm.invalid('code') }"
                            :placeholder="t('views.unit.fields.code')" @change="unitForm.validate('code')" />
                        <FormErrorMessages :messages="unitForm.errors.code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="name" :class="{ 'text-danger': unitForm.invalid('name') }">
                            {{ t('views.unit.fields.name') }}
                        </FormLabel>
                        <FormInput id="name" v-model="unitForm.name" name="name" type="text"
                            :class="{ 'border-danger': unitForm.invalid('name') }"
                            :placeholder="t('views.unit.fields.name')" @change="unitForm.validate('name')" />
                        <FormErrorMessages :messages="unitForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="description">
                            {{ t('views.unit.fields.description') }}
                        </FormLabel>
                        <FormTextarea id="description" v-model="unitForm.description" name="description" type="text"
                            :placeholder="t('views.unit.fields.description')" rows="3" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="category" :class="{ 'text-danger': unitForm.invalid('category') }">
                            {{ t('views.product_group.fields.category') }}
                        </FormLabel>
                        <FormSelect id="category" v-model="unitForm.category" name="category"
                            :class="{ 'border-danger': unitForm.invalid('category') }"
                            @change="unitForm.validate('category')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in categoryDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="unitForm.errors.category" />
                    </div>
                </div>
            </template>
            <template #card-items-button>
                <div class="flex gap-4">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
                        :disabled="unitForm.validating || unitForm.hasErrors">
                        <Lucide v-if="unitForm.validating" icon="Loader" class="animate-spin" />
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