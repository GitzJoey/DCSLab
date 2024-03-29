<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed, watch } from "vue";
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
import { useSelectedUserLocationStore } from "../../stores/user-location";
import { ErrorCode } from "../../types/enums/ErrorCode";
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

const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'views.branch.field_groups.company_info', state: CardState.Expanded, },
    { title: 'views.branch.field_groups.branch_data', state: CardState.Expanded, },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const statusDDL = ref<Array<DropDownOption> | null>(null);

const branchForm = branchServices.useBranchEditForm(route.params.ulid as string);
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
    let response: ServiceResponse<Branch | null> = await branchServices.read(ulid);

    if (response && response.data) {
        branchForm.setData({
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
    branchForm.reset();
    branchForm.setErrors({});
    await loadData(route.params.ulid as string);
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
                    <FormLabel>
                        {{ selectedUserLocation.company.code }}
                        <br />
                        {{ selectedUserLocation.company.name }}
                    </FormLabel>
                    <FormInput type="hidden" v-model="branchForm.company_id" />
                </div>
            </template>
            <template #card-items-1>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel html-for="code" :class="{ 'text-danger': branchForm.invalid('code') }">
                            {{ t('views.branch.fields.code') }}
                        </FormLabel>
                        <FormInputCode id="code" v-model="branchForm.code" name="code" type="text"
                            :class="{ 'border-danger': branchForm.invalid('code') }"
                            :placeholder="t('views.branch.fields.code')" @change="branchForm.validate('code')" />
                        <FormErrorMessages :messages="branchForm.errors.code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="name" :class="{ 'text-danger': branchForm.invalid('name') }">
                            {{ t('views.branch.fields.name') }}
                        </FormLabel>
                        <FormInput id="name" v-model="branchForm.name" name="name" type="text"
                            :class="{ 'border-danger': branchForm.invalid('name') }"
                            :placeholder="t('views.branch.fields.name')" @change="branchForm.validate('name')" />
                        <FormErrorMessages :messages="branchForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="address">
                            {{ t('views.branch.fields.address') }}
                        </FormLabel>
                        <FormTextarea id="address" v-model="branchForm.address" name="address" type="text"
                            :placeholder="t('views.branch.fields.address')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="city">
                            {{ t('views.branch.fields.city') }}
                        </FormLabel>
                        <FormInput id="city" v-model="branchForm.city" name="city" type="text"
                            :placeholder="t('views.branch.fields.city')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="contact">
                            {{ t('views.branch.fields.contact') }}
                        </FormLabel>
                        <FormInput id="contact" v-model="branchForm.contact" name="contact" type="text"
                            :placeholder="t('views.branch.fields.contact')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="is_main" :class="{ 'text-danger': branchForm.invalid('is_main') }"
                            class="pr-5">
                            {{ t('views.branch.fields.is_main') }}
                        </FormLabel>
                        <FormSwitch>
                            <FormSwitch.Input id="is_main" v-model="branchForm.is_main" name="is_main" type="checkbox"
                                :class="{ 'border-danger': branchForm.invalid('is_main') }"
                                :placeholder="t('views.branch.fields.is_main')" @change="branchForm.validate('is_main')" />
                        </FormSwitch>
                        <FormErrorMessages :messages="branchForm.errors.is_main" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="remarks">
                            {{ t('views.branch.fields.remarks') }}
                        </FormLabel>
                        <FormTextarea id="remarks" v-model="branchForm.remarks" name="remarks" type="text"
                            :placeholder="t('views.branch.fields.remarks')" rows="3" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="status" :class="{ 'text-danger': branchForm.invalid('status') }">
                            {{ t('views.branch.fields.status') }}
                        </FormLabel>
                        <FormSelect id="status" v-model="branchForm.status" name="status"
                            :class="{ 'border-danger': branchForm.invalid('status') }"
                            @change="branchForm.validate('status')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="branchForm.errors.status" />
                    </div>
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