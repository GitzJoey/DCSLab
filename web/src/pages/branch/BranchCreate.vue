<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import BranchService from "../../services/BranchService";
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
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();

const branchServices = new BranchService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();
// #endregion

// #region Props, Emits
const emit = defineEmits(['mode-state', 'loading-state']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'Company Information', state: CardState.Expanded, },
    { title: 'Branch Information', state: CardState.Expanded, },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const statusDDL = ref<Array<DropDownOption> | null>(null);

const branchForm = branchServices.useBranchCreateForm();
// #endregion

// #region Computed
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emit('mode-state', ViewMode.FORM_CREATE);
    getDDL();
});
// #endregion

// #region Methods
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

const scrollToError = (id: string): void => {
    let el = document.getElementById(id);

    if (!el) return;

    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

const onSubmit = async () => {
    if (branchForm.hasErrors) {
        scrollToError(Object.keys(branchForm.errors)[0]);
    }
};

const resetForm = () => {
    branchForm.reset();
}
// #endregion

// #region Watchers
watch(
    branchForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('BRANCH_CREATE', newValue.data())
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="branchForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
                <div class="p-5">
                </div>
            </template>
            <template #card-items-button>
                <div class="flex gap-4">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
                        :disabled="branchForm.validating || branchForm.hasErrors">
                        <Lucide v-if="branchForm.validating" icon="Loader" class="animate-spin" />
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