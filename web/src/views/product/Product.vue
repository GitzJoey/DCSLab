<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.product.table.title')" :data="productList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
           <template v-slot:table="tableProps">
                <table class="table table-report -mt-2" aria-describedby="">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">{{ t('views.product.table.cols.code') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.product.table.cols.name') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.product.table.cols.brand') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.product.table.cols.status') }}</th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="tableProps.dataList !== undefined" v-for="(item, itemIdx) in tableProps.dataList.data">
                            <tr :class="{ 'intro-x':true, 'line-through':item.status === 'DELETED' }">
                                <td>{{ item.code }}</td>
                                <td><a href="" @click.prevent="toggleDetail(itemIdx)" class="hover:animate-pulse">{{ item.name }}</a></td>
                                <td>{{ item.brand !== null ? item.brand.name : '' }}</td>
                                <td>
                                    <CheckCircleIcon v-if="item.status === 'ACTIVE'" />
                                    <XIcon v-if="item.status === 'INACTIVE'" />
                                    <XIcon v-if="item.status === 'DELETED'" />
                                </td>
                                <td class="table-report__action w-12">
                                    <div class="flex justify-center items-center">
                                        <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content="t('components.data-list.view')" @click.prevent="showSelected(itemIdx)">
                                            <InfoIcon class="w-4 h-4" />
                                        </Tippy>
                                        <template v-if="item.status !== 'DELETED'">
                                            <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content="t('components.data-list.edit')" @click.prevent="editSelected(itemIdx)">
                                                <CheckSquareIcon class="w-4 h-4" />
                                            </Tippy>
                                            <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content="t('components.data-list.delete')" @click.prevent="deleteSelected(itemIdx)">
                                                <Trash2Icon class="w-4 h-4 text-danger" />
                                            </Tippy>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                            <tr :class="{'intro-x':true, 'hidden transition-all': expandDetail !== itemIdx}">
                                <td colspan="5">
                                    <!-- #region Code -->
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.code') }}</div>
                                        <div class="flex-1">{{ item.code }}</div>
                                    </div>
                                    <!-- #endregion -->
                                    
                                    <!-- #region Product Group -->
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.product_group_id') }}</div>
                                        <div class="flex-1">{{ item.product_group ? item.product_group.name : '' }}</div>
                                    </div>
                                    <!-- #endregion -->

                                    <!-- #region Brand -->
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.brand_id') }}</div>
                                        <div class="flex-1">{{ item.brand.name }}</div>
                                    </div>
                                    <!-- #endregion -->

                                    <!-- #region Name -->
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.name') }}</div>
                                        <div class="flex-1">{{ item.name }}</div>
                                    </div>
                                    <!-- #endregion -->

                                    <!-- #region Product Units -->
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.units.title') }}</div>

                                        <div class="mb-3">
                                            <div class="grid grid-cols-10 mb-3 bg-gray-700 dark:bg-dark-1 gap-2">
                                                <div class="text-white p-3 font-bold col-span-2">{{ t('views.product.fields.units.table.cols.code') }}</div>
                                                <div class="text-white p-3 font-bold col-span-2">{{ t('views.product.fields.units.table.cols.unit') }}</div>
                                                <div class="text-white p-3 font-bold col-span-2 text-right">{{ t('views.product.fields.units.table.cols.conversion_value') }}</div>
                                                <div class="flex justify-center text-white p-3 col-span-2 font-bold">{{ t('views.product.fields.units.table.cols.is_base') }}</div>
                                                <div class="flex justify-center text-white p-3 col-span-2 font-bold">{{ t('views.product.fields.units.table.cols.is_primary') }}</div>
                                            </div>

                                            <div class="grid grid-cols-10 gap-2 mb-2" v-for="(subItem, subItemIdx) in item.product_units">
                                                <div class="col-span-2">{{ subItem.code }}</div>
                                                <div class="col-span-2">{{ subItem.unit.name }}</div>
                                                <div class="col-span-2 text-right">{{ subItem.conversion_value }}</div>
                                                <div class="col-span-2">
                                                    <span v-if="subItem.is_base === true">{{ t('components.switch.on') }}</span>
                                                    <span v-if="subItem.is_base === false">{{ t('components.switch.off') }}</span>
                                                </div>
                                                <div class="col-span-2">
                                                    <span v-if="subItem.is_primary_unit === true">{{ t('components.switch.on') }}</span>
                                                    <span v-if="subItem.is_primary_unit === false">{{ t('components.switch.off') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- #endregion -->

                                    <!-- #region Product Type -->
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.product_type') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.product_type === 'RAW_MATERIAL'">{{ t('components.dropdown.values.productTypeDDL.raw') }}</span>
                                            <span v-if="item.product_type === 'WORK_IN_PROGRESS'">{{ t('components.dropdown.values.productTypeDDL.wip') }}</span>
                                            <span v-if="item.product_type === 'FINISHED_GOODS'">{{ t('components.dropdown.values.productTypeDDL.fg') }}</span>
                                        </div>
                                    </div>
                                    <!-- #endregion -->

                                    <!-- #region Taxable Supply -->
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.taxable_supply') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.taxable_supply">{{ t('components.switch.on') }}</span>
                                            <span v-else>{{ t('components.switch.off') }}</span>
                                        </div>
                                    </div>
                                    <!-- #endregion -->

                                    <!-- #region Standard Rated Supply -->
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.standard_rated_supply') }}</div>
                                        <div class="flex-1">{{ item.standard_rated_supply }}</div>
                                    </div>
                                    <!-- #endregion -->

                                    <!-- #region Price Include Vat -->
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.price_include_vat') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.price_include_vat">{{ t('components.switch.on') }}</span>
                                            <span v-else>{{ t('components.switch.off') }}</span>
                                        </div>
                                    </div>
                                    <!-- #endregion -->

                                    <!-- #region Point -->
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.point') }}</div>
                                        <div class="flex-1">{{ item.point }}</div>
                                    </div>
                                    <!-- #endregion -->

                                    <!-- #region Use Serial Number -->
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.use_serial_number') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.use_serial_number">{{ t('components.switch.on') }}</span>
                                            <span v-else>{{ t('components.switch.off') }}</span>
                                        </div>
                                    </div>
                                    <!-- #endregion -->

                                    <!-- #region Has Expiry Date -->
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.has_expiry_date') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.has_expiry_date">{{ t('components.switch.on') }}</span>
                                            <span v-else>{{ t('components.switch.off') }}</span>
                                        </div>
                                    </div>
                                    <!-- #endregion -->

                                    <!-- #region Remarks -->
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.remarks') }}</div>
                                        <div class="flex-1">{{ item.Remarks }}</div>
                                    </div>
                                    <!-- #endregion -->

                                    <!-- #region Status -->
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.status') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.status === 'ACTIVE'">{{ t('components.dropdown.values.statusDDL.active') }}</span>
                                            <span v-if="item.status === 'INACTIVE'">{{ t('components.dropdown.values.statusDDL.inactive') }}</span>
                                            <span v-if="item.status === 'DELETED'">{{ t('components.dropdown.values.statusDDL.deleted') }}</span>
                                        </div>
                                    </div>
                                    <!-- #endregion -->
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <Modal :show="deleteModalShow" @hidden="deleteModalShow = false">
                    <ModalBody class="p-0">
                        <div class="p-5 text-center">
                            <XCircleIcon class="w-16 h-16 text-danger mx-auto mt-3" />
                            <div class="text-3xl mt-5">{{ t('components.data-list.delete_confirmation.title') }}</div>
                            <div class="text-slate-600 mt-2">
                                {{ t('components.data-list.delete_confirmation.desc_1') }}<br />{{ t('components.data-list.delete_confirmation.desc_2') }}
                            </div>
                        </div>
                        <div class="px-5 pb-8 text-center">
                            <button type="button" class="btn btn-outline-secondary w-24 mr-1" @click="deleteModalShow = false">
                                {{ t('components.buttons.cancel') }}
                            </button>
                            <button type="button" class="btn btn-danger w-24" @click="confirmDelete">{{ t('components.buttons.delete') }}</button>
                        </div>
                    </ModalBody>
                </Modal>
            </template>
        </DataList>
    </div>

    <div class="intro-y box" v-if="mode !== 'list'">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.product.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.product.actions.edit') }}</h2>
        </div>
        <div class="loader-container">
            <VeeForm id="productForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                <div class="p-5">
                    <!-- #region Code -->
                    <div class="mb-3">
                        <label for="inputCode" class="form-label">{{ t('views.product.fields.code') }}</label>
                        <div class="flex items-center">
                            <VeeField id="inputCode" name="code" type="text" :class="{'form-control':true, 'border-danger': errors['code']}" :placeholder="t('views.product.fields.code')" :label="t('views.product.fields.code')" rules="required" @blur="reValidate(errors)" v-model="product.code" :readonly="product.code === '[AUTO]'" />
                            <button type="button" class="btn btn-secondary mx-1" @click="generateCode" v-show="mode === 'create'">{{ t('components.buttons.auto') }}</button>
                        </div>
                        <ErrorMessage name="code" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Product Group -->
                    <div class="mb-3">
                        <label class="form-label" for="product_group_id">{{ t('views.product.fields.product_group_id') }}</label>
                        <VeeField as="select" id="product_group_id" name="product_group_id" :class="{'form-control form-select':true, 'border-danger': errors['product_group_id']}" v-model="product.product_group.hId" :label="t('views.product.fields.product_group_id')" rules="required" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option :value="g.hId" v-for="g in productGroupDDL" v-bind:key="g.hId">{{ g.code }} - {{ g.name }}</option>
                        </VeeField>
                        <ErrorMessage name="product_group_id" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Brand -->
                    <div class="mb-3">
                        <label class="form-label" for="brand_id">{{ t('views.product.fields.brand_id') }}</label>
                        <VeeField as="select" id="brand_id" name="brand_id" :class="{'form-control form-select':true, 'border-danger': errors['brand_id']}" v-model="product.brand.hId" :label="t('views.product.fields.brand_id')" rules="required" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option :value="b.hId" v-for="b in brandDDL" v-bind:key="b.hId">{{ b.code }} - {{ b.name }}</option>
                        </VeeField>
                        <ErrorMessage name="brand_id" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Name -->
                    <div class="mb-3">
                        <label for="inputName" class="form-label">{{ t('views.product.fields.name') }}</label>
                        <VeeField id="inputName" name="name" type="text" :class="{'form-control':true, 'border-danger': errors['name']}" :placeholder="t('views.product.fields.name')" :label="t('views.product.fields.name')" rules="required" @blur="reValidate(errors)" v-model="product.name" />
                        <ErrorMessage name="name" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Product Units -->
                    <div class="mb-3">
                        <label for="inputUnit" class="form-label">{{ t('views.product.fields.units.title') }}</label>
                        <div class="grid grid-cols-9 mb-3 bg-gray-700 dark:bg-dark-1 gap-2">
                            <div class="text-white p-3 font-bold col-span-2">{{ t('views.product.fields.units.table.cols.code') }}</div>
                            <div class="text-white p-3 font-bold col-span-2">{{ t('views.product.fields.units.table.cols.unit') }}</div>
                            <div class="text-white p-3 font-bold col-span-2 text-right">{{ t('views.product.fields.units.table.cols.conversion_value') }}</div>
                            <div class="flex justify-center text-white p-3 font-bold">{{ t('views.product.fields.units.table.cols.is_base') }}</div>
                            <div class="flex justify-center text-white p-3 font-bold">{{ t('views.product.fields.units.table.cols.is_primary') }}</div>
                            <div class="text-white p-3 font-bold"></div>
                        </div>
                        <div class="grid grid-cols-9 gap-2 mb-2" v-for="(pu, puIdx) in product.product_units">
                            <!-- #region Code -->
                            <div class="col-span-2">
                                <input type="hidden" :name="'product_units_hId[' + puIdx + ']'" v-model="pu.hId" />
                                <div class="flex items-center">
                                    <VeeField type="text" :class="{'form-control': true, 'border-danger':errors['product_units_code[' + puIdx + ']']|errors['product_units_code.' + puIdx]}" v-model="pu.code" id="product_units_code" :name="'product_units_code[' + puIdx + ']'" :label="t('views.product.fields.units.table.cols.code') + ' ' + (puIdx+1)" rules="required" @blur="reValidate(errors)" :readonly="mode === 'create' && pu.code === '[AUTO]'" />
                                    <button type="button" class="btn btn-secondary mx-1" @click="generateCodeUnit(puIdx)" v-show="mode === 'create'">{{ t('components.buttons.auto') }}</button>
                                </div>
                                <ErrorMessage :name="'product_units_code[' + puIdx + ']'" class="text-danger" />
                                <ErrorMessage :name="'product_units_code.' + puIdx" class="text-danger" />
                            </div>
                            <!-- #endregion -->

                            <!-- #region Unit -->
                            <div class="col-span-2">
                                <VeeField as="select" :class="{'form-control form-select':true, 'border-danger':errors['product_units_unit_hId[' + puIdx + ']']|errors['product_units_unit_hId.' + puIdx]}" id="product_units_unit_hId" :name="'product_units_unit_hId[' + puIdx + ']'" :label="t('views.product.fields.units.table.cols.unit') + ' ' + (puIdx+1)" rules="required" @blur="reValidate(errors)" v-model="pu.unit.hId">
                                    <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                    <option :value="u.hId" v-for="u in unitDDL" v-bind:key="u.hId">{{ u.name }}</option>
                                </VeeField>
                                <ErrorMessage :name="'product_units_unit_hId[' + puIdx + ']'" class="text-danger" />
                                <ErrorMessage :name="'product_units_unit_hId.' + puIdx" class="text-danger" />
                            </div>
                            <!-- #endregion -->

                            <!-- #region Conv Value -->
                            <div class="col-span-2">
                                <VeeField type="text" :class="{'form-control text-right':true, 'border-danger':errors['product_units_conv_value[' + puIdx +']']|errors['product_units_conv_value.' + puIdx]}" v-model="pu.conversion_value" id="product_units_conv_value" :name="'product_units_conv_value[' + puIdx +']'" :label="t('views.product.fields.units.table.cols.conversion_value') + ' ' + (puIdx+1)" rules="required" @focus="$event.target.select()" :readonly="pu.is_base" />
                                <ErrorMessage :name="'product_units_conv_value[' + puIdx +']'" class="text-danger" />
                                <ErrorMessage :name="'product_units_conv_value.' + puIdx" class="text-danger" />
                            </div>
                            <!-- #endregion -->

                            <!-- #region Is Base -->
                            <div class="flex items-center justify-center">
                                <input id="inputIsBase" class="form-check-input" type="checkbox" v-model="pu.is_base" :true-value="true" :false-value="false" @click="changeIsBase(puIdx)"> 
                                <input type="hidden" v-model="pu.is_base" :name="'product_units_is_base[' + puIdx + ']'" />
                            </div>
                            <!-- #endregion -->

                            <!-- #region Is Primary -->
                            <div class="flex items-center justify-center">
                                <input id="inputIsPrimary" class="form-check-input" type="checkbox" v-model="pu.is_primary_unit" :true-value="true" :false-value="false" @click="changeIsPrimary(puIdx)">
                                <input type="hidden" v-model="pu.is_primary_unit" :name="'product_units_is_primary_unit[' + puIdx + ']'" />
                            </div>
                            <!-- #endregion -->

                            <!-- #region Remarks -->
                            <input type="hidden" name="product_units_remarks[]" v-model="pu.remarks"/>
                            <!-- #endregion -->
                            
                            <div class="flex items-center justify-center">
                                <button class="btn btn-sm btn-secondary" v-if="puIdx !== 0" @click.prevent="deleteUnitSelected(puIdx)"><TrashIcon class="w-3 h-4" /></button>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-secondary w-24" @click.prevent="createNewUnit"><PlusIcon class="w-3 h-4" /></button>
                    </div>
                    <!-- #endregion -->

                    <!-- #region Product Type -->
                    <div class="mb-3">
                        <label for="product_type" class="form-label">{{ t('views.product.fields.product_type') }}</label>
                        <VeeField as="select" id="product_type" name="product_type" :class="{'form-control form-select':true, 'border-danger': errors['product_type']}" v-model="product.product_type" :label="t('views.product.fields.product_type')" rules="required" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option :value="pt.code" v-for="pt in productTypeDDL" v-bind:key="pt.code">{{ t(pt.name) }}</option>
                        </VeeField>
                        <ErrorMessage name="product_type" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Taxable Supply -->
                    <div class="mb-3">
                        <label for="inputTaxableSupply" class="form-label">{{ t('views.product.fields.taxable_supply') }}</label>
                        <div class="form-switch mt-2">
                            <input id="inputTaxableSupply" type="checkbox" class="form-check-input" name="taxable_supply" v-model="product.taxable_supply" :true-value="true" :false-value="false">
                        </div>
                    </div>
                    <!-- #endregion -->

                    <!-- #region Standard Rated Supply -->
                    <div class="mb-3">
                        <label for="inputStandardRatedSupply" class="form-label">{{ t('views.product.fields.standard_rated_supply') }}</label>
                        <VeeField id="inputStandardRatedSupply" name="standard_rated_supply" type="text" :class="{'form-control':true, 'border-danger': errors['standard_rated_supply']}" :placeholder="t('views.product.fields.standard_rated_supply')" :label="t('views.product.fields.standard_rated_supply')" rules="required|numeric|max:100" @blur="reValidate(errors)" v-model="product.standard_rated_supply" />
                        <ErrorMessage name="standard_rated_supply" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Price Include VAT -->
                    <div class="mb-3">
                        <label for="inputPriceIncludeVAT" class="form-label">{{ t('views.product.fields.price_include_vat') }}</label>
                        <div class="form-switch mt-2">
                            <input id="inputPriceIncludeVAT" type="checkbox" class="form-check-input" name="price_include_vat" v-model="product.price_include_vat" :true-value="true" :false-value="false">
                        </div>
                    </div>
                    <!-- #endregion -->

                    <!-- #region Point -->
                    <div class="mb-3">
                        <label for="inputPoint" class="form-label">{{ t('views.product.fields.point') }}</label>
                        <VeeField id="inputPoint" name="point" type="text" :class="{'form-control':true, 'border-danger': errors['point']}" :placeholder="t('views.product.fields.point')" :label="t('views.product.fields.point')" rules="required|numeric|max_value:1000" v-model="product.point" />
                        <ErrorMessage name="point" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Use Serial Number -->
                    <div class="mb-3">
                        <label for="inputUseSerialNumber" class="form-label">{{ t('views.product.fields.use_serial_number') }}</label>
                        <div class="form-switch mt-2">
                            <input id="inputUseSerialNumber" type="checkbox" class="form-check-input" name="use_serial_number" v-model="product.use_serial_number" :true-value="true" :false-value="false">
                        </div>
                    </div>
                    <!-- #endregion -->

                    <!-- #region Has Expiry Date -->
                    <div class="mb-3">
                        <label for="inputHasExpiryDate" class="form-label">{{ t('views.product.fields.has_expiry_date') }}</label>
                        <div class="form-switch mt-2">
                            <input id="inputHasExpiryDate" type="checkbox" class="form-check-input" name="has_expiry_date" v-model="product.has_expiry_date" :true-value="true" :false-value="false">
                        </div>
                    </div>
                    <!-- #endregion -->

                    <!-- #region Remarks -->
                    <div class="mb-3">
                        <label for="inputRemarks" class="form-label">{{ t('views.product.fields.remarks') }}</label>
                        <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="t('views.product.fields.remarks')" v-model="product.remarks" rows="3"></textarea>
                    </div>
                    <!-- #endregion -->

                    <!-- #region Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label">{{ t('views.product.fields.status') }}</label>
                        <VeeField as="select" id="status" name="status" :class="{'form-control form-select':true, 'border-danger': errors['status']}" v-model="product.status" rules="required" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </VeeField>
                        <ErrorMessage name="status" class="text-danger" />
                    </div>
                    <!-- #endregion -->
                </div>
                <div class="pl-5" v-if="mode === 'create' || mode === 'edit'">
                    <button type="submit" class="btn btn-primary w-24 mr-3">{{ t('components.buttons.save') }}</button>
                    <button type="button" class="btn btn-secondary" @click="handleReset(); resetAlertErrors()">{{ t('components.buttons.reset') }}</button>
                </div>
            </VeeForm>
            <div class="loader-overlay" v-if="loading"></div>
        </div>
        <hr/>
        <div>
            <button type="button" class="btn btn-secondary w-15 m-3" @click="backToList">{{ t('components.buttons.back') }}</button>
        </div>
    </div>
</template>

<script setup>
//#region Imports
import { onMounted, onUnmounted, ref, computed, watch } from "vue";
import axios from "@/axios";
import { useI18n } from "vue-i18n";
import route from "@/ziggy";
import dom from "@left4code/tw-starter/dist/js/dom";
import { useUserContextStore } from "@/stores/user-context";
import DataList from "@/global-components/data-list/Main.vue";
import AlertPlaceholder from "@/global-components/alert-placeholder/Main.vue";
import { getCachedDDL, setCachedDDL } from "@/mixins";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - Pinia
const userContextStore = useUserContextStore();
const selectedUserCompany = computed(() => userContextStore.selectedUserCompany );
//#endregion

//#region Data - UI
const mode = ref('list');
const loading = ref(false);
const alertErrors = ref([]);
const deleteId = ref('');
const deleteModalShow = ref(false);
const expandDetail = ref(null);
//#endregion

//#region Data - Views
const productList = ref({});
const product = ref({
    code: '',
    product_group: { 
        hId: '',
        name: '' 
    },
    brand: { 
        hId: '',
        name: '' 
    },
    name: '',
    product_units: [
        {
            hId: '',
            conversion_value: 0,
            unit: {
                hId: '',
                name: '',
            }
        }
    ],
    taxable_supply: false,
    standard_rated_supply: 0,
    price_include_vat: false,
    remarks: '',
    point: 0,
    is_use_serial: false,
    has_expiry_date: false,
    product_type: '',
    status: 'ACTIVE',
});
const statusDDL = ref([]);
const productGroupDDL = ref([]);
const brandDDL = ref([]);
const unitDDL = ref([]);
const productTypeDDL = ref([]);
//#endregion

//#region onMounted
onMounted(() => {
    if (selectedUserCompany.value.hId !== '') {
        getAllProducts({ page: 1 });
        getDDLSync();
    } else  {
        
    }

    getDDL();

    setMode();

    loading.value = false;
});

onUnmounted(() => {
    sessionStorage.removeItem('DCSLAB_LAST_ENTITY');
});
//#endregion

//#region Methods
const setMode = () => {
    if (sessionStorage.getItem('DCSLAB_LAST_ENTITY') !== null) createNew();
}

const getAllProducts = (args) => {
    productList.value = {};
    let companyId = selectedUserCompany.value.hId;
    
    if (args.search === undefined) args.search = '';
    if (args.paginate === undefined) args.paginate = 1;
    if (args.page === undefined) args.page = 1;
    if (args.perPage === undefined) args.perPage = 10;
    if (args.useCache === undefined) args.useCache = true;

    axios.get(route('api.get.db.product.product.list', {
        companyId: companyId,
        productCategory: 'PRODUCTS',
        search: args.search,
        paginate : 1,
        page: args.page,
        perPage: args.perPage,
        useCache: args.useCache
    })).then(response => {
        productList.value = response.data;
        loading.value = false;
    });
}

const getDDL = () => {
    if (getCachedDDL('statusDDL') == null) {
        axios.get(route('api.get.db.common.ddl.list.statuses')).then(response => {
            statusDDL.value = response.data;
            setCachedDDL('statusDDL', response.data);
        });    
    } else {
        statusDDL.value = getCachedDDL('statusDDL');
    }

    if (getCachedDDL('productTypeDDL') == null) {
        axios.get(route('api.get.db.product.common.list.product_type', {
            type: 'product'
        })).then(response => {
            productTypeDDL.value = response.data;
            setCachedDDL('productTypeDDL', response.data);
        });    
    } else {
        productTypeDDL.value = getCachedDDL('productTypeDDL');
    }
}

const getDDLSync = () => {
    axios.get(route('api.get.db.product.product_group.list', {
            companyId: selectedUserCompany.value.hId,
            search:'',
            category: 'PRODUCTS',
            paginate: false
        })).then(response => {
            productGroupDDL.value = response.data;
    });

    axios.get(route('api.get.db.product.brand.list', {
        companyId: selectedUserCompany.value.hId,
        search:'',
        paginate: false
    })).then(response => {
        brandDDL.value = response.data;
    });

    axios.get(route('api.get.db.product.unit.list', {
        companyId: selectedUserCompany.value.hId,
        search:'',
        category: 'PRODUCTS',
        paginate: false
    })).then(response => {
        unitDDL.value = response.data;
    });
}

const onSubmit = (values, actions) => {
    loading.value = true;

    var formData = new FormData(dom('#productForm')[0]); 
    formData.append('company_id', selectedUserCompany.value.hId);
    
    if (mode.value === 'create') {
        axios.post(route('api.post.db.product.product.save'), formData).then(response => {
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else if (mode.value === 'edit') {
        axios.post(route('api.post.db.product.product.edit', product.value.uuid), formData).then(response => {
            actions.resetForm();
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else { }
}

const handleError = (e, actions) => {
    //Laravel Validations
    if (e.response.data.errors !== undefined && Object.keys(e.response.data.errors).length > 0) {
        for (var key in e.response.data.errors) {
            for (var i = 0; i < e.response.data.errors[key].length; i++) {
                actions.setFieldError(key, e.response.data.errors[key][i]);
            }
        }
        alertErrors.value = e.response.data.errors;
    } else {
        //Catch From Controller
        alertErrors.value = {
            controller: e.response.status + ' ' + e.response.statusText +': ' + e.response.data.message
        };
    }
}

const invalidSubmit = (e) => {
    alertErrors.value = e.errors;
    if (dom('.border-danger').length !== 0) dom('.border-danger')[0].scrollIntoView({ behavior: "smooth" });
}

const reValidate = (errors) => {
    alertErrors.value = errors;
}

const emptyProduct = () => {
    return {
        code: '[AUTO]',
        product_group: { 
            hId: '',
            name: '' 
        },
        brand: { 
            hId: '',
            name: '' 
        },
        name: '',
        product_units: [
            {
                hId: '',
                code: '[AUTO]',
                is_base: true,
                conversion_value: 1,
                is_primary_unit: true,
                remarks: '',
                unit: {
                    hId: '',
                    name: '',
                }
            }
        ],
        product_type: '',
        taxable_supply: false,
        standard_rated_supply: 0,
        price_include_vat: false,
        point: 0,
        remarks: '',
        use_serial_number: false,
        has_expiry_date: false,
        status: 'ACTIVE',
    }
}

const resetAlertErrors = () => {
    alertErrors.value = [];
}

const createNew = () => {
    mode.value = 'create';
    
    if (sessionStorage.getItem('DCSLAB_LAST_ENTITY') !== null) {
        product.value = JSON.parse(sessionStorage.getItem('DCSLAB_LAST_ENTITY'));
        sessionStorage.removeItem('DCSLAB_LAST_ENTITY');
    } else {
        product.value = emptyProduct();
    }
}

const createNewUnit = () => {
    let product_unit = {
        hId: '',
        code: '[AUTO]',
        conversion_value: 0,
        is_base: false,
        is_primary_unit: false,
        unit: { hId: '' }
    };

    product.value.product_units.push(product_unit);
}

const onDataListChange = ({search, paginate, page, perPage, useCache}) => {
    getAllProducts({search, paginate, page, perPage, useCache});
}

const editSelected = (index) => {
    mode.value = 'edit';
    product.value = productList.value.data[index];

    if (product.value.product_group === null) {
        product.value.product_group = {
            hId: ''
        };
    }
}

const deleteSelected = (index) => {
    deleteId.value = productList.value.data[index].uuid;
    deleteModalShow.value = true;
}

const deleteUnitSelected = (index) => {
    product.value.product_units.splice(index, 1);
}

const confirmDelete = () => {
    deleteModalShow.value = false;
    axios.post(route('api.post.db.product.product.delete', deleteId.value)).then(response => {
        backToList();
    }).catch(e => {
        alertErrors.value = e.response.data;
    }).finally(() => {

    });
}

const showSelected = (index) => {
    toggleDetail(index);
}

const backToList = () => {
    resetAlertErrors();
    sessionStorage.removeItem('DCSLAB_LAST_ENTITY');

    mode.value = 'list';
    getAllProducts({
        page: productList.value.meta.current_page, 
        perPage: productList.value.meta.per_page
    });
}

const toggleDetail = (idx) => {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
}

const generateCode = () => {
    if (product.value.code === '[AUTO]') product.value.code = '';
    else  product.value.code = '[AUTO]'
}

const generateCodeUnit = (idx) => {
    if (product.value.product_units[idx].code === '[AUTO]') product.value.product_units[idx].code = '';
    else  product.value.product_units[idx].code = '[AUTO]'
}

const changeIsBase = (idx) => {
    let checked_state = product.value.product_units[idx].is_base === true ? true:false;

    if (!checked_state) {
        for (let i = 0; i < product.value.product_units.length; i++) {
            if (i === idx) {
                product.value.product_units[i].conversion_value = 1;
            }
            product.value.product_units[i].is_base = false;
        }
    } else {

    }
}

const changeIsPrimary = (idx) => {
    let checked_state = product.value.product_units[idx].is_primary_unit === true ? true:false;
    
    if (!checked_state) {
        for (let i = 0; i < product.value.product_units.length; i++) {
            if (i === idx) continue;
            product.value.product_units[i].is_primary_unit = false;
        }
    } else {
        
    }
}
//#endregion

//#region Computed
//#endregion

//#region Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value.hId !== '') {
        getAllProducts({ page: 1 });
        getDDLSync();
    }
}, { deep: true });

watch(product, (newV) => {
    if (mode.value == 'create') sessionStorage.setItem('DCSLAB_LAST_ENTITY', JSON.stringify(newV));
}, { deep: true });
//#endregion
</script>