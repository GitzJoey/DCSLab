<script setup lang="ts">
// #region Imports
import { onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";
import BranchService from "../../services/BranchService";
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
import { Branch } from "../../types/models/Branch";
import Button from "../../base-components/Button";
import { debounce } from "lodash";
import Lucide from "../../base-components/Lucide";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const route = useRoute();

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

const branchForm = branchServices.useBranchEditForm(route.params.ulid as string);
// #endregion

// #region Computed
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emit('mode-state', ViewMode.FORM_EDIT);
    getDDL();

    emit('loading-state', true);
    await loadData(route.params.ulid as string);
    emit('loading-state', false);
});
// #endregion

// #region Methods
const loadData = async (ulid: string) => {
    let response: ServiceResponse<Branch | null> = await branchServices.read(ulid);

    if (response && response.data) {
        branchForm.setData({

        });
    }
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

const resetForm = () => {
    branchForm.reset();
}
// #endregion

// #region Watchers
watch(
    branchForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('BRANCH_EDIT', newValue.data())
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