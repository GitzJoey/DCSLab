<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import {
    convertErrorTypeToAlertListType
} from "@/utils/helper";
import { useRoute, useRouter } from "vue-router";
import UnitService from "@/services/UnitService";
import CacheService from "@/services/CacheService";
import DashboardService from "@/services/DashboardService";
import { TwoColumnsLayout } from "@/components/Base/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormInputCode,
    FormErrorMessages,
    FormTextarea,
    FormSelect,
} from "@/components/Base/Form";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "@/types/enums/CardState";
import { ServiceResponse } from "@/types/services/ServiceResponse";
import { ViewMode } from "@/types/enums/ViewMode";
import Button from "@/components/Base/Button";
import { debounce } from "lodash";
import { Unit } from "@/types/models/Unit";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { ErrorCode } from "@/types/enums/ErrorCode";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
import Lucide from "@/components/Base/Lucide";
import { DropDownOption } from "@/types/models/DropDownOption";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const unitService = new UnitService();
const cacheServices = new CacheService();
const dashboardService = new DashboardService();
// #endregion

// #region Props, Emits
const emits = defineEmits([
    "mode-state",
    "loading-state",
    "update-profile",
    "show-alertplaceholder",
]);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    {
        title: "views.unit.field_groups.company_info",
        state: CardState.Expanded,
    },
    {
        title: "views.unit.field_groups.unit_data",
        state: CardState.Expanded,
    },
    { title: "", state: CardState.Hidden, id: "button" },
]);

const unitForm = unitService.useUnitEditForm(route.params.ulid as string);
const unitTypesDDL = ref<Array<DropDownOption> | null>([]);
// #endregion

// #region Computed
const isUserLocationSelected = computed(
    () => selectedUserLocationStore.isUserLocationSelected
);
const selectedUserLocation = computed(
    () => selectedUserLocationStore.selectedUserLocation
);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emits("mode-state", ViewMode.FORM_EDIT);
    if (!isUserLocationSelected.value) {
        router.push({
            name: "side-menu-error-code",
            params: { code: ErrorCode.USERLOCATION_REQUIRED },
        });
    }
    await getUnitTypesDDL();
    await loadData(route.params.ulid as string);
});
// #endregion

// #region Methods
const getUnitTypesDDL = async () => {
    unitTypesDDL.value = await unitService.getTypes();
};

const loadData = async (ulid: string) => {
    emits("loading-state", true);
    const result: ServiceResponse<Unit | null> = await unitService.read(ulid);

    if (result.success && result.data) {
        unitForm.setData({
            company_id: result.data.company.id,
            code: result.data.code,
            name: result.data.name,
            description: result.data.description,
            type: result.data.type,
        });
    } else {
        router.push({ name: "side-menu-product-unit-list" });
    }
    emits("loading-state", false);
};

const handleExpandCard = (index: number) => {
    if (cards.value[index].state === CardState.Collapsed) {
        cards.value[index].state = CardState.Expanded;
    } else if (cards.value[index].state === CardState.Expanded) {
        cards.value[index].state = CardState.Collapsed;
    }
};

const scrollToError = (id: string): void => {
    let el = document.getElementById(id);
    if (!el) return;
    el.scrollIntoView({ behavior: "smooth", block: "center" });
};

const onSubmit = async () => {
    if (unitForm.hasErrors) {
        scrollToError(Object.keys(unitForm.errors)[0]);
    }

    emits("loading-state", true);
    await unitForm
        .submit()
        .then(() => {
            emits("update-profile");
            router.push({ name: "side-menu-product-unit-list" });
        })
        .catch((error) => {
            let errorList: Record<
                string,
                Array<string>
            > = convertErrorTypeToAlertListType(error as Error);
            showAlertPlaceholder("danger", "", errorList);
        })
        .finally(() => {
            emits("loading-state", false);
        });
};

const resetForm = async () => {
    unitForm.reset();
    unitForm.setErrors({});
    await loadData(route.params.ulid as string);
};

const setCode = () => {
    unitForm.forgetError("code");
    if (unitForm.code == "_AUTO_") {
        unitForm.setData({ code: "" });
    } else {
        unitForm.setData({ code: "_AUTO_" });
    }
};

const showAlertPlaceholder = (
    pAlertType: "hidden" | "danger" | "success" | "warning" | "pending" | "dark",
    pTitle: string,
    pAlertList: Record<string, Array<string>> | null
) => {
    let ap: AlertPlaceholderProps = {
        alertType: pAlertType,
        title: pTitle,
        alertList: pAlertList,
    };
    emits("show-alertplaceholder", ap);
};


// #region Watchers
watch(
    unitForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity("UNIT_EDIT", newValue.data());
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="unitForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout
            :cards="cards"
            :using-side-tab="false"
            @handle-expand-card="handleExpandCard"
        >
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
                        <FormLabel :class="{ 'text-danger': unitForm.invalid('code') }">
                            {{ t('views.unit.fields.code') }}
                        </FormLabel>
                        <FormInputCode
                            id="code"
                            v-model="unitForm.code"
                            :class="{ 'border-danger': unitForm.invalid('code') }"
                            @change="unitForm.validate('code')"
                            @set-auto="setCode"
                        />
                        <FormErrorMessages :messages="unitForm.errors.code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': unitForm.invalid('name') }">
                            {{ t('views.unit.fields.name') }}
                        </FormLabel>
                        <FormInput
                            id="name"
                            type="text"
                            v-model="unitForm.name"
                            :class="{ 'border-danger': unitForm.invalid('name') }"
                            :placeholder="t('views.unit.fields.name')"
                            @change="unitForm.validate('name')"
                        />
                        <FormErrorMessages :messages="unitForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel>
                            {{ t('views.unit.fields.description') }}
                        </FormLabel>
                        <FormTextarea
                            id="description"
                            v-model="unitForm.description"
                            :class="{
                                'border-danger': unitForm.invalid('description'),
                            }"
                            :placeholder="t('views.unit.fields.description')"
                            @change="unitForm.validate('description')"
                        />
                        <FormErrorMessages
                            :messages="unitForm.errors.description"
                        />
                    </div>
                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': unitForm.invalid('type') }">
                            {{ t('views.unit.fields.type') }}
                        </FormLabel>
                        <FormSelect
                            v-model="unitForm.type"
                            :class="{ 'border-danger': unitForm.invalid('type') }"
                            @change="unitForm.validate('type')"
                        >
                            <option value="" disabled selected>{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="item in unitTypesDDL" :key="item.code" :value="item.code">
                                {{ t(item.name) }}
                            </option>
                        </FormSelect>
                        <FormErrorMessages :messages="unitForm.errors.type" />
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
                        :disabled="unitForm.validating || unitForm.hasErrors"
                    >
                        <Lucide
                            v-if="unitForm.validating"
                            icon="Loader"
                            class="animate-spin"
                        />
                        <template v-else>
                            {{ t("components.buttons.submit") }}
                        </template>
                    </Button>
                    <Button
                        type="button"
                        href="#"
                        variant="soft-secondary"
                        class="w-28 shadow-md"
                        @click="resetForm"
                    >
                        {{ t("components.buttons.reset") }}
                    </Button>
                </div>
            </template>
        </TwoColumnsLayout>
    </form>
</template>
