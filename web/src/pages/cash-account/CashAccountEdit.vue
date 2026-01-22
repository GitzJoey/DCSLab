<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";
import CashAccountService from "@/services/CashAccountService";
import DashboardService from "@/services/DashboardService";
import CacheService from "@/services/CacheService";
import { convertErrorTypeToAlertListType } from "@/utils/helper";
import { TwoColumnsLayout } from "@/components/Base/Form/FormLayout";
import { FormInput, FormLabel, FormTextarea, FormSwitch, FormInputCode, FormErrorMessages } from "@/components/Base/Form";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "@/types/enums/CardState";
import { ServiceResponse } from "@/types/services/ServiceResponse";
import { ViewMode } from "@/types/enums/ViewMode";
import { CashAccount } from "@/types/models/CashAccount";
import Button from "@/components/Base/Button";
import { debounce } from "lodash";
import Lucide from "@/components/Base/Lucide";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { ErrorCode } from "@/types/enums/ErrorCode";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const route = useRoute();

const cashAccountServices = new CashAccountService();
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
		title: "views.cash_account.field_groups.company_info",
		state: CardState.Expanded,
	},
	{
		title: "views.cash_account.field_groups.cash_account_data",
		state: CardState.Expanded,
	},
	{ title: "", state: CardState.Hidden, id: "button" },
]);

const cashAccountForm = cashAccountServices.useCashAccountEditForm(route.params.ulid as string);
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

	await loadData(route.params.ulid as string);
});
// #endregion

// #region Methods
const loadData = async (ulid: string) => {
	emits("loading-state", true);
	let result: ServiceResponse<CashAccount | null> = await cashAccountServices.read(ulid);

	if (result.success && result.data) {
		cashAccountForm.setData({
			company_id: result.data.company.id,
			code: result.data.code,
			name: result.data.name,
			is_bank: result.data.is_bank,
			is_active: result.data.is_active,
			remarks: result.data.remarks,
		});
	} else {
		router.push({ name: "side-menu-finance-cash-account-list" });
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
	if (cashAccountForm.hasErrors) {
		scrollToError(Object.keys(cashAccountForm.errors)[0]);
	}

	emits("loading-state", true);
	await cashAccountForm
		.submit()
		.then(() => {
			emits("update-profile");
			router.push({ name: "side-menu-finance-cash-account-list" });
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
	cashAccountForm.reset();
	cashAccountForm.setErrors({});
	await loadData(route.params.ulid as string);
};

const setCode = () => {
	cashAccountForm.forgetError("code");
	if (cashAccountForm.code == "_AUTO_") {
		cashAccountForm.setData({ code: "" });
	} else {
		cashAccountForm.setData({ code: "_AUTO_" });
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
	cashAccountForm,
	debounce((newValue): void => {
		cacheServices.setLastEntity("CASH_ACCOUNT_EDIT", newValue.data());
	}, 500),
	{ deep: true },
);
// #endregion
</script>

<template>
	<form id="cashAccountForm" @submit.prevent="onSubmit">
		<TwoColumnsLayout :cards="cards" :title="t('views.cash_account.page_title')" :using-side-tab="false" @handle-expand-card="handleExpandCard">
			<template #card-items-0>
				<div class="p-5">
					<FormLabel>
						{{ selectedUserLocation.company.code }}
						<br />
						{{ selectedUserLocation.company.name }}
					</FormLabel>
					<FormInput type="hidden" v-model="cashAccountForm.company_id" />
				</div>
			</template>
			<template #card-items-1>
				<div class="p-5">
					<!-- Code -->
					<div class="pb-4">
						<FormLabel :class="{ 'text-danger': cashAccountForm.invalid('code') }">
							{{ t("views.cash_account.fields.code") }}
						</FormLabel>
						<FormInputCode
							v-model="cashAccountForm.code"
							type="text"
							:class="{ 'border-danger': cashAccountForm.invalid('code') }"
							:placeholder="t('views.cash_account.fields.code')"
							@set-auto="setCode"
							@change="cashAccountForm.validate('code')"
						/>
						<FormErrorMessages :messages="cashAccountForm.errors.code" />
					</div>

					<!-- Name -->
					<div class="pb-4">
						<FormLabel :class="{ 'text-danger': cashAccountForm.invalid('name') }">
							{{ t("views.cash_account.fields.name") }}
						</FormLabel>
						<FormInput
							v-model="cashAccountForm.name"
							type="text"
							:class="{ 'border-danger': cashAccountForm.invalid('name') }"
							:placeholder="t('views.cash_account.fields.name')"
							@change="cashAccountForm.validate('name')"
						/>
						<FormErrorMessages :messages="cashAccountForm.errors.name" />
					</div>

					<!-- Is Bank -->
					<div class="pb-4">
						<FormLabel :class="{ 'text-danger': cashAccountForm.invalid('is_bank') }" class="pr-5">
							{{ t("views.cash_account.fields.is_bank") }}
						</FormLabel>
						<FormSwitch>
							<FormSwitch.Input
								v-model="cashAccountForm.is_bank"
								type="checkbox"
								:class="{ 'border-danger': cashAccountForm.invalid('is_bank') }"
								:placeholder="t('views.cash_account.fields.is_bank')"
								@change="cashAccountForm.validate('is_bank')"
							/>
						</FormSwitch>
						<FormErrorMessages :messages="cashAccountForm.errors.is_bank" />
					</div>

					<!-- Is Active -->
					<div class="pb-4">
						<FormLabel :class="{ 'text-danger': cashAccountForm.invalid('is_active') }" class="pr-5">
							{{ t("views.cash_account.fields.is_active") }}
						</FormLabel>
						<FormSwitch>
							<FormSwitch.Input
								v-model="cashAccountForm.is_active"
								type="checkbox"
								:class="{
									'border-danger': cashAccountForm.invalid('is_active'),
								}"
								:placeholder="t('views.cash_account.fields.is_active')"
								@change="cashAccountForm.validate('is_active')"
							/>
						</FormSwitch>
						<FormErrorMessages :messages="cashAccountForm.errors.is_active" />
					</div>

					<!-- Remarks -->
					<div class="pb-4">
						<FormLabel>
							{{ t("views.cash_account.fields.remarks") }}
						</FormLabel>
						<FormTextarea v-model="cashAccountForm.remarks" type="text" :placeholder="t('views.cash_account.fields.remarks')" rows="3" />
					</div>
				</div>
			</template>
			<template #card-items-button>
				<div class="flex gap-4">
					<Button type="submit" href="#" variant="primary" class="w-28 shadow-md" :disabled="cashAccountForm.validating || cashAccountForm.hasErrors">
						<Lucide v-if="cashAccountForm.validating" icon="Loader" class="animate-spin" />
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
