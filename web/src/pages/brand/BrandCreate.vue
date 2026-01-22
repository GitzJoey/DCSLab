<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { isAxiosError, AxiosError } from "axios";
import BrandService from "@/services/BrandService";
import CacheService from "@/services/CacheService";
import { TwoColumnsLayout } from "@/components/Base/Form/FormLayout";
import { FormInput, FormLabel, FormInputCode, FormErrorMessages } from "@/components/Base/Form";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "@/types/enums/CardState";
import Button from "@/components/Base/Button";
import { ViewMode } from "@/types/enums/ViewMode";
import { debounce } from "lodash";
import Lucide from "@/components/Base/Lucide";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { useRouter } from "vue-router";
import { ErrorCode } from "@/types/enums/ErrorCode";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const brandService = new BrandService();
const cacheServices = new CacheService();
// #endregion

// #region Props, Emits
const emits = defineEmits(["mode-state", "loading-state", "update-profile", "show-alertplaceholder"]);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
	{
		title: "views.brand.field_groups.company_info",
		state: CardState.Expanded,
	},
	{
		title: "views.brand.field_groups.brand_data",
		state: CardState.Expanded,
	},
	{ title: "", state: CardState.Hidden, id: "button" },
]);

const brandForm = brandService.useBrandCreateForm();
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
	emits("mode-state", ViewMode.FORM_CREATE);
	loadFromCache();
	if (!isUserLocationSelected.value) {
		router.push({
			name: "side-menu-error-code",
			params: { code: ErrorCode.USERLOCATION_REQUIRED },
		});
	}
	setCompanyIdData();
});
// #endregion

// #region Methods
const setCompanyIdData = () => {
	brandForm.setData({
		company_id: selectedUserLocation.value.company.id,
	});
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
	if (brandForm.hasErrors) {
		scrollToError(Object.keys(brandForm.errors)[0]);
	}

	emits("loading-state", true);
	await brandForm
		.submit()
		.then((response: any) => {
			resetForm();
			emits("update-profile");
			router.push({ name: "side-menu-product-brand-list" });
		})
		.catch((error: any) => {
			console.error(error);
			let errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error);
			showAlertPlaceholder("danger", "", errorList);
		})
		.finally(() => {
			emits("loading-state", false);
		});
};

const resetForm = () => {
	brandForm.reset();
	brandForm.setErrors({});
};

const setCode = () => {
	brandForm.forgetError("code");
	if (brandForm.code == "_AUTO_") {
		brandForm.setData({ code: "" });
	} else {
		brandForm.setData({ code: "_AUTO_" });
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

const convertErrorTypeToAlertListType = (error: unknown) => {
	const record: Record<string, Array<string>> = {};
	const anyError = error as any;
	const response = isAxiosError(error) ? (error as AxiosError).response : anyError?.response;

	if (response && response.data) {
		const data = response.data as any;
		if (data.errors && typeof data.errors === "object") {
			for (const key of Object.keys(data.errors)) {
				const value = data.errors[key];
				if (Array.isArray(value)) {
					record[key] = value;
				} else if (value !== undefined && value !== null) {
					record[key] = [String(value)];
				}
			}
			return record;
		}
		if (data.message) {
			record.error = [String(data.message)];
			return record;
		}
	}

	if (error instanceof Error && error.message) {
		record.error = [error.message];
	} else {
		record.error = ["Unknown error"];
	}
	return record;
};

const loadFromCache = () => {
	const data = cacheServices.getLastEntity("BRAND_CREATE") as Record<string, unknown>;
	if (data) {
		brandForm.setData(data);
	}
};
// #endregion

// #region Watchers
watch(
	brandForm,
	debounce((newValue): void => {
		cacheServices.setLastEntity("BRAND_CREATE", newValue.data());
		if (brandForm.hasErrors) {
		}
	}, 500),
	{ deep: true },
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
						<FormLabel :class="{ 'text-danger': brandForm.invalid('code') }">
							{{ t("views.brand.fields.code") }}
						</FormLabel>
						<FormInputCode
							v-model="brandForm.code"
							:class="{ 'border-danger': brandForm.invalid('code') }"
							:placeholder="t('views.brand.fields.code')"
							@set-auto="setCode"
							@change="brandForm.validate('code')"
						/>
						<FormErrorMessages :messages="brandForm.errors.code" />
					</div>
					<div class="pb-4">
						<FormLabel :class="{ 'text-danger': brandForm.invalid('name') }">
							{{ t("views.brand.fields.name") }}
						</FormLabel>
						<FormInput
							v-model="brandForm.name"
							type="text"
							:class="{ 'border-danger': brandForm.invalid('name') }"
							:placeholder="t('views.brand.fields.name')"
							@change="brandForm.validate('name')"
						/>
						<FormErrorMessages :messages="brandForm.errors.name" />
					</div>
				</div>
			</template>
			<template #card-items-button>
				<div class="flex gap-4">
					<Button type="submit" href="#" variant="primary" class="w-28 shadow-md" :disabled="brandForm.validating">
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
