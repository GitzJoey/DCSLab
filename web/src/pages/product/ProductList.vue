<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref } from "vue";
import DataList from "@/components/DataList";
import { useI18n } from "vue-i18n";
import Button from "@/components/Base/Button";
import Lucide from "@/components/Base/Lucide";
import Table from "@/components/Base/Table";
import ProductService from "@/services/ProductService";
import { Product } from "@/types/models/Product";
import { Collection } from "@/types/resources/Collection";
import { DataListEmittedData } from "@/components/DataList/DataList.vue";
import { ServiceResponse } from "@/types/services/ServiceResponse";
import { ProductReadAnyPaginateRequest } from "@/types/services/product/ProductRequest";
import { useRouter } from "vue-router";
import { Dialog } from "@/components/Base/Headless";
import { ViewMode } from "@/types/enums/ViewMode";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { ErrorCode } from "@/types/enums/ErrorCode";
import { NotificationData } from "@/types/models/NotificationData";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
import { formatCurrency } from "@/utils/helper";
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const productServices = new ProductService();
const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits(["mode-state", "loading-state", "update-profile", "show-alertplaceholder", "show-notification"]);
// #endregion

// #region Refs
const deleteUlid = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
const productLists = ref<Collection<Array<Product>> | null>({
	data: [],
	meta: {
		current_page: 0,
		from: null,
		last_page: 0,
		path: "",
		per_page: 0,
		to: null,
		total: 0,
	},
	links: {
		first: "",
		last: "",
		prev: null,
		next: null,
	},
});
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
	emits("mode-state", ViewMode.LIST);

	if (!isUserLocationSelected.value) {
		router.push({
			name: "side-menu-error-code",
			params: { code: ErrorCode.USERLOCATION_REQUIRED },
		});
	}

	await getProducts("", true, 1, 10);
});
// #endregion

