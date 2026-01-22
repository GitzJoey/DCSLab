<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";
import InvestorService from "@/services/InvestorService";
import DashboardService from "@/services/DashboardService";
import CacheService from "@/services/CacheService";
import { convertErrorTypeToAlertListType } from "@/utils/helper";
import { TwoColumnsLayout } from "@/components/Base/Form/FormLayout";
import { FormInput, FormLabel, FormTextarea, FormInputCode, FormErrorMessages } from "@/components/Base/Form";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "@/types/enums/CardState";
import { DropDownOption } from "@/types/models/DropDownOption";
import { ServiceResponse } from "@/types/services/ServiceResponse";
import { ViewMode } from "@/types/enums/ViewMode";
import Button from "@/components/Base/Button";
import { debounce } from "lodash";
import Lucide from "@/components/Base/Lucide";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { ErrorCode } from "@/types/enums/ErrorCode";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
import { Investor } from "@/types/models/Investor";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const route = useRoute();

const investorServices = new InvestorService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();

const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits(["mode-state", "loading-state", "update-profile", "show-alertplaceholder"]);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
	{
		title: "views.investor.field_groups.company_info",
		state: CardState.Expanded,
	},
	{
		title: "views.investor.field_groups.investor_data",
		state: CardState.Expanded,
	},
	{ title: "", state: CardState.Hidden, id: "button" },
]);

const statusDDL = ref<Array<DropDownOption> | null>(null);

const investorForm = investorServices.useInvestorEditForm(route.params.ulid as string);
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
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

	await Promise.all([getDDL(), loadData(route.params.ulid as string)]);
});
// #endregion

// #region Methods
const loadData = async (ulid: string) => {
	emits("loading-state", true);
	let result: ServiceResponse<Investor | null> = await investorServices.read(ulid);

	if (result.success && result.data) {
		investorForm.setData({
			company_id: result.data.company.id,
			code: result.data.code,
			name: result.data.name,
			remarks: result.data.remarks,
		});
	} else {
		router.push({ name: "side-menu-company-investor-list" });
	}
	emits("loading-state", false);
};

const getDDL = async (): Promise<void> => {
	statusDDL.value = await dashboardServices.getStatusDDL();
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
	if (investorForm.hasErrors) {
		scrollToError(Object.keys(investorForm.errors)[0]);
	}

	emits("loading-state", true);
	await investorForm
		.submit()
		.then(() => {
			emits("update-profile");
			router.push({ name: "side-menu-company-investor-list" });
		})
		.catch((error) => {
			let errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error as Error);
			showAlertPlaceholder("danger", "", errorList);
		})
		.finally(() => {
			emits("loading-state", false);
		});
};

const resetForm = async () => {
	investorForm.reset();
	investorForm.setErrors({});
	await loadData(route.params.ulid as string);
};

const setCode = () => {
	investorForm.forgetError("code");
	if (investorForm.code == "_AUTO_") {
		investorForm.setData({ code: "" });
	} else {
		investorForm.setData({ code: "_AUTO_" });
	}
};

const showAlertPlaceholder = (
	pAlertType: "hidden" | "danger" | "success" | "warning" | "pending" | "dark",
	pTitle: string,
	pAlertList: Record<string, Array<string>> | null,
) => {
	let ap: AlertPlaceholderProps = {
		alertType: pAlertType,
		title: pTitle,
		alertList: pAlertList,
	};

	emits("show-alertplaceholder", ap);
};

// #endregion

// #region Watchers
watch(
	investorForm,
	debounce((newValue): void => {
		cacheServices.setLastEntity("INVESTOR_EDIT", newValue.data());
	}, 500),
	{ deep: true },
);
// #endregion
</script>

<template>
	<form id="investorForm" @submit.prevent="onSubmit">
		<TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
			<template #card-items-0>
				<div class="p-5">
					<FormLabel>
						{{ selectedUserLocation.company.code }}
						<br />
						{{ selectedUserLocation.company.name }}
					</FormLabel>
					<FormInput type="hidden" v-model="investorForm.company_id" />
				</div>
			</template>
			<template #card-items-1>
				<div class="p-5">
					<div class="pb-4">
						<FormLabel :class="{ 'text-danger': investorForm.invalid('code') }">
							{{ t("views.investor.fields.code") }}
						</FormLabel>
						<FormInputCode
							v-model="investorForm.code"
							type="text"
							:class="{ 'border-danger': investorForm.invalid('code') }"
							:placeholder="t('views.investor.fields.code')"
							@set-auto="setCode"
							@change="investorForm.validate('code')"
						/>
						<FormErrorMessages :messages="investorForm.errors.code" />
					</div>
					<div class="pb-4">
						<FormLabel :class="{ 'text-danger': investorForm.invalid('name') }">
							{{ t("views.investor.fields.name") }}
						</FormLabel>
						<FormInput
							v-model="investorForm.name"
							type="text"
							:class="{ 'border-danger': investorForm.invalid('name') }"
							:placeholder="t('views.investor.fields.name')"
							@change="investorForm.validate('name')"
						/>
						<FormErrorMessages :messages="investorForm.errors.name" />
					</div>
					<div class="pb-4">
						<FormLabel>
							{{ t("views.investor.fields.remarks") }}
						</FormLabel>
						<FormTextarea v-model="investorForm.remarks" type="text" :placeholder="t('views.investor.fields.remarks')" rows="3" />
					</div>
				</div>
			</template>
			<template #card-items-button>
				<div class="flex gap-4">
					<Button type="submit" href="#" variant="primary" class="w-28 shadow-md" :disabled="investorForm.validating || investorForm.hasErrors">
						<Lucide v-if="investorForm.validating" icon="Loader" class="animate-spin" />
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
