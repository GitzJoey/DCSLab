<template>
    <div :class="{'block block-bordered block-themed block-mode-loading-refresh':true, 'block-mode-loading':this.loading, 'block-mode-fullscreen':this.fullscreen, 'block-mode-hidden':this.contentHidden}">
        <div class="block-header bg-gray-dark">
            <h3 class="block-title" v-if="this.mode === 'list'"><strong>{{ $t('table.title') }}</strong></h3>
            <h3 class="block-title" v-if="this.mode === 'create'"><strong>{{ $t('actions.create') }}</strong></h3>
            <h3 class="block-title" v-if="this.mode === 'edit'"><strong>{{ $t('actions.edit') }}</strong></h3>
            <h3 class="block-title" v-if="this.mode === 'show'"><strong>{{ $t('actions.show') }}</strong></h3>
            <div class="block-options">
                <button type="button" class="btn-block-option" v-on:click="toggleFullScreen">
                    <i class="icon icon-size-actual" v-if="this.fullscreen === true"></i>
                    <i class="icon icon-size-fullscreen" v-if="this.fullscreen === false"></i>
                </button>
                <button type="button" class="btn-block-option" v-on:click="refreshList" v-if="this.mode === 'list'">
                    <i class="icon icon-refresh"></i>
                </button>
                <button type="button" class="btn-block-option" v-on:click="toggleContentHidden">
                    <i class="icon icon-arrow-down" v-if="this.contentHidden === true"></i>
                    <i class="icon icon-arrow-up" v-if="this.contentHidden === false"></i>
                </button>
            </div>
        </div>
        <div class="block-content">
            <transition name="fade">
                <div id="error" v-if="this.mode === 'error'">
                    <div class="alert alert-warning alert-dismissable" role="alert" v-if="this.listErrors.length !== 0">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close" v-on:click="resetListErrors">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h3 class="alert-heading font-size-h5 font-w700 mb-5"><i class="fa fa-warning"></i>&nbsp;{{ $t('errors.warning') }}</h3>
                        <ul>
                            <li v-for="e in this.listErrors">{{ e }}</li>
                        </ul>
                    </div>
                </div>
            </transition>
            <transition name="fade">
                <div id="list" v-if="this.mode === 'list'">
                                        <div class="alert alert-warning alert-dismissable" role="alert" v-if="this.tableListErrors.length !== 0">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close" v-on:click="resetTableListErrors">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h3 class="alert-heading font-size-h5 font-w700 mb-5"><i class="fa fa-warning"></i>&nbsp;{{ $t('errors.warning') }}</h3>
                        <ul>
                            <li v-for="e in this.tableListErrors">{{ e }}</li>
                        </ul>
                    </div>
                    <div class="alert alert-warning alert-dismissable" role="alert" v-if="this.tableListErrors.length !== 0">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close" v-on:click="resetTableListErrors">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h3 class="alert-heading font-size-h5 font-w700 mb-5"><i class="fa fa-warning"></i>&nbsp;{{ $t('errors.warning') }}</h3>
                        <ul>
                            <li v-for="e in this.tableListErrors">{{ e }}</li>
                        </ul>
                    </div>
                    <table class="table table-vcenter">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ $t("table.cols.code") }}</th>
                                <th>{{ $t("table.cols.product_group_id") }}</th>
                                <th>{{ $t("table.cols.brand_id") }}</th>
                                <th>{{ $t("table.cols.name") }}</th>
                                <th>{{ $t("table.cols.tax_status") }}</th>
                                <th>{{ $t("table.cols.product_type") }}</th>
                                <th>{{ $t("table.cols.status") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(c, cIdx) in productList.data">
                                <td>{{ c.code }}</td>
                                <td>{{ c.product_group.name }}</td>
                                <td>{{ c.brand.name }} </td>
                                <td>{{ c.name }}</td>
                                <td>
                                    <span v-if="c.tax_status === 1">{{ $t('tax_statusDDL.notax') }}</span>
                                    <span v-if="c.tax_status === 2">{{ $t('tax_statusDDL.excudetax') }}</span>
                                    <span v-if="c.tax_status === 3">{{ $t('tax_statusDDL.includetax') }}</span>
                                </td>
                                <td>
                                    <span v-if="c.product_type === 1">{{ $t('product_typeDDL.rawmaterial') }}</span>
                                    <span v-if="c.product_type === 2">{{ $t('product_typeDDL.wip') }}</span>
                                    <span v-if="c.product_type === 3">{{ $t('product_typeDDL.finishedgoods') }}</span>
                                </td>
                                <td>
                                    <span v-if="c.status === 1">{{ $t('statusDDL.active') }}</span>
                                    <span v-if="c.status === 0">{{ $t('statusDDL.inactive') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.show')" v-on:click="showSelected(cIdx)">
                                            <i class="fa fa-info"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.edit')" v-on:click="editSelected(cIdx)">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.delete')" v-on:click="deleteSelected(cIdx)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-end">
                            <li :class="{'page-item':true, 'disabled': this.productList.prev_page_url == null}">
                                <a class="page-link" href="#" aria-label="Previous" v-on:click="onPaginationChangePageProduct('prev')">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li :class="{'page-item':true, 'disabled': this.productList.next_page_url == null}">
                                <a class="page-link" href="#" aria-label="Next" v-on:click="onPaginationChangePageProduct('next')">
                                    <span aria-hidden="true">&raquo;</span>
                                    <span class="sr-only">Next</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </transition>
            <transition name="fade">
                <div id="crud" v-if="this.mode !== 'list' && this.mode !== 'error'">
                    <Form id="productForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
                        <div class="alert alert-warning alert-dismissable" role="alert" v-if="Object.keys(errors).length !== 0">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close" v-on:click="handleReset">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h3 class="alert-heading font-size-h5 font-w700 mb-5"><i class="fa fa-warning"></i>&nbsp;{{ $t('errors.warning') }}</h3>
                            <ul>
                                <li v-for="e in errors">{{ e }}</li>
                            </ul>
                        </div>
                        <div class="form-group row">
                            <label for="inputCode" class="col-2 col-form-label">{{ $t('fields.code') }}</label>
                            <div class="col-md-10">
                                <Field id="inputCode" name="code" as="input" :class="{'form-control':true, 'is-invalid': errors['code']}" :placeholder="$t('fields.code')" :label="$t('fields.code')" v-model="product.code" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="code" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ product.code }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="product_group_id">{{ $t('fields.product_group_id') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="product_group_id" name="product_group_id" v-model="product.product_group.hId" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value="0">{{ $t('placeholder.please_select') }}</option>
                                    <option :value="c.hId" v-for="c in this.groupDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    {{ product.product_group.name }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="brand_id">{{ $t('fields.brand_id') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="brand_id" name="brand_id" v-model="product.brand.hId" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value="0">{{ $t('placeholder.please_select') }}</option>
                                    <option :value="c.hId" v-for="c in this.brandDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    {{ product.brand.name }}
                                </div>            
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputName" class="col-2 col-form-label">{{ $t('fields.name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="product.name" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ product.name }}</div>
                            </div>
                        </div>

                        <div :class="{'block block-bordered block-themed block-mode-loading-refresh':true, 'block-mode-loading':this.loading, 'block-mode-fullscreen':this.fullscreen, 'block-mode-hidden':this.contentHidden}">
                            <div class="block-header bg-gray-dark">
                                <h3 class="block-title"><strong>Product Unit</strong></h3>

                                <div class="block-options">
                                    <button type="button" class="btn-block-option" v-on:click="toggleFullScreen">
                                        <i class="icon icon-size-actual" v-if="this.fullscreen === true"></i>
                                        <i class="icon icon-size-fullscreen" v-if="this.fullscreen === false"></i>
                                    </button>
                                    <button type="button" class="btn-block-option" v-on:click="refreshList" v-if="this.mode === 'list'">
                                        <i class="icon icon-refresh"></i>
                                    </button>
                                    <button type="button" class="btn-block-option" v-on:click="toggleContentHidden">
                                        <i class="icon icon-arrow-down" v-if="this.contentHidden === true"></i>
                                        <i class="icon icon-arrow-up" v-if="this.contentHidden === false"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="block-content">
                                <table class="table table-vcenter">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Code</th>
                                            <th>Unit</th>
                                            <th>Conversion Value</th>
                                            <th>Is Base</th>
                                            <th>Primary Unit</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <input type="hidden" v-model="product.product_unit">
                                        <tr v-for="(c, cIdx) in product.product_unit">
                                            <input type="hidden" v-model="c.hId" name="product_unit_hId[]"/>

                                            <!-- Code -->
                                            <td>
                                                <input type="text" class="form-control" v-model="c.code" id="product_unit_code" name="product_unit_code[]"/>
                                            </td>                                          

                                            <!-- Unit -->
                                            <td>
                                                <select class="form-control" id="unit_id" name="unit_id[]" v-model="c.unit.hId">
                                                    <option value="0">{{ $t('placeholder.please_select') }}</option>
                                                    <option :value="c.hId" v-for="c in this.unitDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                                </select>
                                                <div class="form-control-plaintext" v-show="this.mode === 'show_product'">
                                                    {{ c.unit.name }}
                                                </div>
                                            </td>

                                            <!-- Conversion Value -->
                                            <td>
                                                <input type="text" class="form-control" v-model="c.conversion_value" id="conv_value" name="conv_value[]"/>
                                            </td>

                                            <!-- Is Base -->
                                            <td>
                                                <label class="css-control css-control-primary css-checkbox">
                                                    <input type="checkbox" class="css-control-input" id="is_base" v-model="c.is_base" true-value="1" false-value="0">
                                                    <span class="css-control-indicator"></span>
                                                </label>
                                                <input type="hidden" v-model="c.is_base" name="is_base[]"/>
                                            </td>

                                            <!-- Is Primary Unit -->
                                            <td>
                                                <label class="css-control css-control-primary css-checkbox">
                                                    <input type="checkbox" class="css-control-input" id="is_primary_unit" v-model="c.is_primary_unit" true-value="1" false-value="0">
                                                    <span class="css-control-indicator"></span>
                                                </label>
                                                <input type="hidden" v-model="c.is_primary_unit" name="is_primary_unit[]"/>
                                            </td>

                                            <!-- Delete Button -->
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.delete')" v-on:click="deleteSelectedProductUnit(cIdx)">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="block-content block-content-full block-content-sm bg-body-light font-size-sm">
                                <button type="button" class="btn btn-primary min-width-125" data-toggle="click-ripple" v-on:click="createNewProductUnit"><i class="fa fa-plus-square"></i></button>
                            </div> 
                        </div>
                        <div class="form-group row">
                            <label for="tax_status" class="col-2 col-form-label">{{ $t('fields.tax_status') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <select class="form-control" id="tax_status" name="tax_status" v-model="product.tax_status" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value="">{{ $t('placeholder.please_select') }}</option>
                                    <option value="1">{{ $t('tax_statusDDL.notax') }}</option>
                                    <option value="2">{{ $t('tax_statusDDL.excudetax') }}</option>
                                    <option value="3">{{ $t('tax_statusDDL.includetax') }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    <span v-if="product.tax_status === 1">{{ $t('tax_statusDDL.notax') }}</span>
                                    <span v-if="product.tax_status === 2">{{ $t('tax_statusDDL.excudetax') }}</span>
                                    <span v-if="product.tax_status === 3">{{ $t('tax_statusDDL.includetax') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="supplier_id">{{ $t('fields.supplier_id') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="supplier_id" name="supplier_id" v-model="product.defaultSupplier" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value="0">{{ $t('placeholder.please_select') }}</option>
                                    <option :value="c.hId" v-for="c in this.supplierDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    <!-- {{ product.supplier.name }} -->
                                    {{ product.defaultSupplier }}
                                </div>            
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputRemarks" class="col-2 col-form-label">{{ $t('fields.remarks') }}</label>
                            <div class="col-md-10">
                                <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="$t('fields.remarks')" v-model="product.remarks" v-show="this.mode === 'create' || this.mode === 'edit'" rows="3"></textarea>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ product.remarks }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputPoint" class="col-2 col-form-label">{{ $t('fields.point') }}</label>
                            <div class="col-md-10">
                                <Field id="inputPoint" name="point" as="input" :class="{'form-control':true, 'is-invalid': errors['point']}" :placeholder="$t('fields.point')" :label="$t('fields.point')" v-model="product.point" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ product.point }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="use_serial_number" class="col-2 col-form-label">{{ $t('fields.use_serial_number') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">
                                    <span v-show="this.mode === 'create' || this.mode === 'edit'">
                                        <input type="checkbox" class="css-control-input" id="use_serial_number" name="use_serial_number" v-model="product.use_serial_number" true-value="1" false-value="0">
                                        <span class="css-control-indicator"></span>
                                    </span>
                                    <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                        <span v-if="product.use_serial_number === 1">{{ $t('use_serial_number.active') }}</span>
                                        <span v-if="product.use_serial_number === 0">{{ $t('use_serial_number.inactive') }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="has_expiry_date" class="col-2 col-form-label">{{ $t('fields.has_expiry_date') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">
                                    <span v-show="this.mode === 'create' || this.mode === 'edit'">
                                        <input type="checkbox" class="css-control-input" id="has_expiry_date" name="has_expiry_date" v-model="product.has_expiry_date" true-value="1" false-value="0">
                                        <span class="css-control-indicator"></span>
                                    </span>
                                    <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                        <span v-if="product.has_expiry_date === 1">{{ $t('has_expiry_date.active') }}</span>
                                        <span v-if="product.has_expiry_date === 0">{{ $t('has_expiry_date.inactive') }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="product_type" class="col-2 col-form-label">{{ $t('fields.product_type') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <select class="form-control" id="product_type" name="product_type" v-model="product.product_type" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value="">{{ $t('placeholder.please_select') }}</option>
                                    <option value="1">{{ $t('product_typeDDL.rawmaterial') }}</option>
                                    <option value="2">{{ $t('product_typeDDL.wip') }}</option>
                                    <option value="3">{{ $t('product_typeDDL.finishedgoods') }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    <span v-if="product.product_type === 1">{{ $t('product_typeDDL.rawmaterial') }}</span>
                                    <span v-if="product.product_type === 2">{{ $t('product_typeDDL.wip') }}</span>
                                    <span v-if="product.product_type === 3">{{ $t('product_typeDDL.finishedgoods') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="status" class="col-2 col-form-label">{{ $t('fields.status') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <select class="form-control" id="status" name="status" v-model="product.status" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value='1'>{{ $t('statusDDL.active') }}</option>
                                    <option value='0'>{{ $t('statusDDL.inactive') }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    <span v-if="product.status === 1">{{ $t('statusDDL.active') }}</span>
                                    <span v-if="product.status === 0">{{ $t('statusDDL.inactive') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">    
                            <div class="col">
                                <div v-if="this.mode === 'create' || this.mode === 'edit'">
                                    <button type="button" class="btn btn-secondary min-width-125 float-right ml-2" data-toggle="click-ripple" v-on:click="handleReset">{{ $t("buttons.reset") }}</button>
                                    <button type="submit" class="btn btn-primary min-width-125 float-right ml-2" data-toggle="click-ripple">{{ $t("buttons.submit") }}</button>&nbsp;&nbsp;&nbsp;
                                </div>
                            </div>
                        </div>
                    </Form>
                </div>
            </transition>
        </div>
        <div class="block-content block-content-full block-content-sm bg-body-light font-size-sm">
            <div v-if="this.mode === 'list'">
                <button type="button" class="btn btn-primary min-width-125" data-toggle="click-ripple" v-on:click="createNew"><i class="fa fa-plus-square"></i></button>
            </div>
            <div v-if="this.mode !== 'list' && this.mode !== 'error'">
                <button type="button" class="btn btn-secondary min-width-125" data-toggle="click-ripple" v-on:click="backToList">{{ $t("buttons.back") }}</button>
            </div>
        </div>
    </div>
</template>

<script>
import { Form, Field, ErrorMessage, defineRule, configure } from "vee-validate";
import { required } from '@vee-validate/rules';
import { localize, setLocale } from '@vee-validate/i18n';
import en from '@vee-validate/i18n/dist/locale/en.json';
import id from '@vee-validate/i18n/dist/locale/id.json';
import { find } from 'lodash';

configure({
    validateOnInput: true,
    generateMessage: localize({ en, id }),
})

setLocale(document.documentElement.lang);

defineRule('required', required);

export default {
    components: {
        Form, Field, ErrorMessage
    },
    setup() {
        const schema = {
            code: 'required',
            name: 'required',
        };

        return {
            schema
        };
    },
    data() {
        return {
            mode: '',
            loading: false,
            fullscreen: false,
            contentHidden: false,
            productList: [],
            product: {
                code: '',
                product_group: {hId: '0'},
                brand: {hId: '0'},   
                name: '',
                product_unit: [
                    {
                        hId: '',
                        conversion_value: '0',
                        unit: {hId: '0'}
                    }
                ],
                tax_status: '',
                defaultSupplier: '',
                supplier: {hId: '0'},
                remarks: '',
                point: '',
                use_serial_number: '',
                has_expiry_date: '',
                product_type: '',
                status: '',
            },
            groupDDL: [],
            brandDDL: [],
            unitDDL: [],
            supplierDDL: [],
            statusDDL: [],
            listErrors: [],
            tableListErrors: [],
        }
    },
    created() {
    },

    mounted() {
        this.mode = 'list';
        this.getAllProduct(1);
        this.getAllProductGroup();
        this.getAllBrand();
        this.getAllUnit();
        this.getAllProductUnit();
        this.getAllSupplier();
        },
    methods: {
        getAllProduct(page) {
            this.loading = true;
            axios.get(route('api.get.dashboard.product.read.product') + '?page=' + page).then(response => {
                this.productList = response.data;
                this.loading = false;
            }).catch(e => {
                this.handleListError(e);
                this.loading = false;
            });
        },
        getAllProductGroup() {
            axios.get(route('api.get.dashboard.productgroup.read.product')) .then(response => {
                this.groupDDL = response.data;
            });
        },
        getAllBrand() {
            axios.get(route('api.get.dashboard.brand.read.all')).then(response => {
                this.brandDDL = response.data;
            });
        },
        getAllUnit() {
            axios.get(route('api.get.dashboard.unit.read.product')).then(response => {
                this.unitDDL = response.data;
            });
        },
        getAllProductUnit() {
            axios.get(route('api.get.dashboard.productunit.read.all')).then(response => {
                this.product_unitDDL = response.data;
            });
        },
        getAllSupplier() {
            axios.get(route('api.get.dashboard.supplier.read.all')).then(response => {
                this.supplierDDL = response.data;
            });
        },
        onPaginationChangePage(page) {
            if (page === 'next') {
                this.getAllProduct(this.productList.current_page + 1);
            } else if (page === 'prev') {
                this.getAllProduct(this.productList.current_page - 1);
            } else {
                this.getAllProduct(page);
            }
        },
        emptyProduct() {
            return {
                code: 'AUTO',
                product_group : {hId: '0'},
                brand: {hId: '0'},
                name: '',
                product_unit: [
                    {
                        hId: '',
                        code: 'AUTO',
                        is_base: '1',
                        conversion_value: '1',
                        is_primary_unit: '1',
                        unit: {hId: '0'}
                    }
                ],
                tax_status: '1',
                defaultSupplier: '',
                supplier: {hId: '0'},
                remarks: '',
                point: '0',
                use_serial_number: '',
                has_expiry_date: '',
                product_type: '3',
                status: '1',
            }
        },
        createNew() {
            this.mode = 'create';
            this.product = this.emptyProduct();
        },
        editSelected(idx) {
            this.mode = 'edit';
            this.product = this.productList.data[idx];
        },
        showSelected(idx) {
            this.mode = 'show';
            this.product = this.productList.data[idx];
        },
        deleteSelected(idx) {
            this.mode = 'delete';
            this.product = this.productList.data[idx];

            this.loading = true;
            axios.post(route('api.post.dashboard.product.delete', this.product.hId)).then(response => {
                this.backToList();
            }).catch(e => {
                this.handleError(e, actions);
                this.loading = false;
            });
        },
        createNewProductUnit() {
            var product_unit = {
                hId: '',
                code: 'AUTO',
                conversion_value: '0',
                unit: {hId: ''}
            };
            this.product.product_unit.push(product_unit);
        },
        deleteSelectedProductUnit(idx) {
            this.loading = true;
            if (this.mode === 'create') {
                this.product.product_unit.splice(idx, 1);
                this.loading = false;
            } else if (this.mode === 'edit') {
                // console.log(this.product.product_unit[idx].hId);
                if (this.product.product_unit[idx].hId !== '') {
                    axios.post(route('api.post.dashboard.productunit.delete', this.product.product_unit[idx].hId), new FormData($('#productForm')[0])).then(response => {
                        this.product.product_unit.splice(idx, 1);
                        this.loading = false;
                    }).catch(e => {
                        this.handleError(e, actions);
                        this.loading = false;
                    });
                } else { 
                    this.product.product_unit.splice(idx, 1);
                    this.loading = false;
                }
            } else { }
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post(route('api.post.dashboard.product.save'), new FormData($('#productForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else if (this.mode === 'edit') {
                axios.post(route('api.post.dashboard.product.edit', this.product.hId), new FormData($('#productForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else { }
        },
        handleError(e, actions) {
            //Laravel Validations
            if (e.response.data.errors !== undefined && Object.keys(e.response.data.errors).length > 0) {
                for (var key in e.response.data.errors) {
                    for (var i = 0; i < e.response.data.errors[key].length; i++) {
                        actions.setFieldError(key, e.response.data.errors[key][i]);
                    }
                }
            } else {
                //Catch From Controller
                actions.setFieldError('', e.response.data.message + ' (' + e.response.status + ' ' + e.response.statusText + ')');
            }
        },
        handleListError(e) {
            if (e.response.data.message !== undefined) {
                this.mode = 'error';
                this.listErrors.push(e.response.data.message);
            }
        },
        resetListErrors() {
            this.listErrors = [];
        },
        handleTableListError(e) {
            if (e.response.data.message !== undefined) {
                this.tableListErrors.push(e.response.data.message);
            }
        },
        resetTableListErrors() {
            this.tableListErrors = [];
        },
        handleUpload(e) {
            const files = e.target.files;

            let filename = files[0].name;

            const fileReader = new FileReader()
            fileReader.addEventListener('load', () => {
                this.product.profile.img_path = fileReader.result
            })
            fileReader.readAsDataURL(files[0])
        },
        backToList() {
            this.mode = 'list';
            this.getAllProduct(this.productList.current_page);
            this.product = this.emptyProduct();
        },
        toggleFullScreen() {
            this.fullscreen = !this.fullscreen;
        },
        toggleContentHidden() {
            this.contentHidden = !this.contentHidden;
        },
        refreshList() {
            this.getAllProduct(this.productList.current_page);
        },
    },
    computed: {
        getPages() {
            if (this.productList.current_page == null) return 0;

            return Math.ceil(this.productList.total / this.productList.per_page);
        },
        retrieveImage()
        {
            if (this.product.profile.img_path && this.product.profile.img_path !== '') {
                if (this.product.profile.img_path.includes('data:image')) {
                    return this.product.profile.img_path;
                } else {
                    return '/storage/' + this.product.profile.img_path;
                }
            } else {
                return '/images/def-product.png';
            }
        }
    }
}
</script>