// #region Methods
const getProducts = async (search: string, refresh: boolean, page: number, per_page: number) => {
	emits("loading-state", true);

	let company_id = selectedUserLocation.value.company.id;

	const searchReq: ProductReadAnyPaginateRequest = {
		with_trashed: false,

		company_id: company_id,
		search: search,
		type: 1, // Physical Product
		include_id: undefined,

		refresh: refresh,
		page: page,
		per_page: per_page,
	};

	let result: ServiceResponse<Collection<Array<Product>> | null> = await productServices.readAnyPaginate(searchReq);

	if (result.success && result.data) {
		productLists.value = result.data;
	} else {
		showAlertPlaceholder("danger", "", result.errors as Record<string, Array<string>>);
	}

	emits("loading-state", false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
	await getProducts(data.search.text, false, data.pagination.page, data.pagination.per_page);
};

const viewSelected = (idx: number) => {
	if (expandDetail.value === idx) {
		expandDetail.value = null;
	} else {
		expandDetail.value = idx;
	}
};

const editSelected = (idx: number) => {
	if (!productLists.value) return;
	let ulid = productLists.value.data[idx].ulid;
	emits("mode-state", ViewMode.FORM_EDIT);
	router.push({
		name: "side-menu-product-product-edit",
		params: { ulid: ulid },
	});
};

const deleteSelected = (idx: number) => {
	if (!productLists.value) return;
	let ulid = productLists.value.data[idx].ulid;
	deleteUlid.value = ulid;
	deleteModalShow.value = true;
};

const confirmDelete = async () => {
	deleteModalShow.value = false;
	emits("loading-state", true);

	let result = await productServices.delete(deleteUlid.value);

	emits("loading-state", false);

	if (result.success) {
		await getProducts("", true, 1, 10);
		showNotification(t("views.product.alert.delete.title"), t("views.product.alert.delete.message"));
	} else {
		showAlertPlaceholder("danger", "", result.errors as Record<string, Array<string>>);
	}
};

const showNotification = (pTitle: string, pContent: string) => {
	let n: NotificationData = {
		title: pTitle,
		content: pContent,
	};
	emits("show-notification", n);
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
</script>

<template>
	<div class="grid grid-cols-12 gap-6 mt-5">
		<div class="col-span-12 intro-y lg:col-span-12">
			<DataList
				:title="t('views.product.table.title')"
				:data="productLists"
				:enable-search="true"
				:can-print="true"
				:can-export="true"
				:pagination="productLists ? productLists.meta : null"
				@dataListChanged="handleDataListChange"
			>
				<template #content>
					<Table class="mt-5" :hover="true">
						<Table.Thead variant="light">
							<Table.Tr>
								<Table.Th class="whitespace-nowrap">
									{{ t("views.product.detail.info_title") }}
								</Table.Th>
								<Table.Th class="whitespace-nowrap">
									{{ t("views.product.table.cols.name") }}
								</Table.Th>
								<Table.Th class="whitespace-nowrap">
									{{ t("views.product.table.cols.unit") }}
								</Table.Th>
								<Table.Th class="whitespace-nowrap text-right">
									{{ t("views.product.table.cols.price") }}
								</Table.Th>
								<Table.Th class="whitespace-nowrap"></Table.Th>
							</Table.Tr>
						</Table.Thead>
						<Table.Tbody v-if="productLists !== null">
							<template v-if="productLists.data.length === 0">
								<Table.Tr class="intro-x">
									<Table.Td colspan="5">
										<div class="flex justify-center italic">
											{{ t("components.data-list.data_not_found") }}
										</div>
									</Table.Td>
								</Table.Tr>
							</template>
							<template v-for="(item, itemIdx) in productLists.data" :key="item.ulid">
								<Table.Tr class="intro-x">
									<Table.Td>
										<div class="font-medium whitespace-nowrap">{{ item.code }}</div>
										<div class="mt-0.5 flex items-center flex-wrap">
											<span class="font-medium">{{ item.category.name }}</span>
											<template v-if="item.brand">
												<Lucide icon="ChevronRight" class="w-3 h-3 text-slate-400 mx-1" />
												<span>{{ item.brand.name }}</span>
											</template>
										</div>
										<div class="mt-1">
											<div v-if="item.status == 'ACTIVE'" class="flex items-center text-success text-xs">
												<Lucide icon="CheckCircle" class="w-3 h-3 mr-1" /> {{ t("views.product.status.active") }}
											</div>
											<div v-else class="flex items-center text-danger text-xs">
												<Lucide icon="X" class="w-3 h-3 mr-1" /> {{ t("views.product.status.inactive") }}
											</div>
										</div>
									</Table.Td>
									<Table.Td>
										<div class="font-medium">
											{{ item.name }}
										</div>
										<div class="text-slate-500 text-xs mt-0.5">
											{{ item.slug }}
										</div>
									</Table.Td>
									<Table.Td>
										<div class="flex flex-col gap-0">
											<div v-for="unit in item.product_units" :key="unit.ulid" class="h-6 flex items-center whitespace-nowrap">
												{{ unit.unit.name
												}}{{ unit.conversion_value > 1 ? ": " + formatCurrency(unit.conversion_value) + " " + item.product_units[0].unit.name : "" }}
											</div>
										</div>
									</Table.Td>
									<Table.Td>
										<div class="flex flex-col gap-0 items-end">
											<div v-for="unit in item.product_units" :key="unit.ulid" class="h-6 flex items-center whitespace-nowrap">
												{{ formatCurrency(unit.price) }}
											</div>
										</div>
									</Table.Td>
									<Table.Td>
										<div class="flex justify-end gap-1">
											<Button variant="outline-secondary" @click="viewSelected(itemIdx)">
												<Lucide icon="Info" class="w-4 h-4" />
											</Button>
											<Button variant="outline-secondary" @click="editSelected(itemIdx)">
												<Lucide icon="Pen" class="w-4 h-4" />
											</Button>
											<Button variant="outline-secondary" @click="deleteSelected(itemIdx)">
												<Lucide icon="Trash2" class="w-4 h-4 text-danger" />
											</Button>
										</div>
									</Table.Td>
								</Table.Tr>
								<Table.Tr
									:class="{
										'intro-x': true,
										'hidden transition-all': expandDetail !== itemIdx,
									}"
								>
									<Table.Td colspan="5" class="p-5">
										<div class="grid grid-cols-12 gap-6">
											<!-- Product Information -->
											<div class="col-span-12 lg:col-span-6">
												<div class="font-medium text-base mb-3 border-b pb-2">
													{{ t("views.product.detail.info_title") }}
												</div>
												<div class="grid grid-cols-1 gap-y-2">
													<div class="flex flex-row">
														<div class="w-48 text-slate-500">
															{{ t("views.product.fields.code") }}
														</div>
														<div class="flex-1 font-medium">{{ item.code }}</div>
													</div>
													<div class="flex flex-row">
														<div class="w-48 text-slate-500">
															{{ t("views.product.fields.category_id") }}
														</div>
														<div class="flex-1 font-medium">{{ item.category.name }}</div>
													</div>
													<div class="flex flex-row">
														<div class="w-48 text-slate-500">
															{{ t("views.product.fields.brand_id") }}
														</div>
														<div class="flex-1 font-medium">
															{{ item.brand ? item.brand.name : "-" }}
														</div>
													</div>
													<div class="flex flex-row">
														<div class="w-48 text-slate-500">
															{{ t("views.product.fields.name") }}
														</div>
														<div class="flex-1 font-medium">{{ item.name }}</div>
													</div>
													<div class="flex flex-row">
														<div class="w-48 text-slate-500">
															{{ t("views.product.fields.slug") }}
														</div>
														<div class="flex-1 font-medium">{{ item.slug }}</div>
													</div>
													<div class="flex flex-row">
														<div class="w-48 text-slate-500">
															{{ t("views.product.fields.status") }}
														</div>
														<div class="flex-1 font-medium">
															{{ item.status == "ACTIVE" ? t("views.product.status.active") : t("views.product.status.inactive") }}
														</div>
													</div>
													<div class="flex flex-row">
														<div class="w-48 text-slate-500">
															{{ t("views.product.fields.remarks") }}
														</div>
														<div class="flex-1 font-medium">{{ item.remarks || "-" }}</div>
													</div>
												</div>
											</div>

											<!-- Settings / Tax Info -->
											<div class="col-span-12 lg:col-span-6">
												<div class="font-medium text-base mb-3 border-b pb-2">
													{{ t("views.product.detail.settings_title") }}
												</div>
												<div class="grid grid-cols-1 gap-y-2">
													<div class="flex flex-row">
														<div class="w-48 text-slate-500">
															{{ t("views.product.fields.is_taxable") }}
														</div>
														<div class="flex-1 font-medium">
															{{ item.is_taxable ? t("components.dropdown.values.switch.on") : t("components.dropdown.values.switch.off") }}
														</div>
													</div>
													<div class="flex flex-row">
														<div class="w-48 text-slate-500">
															{{ t("views.product.fields.vat_rate") }}
														</div>
														<div class="flex-1 font-medium">{{ formatCurrency(item.vat_rate) }}%</div>
													</div>
													<div class="flex flex-row">
														<div class="w-48 text-slate-500">
															{{ t("views.product.fields.is_price_include_vat") }}
														</div>
														<div class="flex-1 font-medium">
															{{ item.is_price_include_vat ? t("components.dropdown.values.switch.on") : t("components.dropdown.values.switch.off") }}
														</div>
													</div>
													<div class="flex flex-row">
														<div class="w-48 text-slate-500">
															{{ t("views.product.fields.is_use_serial_number") }}
														</div>
														<div class="flex-1 font-medium">
															{{ item.is_use_serial_number ? t("components.dropdown.values.switch.on") : t("components.dropdown.values.switch.off") }}
														</div>
													</div>
													<div class="flex flex-row">
														<div class="w-48 text-slate-500">
															{{ t("views.product.fields.is_expirable") }}
														</div>
														<div class="flex-1 font-medium">
															{{ item.is_expirable ? t("components.dropdown.values.switch.on") : t("components.dropdown.values.switch.off") }}
														</div>
													</div>
												</div>
											</div>

											<!-- Product Units Table -->
											<div class="col-span-12">
												<div class="font-medium text-base mb-3 border-b pb-2">
													{{ t("views.product.detail.units_title") }}
												</div>
												<Table class="border">
													<Table.Thead>
														<Table.Tr>
															<Table.Th>{{ t("views.product.fields.code") }}</Table.Th>
															<Table.Th>{{ t("views.product.fields.unit_id") }}</Table.Th>
															<Table.Th>{{ t("views.product.fields.conversion_value") }}</Table.Th>
															<Table.Th class="text-right">{{ t("views.product.fields.price") }}</Table.Th>
															<Table.Th class="text-right">{{ t("views.product.fields.point") }}</Table.Th>
															<Table.Th class="text-center">{{ t("views.product.fields.is_manufacturer_sku") }}</Table.Th>
														</Table.Tr>
													</Table.Thead>
													<Table.Tbody>
														<Table.Tr v-for="unit in item.product_units" :key="unit.ulid">
															<Table.Td>{{ unit.code }}</Table.Td>
															<Table.Td class="font-medium">{{ unit.unit.name }}</Table.Td>
															<Table.Td>
																<div class="flex flex-col">
																	<span>{{ formatCurrency(unit.conversion_value) }}</span>
																	<span v-if="unit.conversion_value > 1" class="text-xs text-slate-500">
																		1 {{ unit.unit.name }} = {{ formatCurrency(unit.conversion_value) }} {{ item.product_units[0].unit.name }}
																	</span>
																	<span v-else class="text-xs text-slate-500">{{ t("views.product.fields.base_unit") }}</span>
																</div>
															</Table.Td>
															<Table.Td class="text-right">
																<div class="flex flex-col">
																	<span>{{ formatCurrency(unit.price) }}</span>
																	<span v-if="unit.conversion_value > 1 && unit.price > 0" class="text-xs text-slate-500 whitespace-nowrap">
																		{{ t("views.product.fields.base_unit_price") }}: {{ formatCurrency((unit.price / unit.conversion_value).toFixed(2)) }}
																	</span>
																</div>
															</Table.Td>
															<Table.Td class="text-right">{{ unit.point }}</Table.Td>
															<Table.Td class="text-center">
																<Lucide v-if="unit.is_manufacturer_sku" icon="CheckCircle" class="text-success w-4 h-4 mx-auto" />
																<Lucide v-else icon="X" class="text-danger w-4 h-4 mx-auto" />
															</Table.Td>
														</Table.Tr>
													</Table.Tbody>
												</Table>
											</div>
										</div>
									</Table.Td>
								</Table.Tr>
							</template>
						</Table.Tbody>
					</Table>
				</template>
			</DataList>
		</div>
	</div>
	<Dialog
		:open="deleteModalShow"
		@close="
			() => {
				deleteModalShow = false;
			}
		"
	>
		<Dialog.Panel>
			<div class="p-5 text-center">
				<Lucide icon="XCircle" class="w-16 h-16 mx-auto mt-3 text-danger" />
				<div class="mt-5 text-3xl">
					{{ t("components.delete-modal.title") }}
				</div>
				<div class="mt-2 text-slate-500">
					{{ t("components.delete-modal.desc_1") }}
					<br />
					{{ t("components.delete-modal.desc_2") }}
				</div>
			</div>
			<div class="px-5 pb-8 text-center">
				<Button
					type="button"
					variant="outline-secondary"
					@click="
						() => {
							deleteModalShow = false;
						}
					"
					class="w-24 mr-1"
				>
					{{ t("components.buttons.cancel") }}
				</Button>
				<Button type="button" variant="danger" class="w-24" @click="confirmDelete">
					{{ t("components.buttons.delete") }}
				</Button>
			</div>
		</Dialog.Panel>
	</Dialog>
</template>
