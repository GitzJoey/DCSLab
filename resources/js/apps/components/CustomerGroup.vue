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
                <div id="list" v-if="this.mode === 'list'">
                    <table class="table table-vcenter">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ $t("table.cols.code") }}</th>
                                <th>{{ $t("table.cols.name") }}</th>
                                <th>{{ $t("table.cols.is_member_card") }}</th>
                                <th>{{ $t("table.cols.limit_outstanding_notes") }}</th>
                                <th>{{ $t("table.cols.limit_payable_nominal") }}</th>
                                <th>{{ $t("table.cols.limit_due_date") }}</th>
                                <th>{{ $t("table.cols.term") }}</th>
                                <th>{{ $t("table.cols.selling_point") }}</th>
                                <th>{{ $t("table.cols.selling_point_multiple") }}</th>
                                <th>{{ $t("table.cols.sell_at_capital_price") }}</th>
                                <th>{{ $t("table.cols.global_markup_percent") }}</th>
                                <th>{{ $t("table.cols.global_markup_nominal") }}</th>
                                <th>{{ $t("table.cols.global_discount_percent") }}</th>
                                <th>{{ $t("table.cols.global_discount_nominal") }}</th>
                                <th>{{ $t("table.cols.round_on") }}</th>
                                <th>{{ $t("table.cols.round_digit") }}</th>
                                <th>{{ $t("table.cols.remarks") }}</th>
                                <th>{{ $t("table.cols.finance_cash_id") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(c, cIdx) in customergroupList.data">
                                <td>{{ c.code }}</td>
                                <td>{{ c.name }}</td>
                                <td>{{ c.is_member_card }}</td>
                                <td>{{ c.use_limit_outstanding_notes }}</td>
                                <td>{{ c.limit_outstanding_notes }}</td>
                                <td>{{ c.use_limit_payable_nominal }}</td>
                                <td>{{ c.limit_payable_nominal }}</td>
                                <td>{{ c.use_limit_due_date }}</td>
                                <td>{{ c.limit_due_date }}</td>
                                <td>{{ c.term }}</td>
                                <td>{{ c.selling_point }}</td>
                                <td>{{ c.selling_point }}</td>
                                <td>{{ c.selling_point_multiple }}</td>
                                <td>{{ c.sell_at_capital_price }}</td>
                                <td>{{ c.global_markup_percent }}</td>
                                <td>{{ c.global_markup_nominal }}</td>
                                <td>{{ c.global_discount_percent }}</td>
                                <td>{{ c.global_discount_nominal }}</td>
                                <td>{{ c.is_rounding }}</td>
                                <td>{{ c.round_on }}</td>
                                <td>{{ c.round_digit }}</td>
                                <td>{{ c.remarks }}</td>
                                <td>{{ c.finance_cash_id }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.show')" v-on:click="showSelected(cIdx)">
                                            <i class="fa fa-info"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.edit')" v-on:click="editSelected(cIdx)">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-end">
                            <li :class="{'page-item':true, 'disabled': this.customergroupList.prev_page_url == null}">
                                <a class="page-link" href="#" aria-label="Previous" v-on:click="onPaginationChangePage('prev')">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li :class="{'page-item':true, 'disabled': this.customergroupList.next_page_url == null}">
                                <a class="page-link" href="#" aria-label="Next" v-on:click="onPaginationChangePage('next')">
                                    <span aria-hidden="true">&raquo;</span>
                                    <span class="sr-only">Next</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </transition>
            <transition name="fade">
                <div id="crud" v-if="this.mode !== 'list'">
                    <Form id="customergroupForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
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
                                <Field id="inputCode" name="code" as="input" :class="{'form-control':true, 'is-invalid': errors['code']}" :placeholder="$t('fields.code')" :label="$t('fields.code')" v-model="customergroup.code" v-if="this.mode === 'create' || this.mode === 'edit'" :readonly="this.mode === 'edit'"/>
                                <ErrorMessage name="code" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.code }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputName" class="col-2 col-form-label">{{ $t('fields.name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="customergroup.name" v-if="this.mode === 'create' || this.mode === 'edit'" :readonly="this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputIsMemberCard" class="col-2 col-form-label">{{ $t('fields.is_member_card') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">
                                    <input type="checkbox" class="css-control-input">
                                    <span class="css-control-indicator"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputUseLimitOutstandingNotes" class="col-2 col-form-label">{{ $t('fields.use_limit_outstanding_notes') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">
                                    <input type="checkbox" class="css-control-input">
                                    <span class="css-control-indicator"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputLimitOutstandingNotes" class="col-2 col-form-label">{{ $t('fields.limit_outstanding_notes') }}</label>
                            <div class="col-md-10">
                                <Field id="inputLimitOutstandingNotes" name="limit_outstanding_notes" as="input" :class="{'form-control':true, 'is-invalid': errors['limit_outstanding_notes']}" :placeholder="$t('fields.limit_outstanding_notes')" :label="$t('fields.limit_outstanding_notes')" v-model="customergroup.limit_outstanding_notes" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="limit_outstanding_notes" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.limit_outstanding_notes }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputUseLimitPayableNominal" class="col-2 col-form-label">{{ $t('fields.use_limit_payable_nominal') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">
                                    <input type="checkbox" class="css-control-input">
                                    <span class="css-control-indicator"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputLimitPayableNominal" class="col-2 col-form-label">{{ $t('fields.limit_payable_nominal') }}</label>
                            <div class="col-md-10">
                                <Field id="inputLimitPayableNominal" name="limit_payable_nominal" as="input" :class="{'form-control':true, 'is-invalid': errors['limit_payable_nominal']}" :placeholder="$t('fields.limit_payable_nominal')" :label="$t('fields.name')" v-model="customergroup.limit_payable_nominal" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="limit_payable_nominal" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.limit_payable_nominal }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputUseLimitDueDate" class="col-2 col-form-label">{{ $t('fields.use_limit_due_date') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">
                                    <input type="checkbox" class="css-control-input">
                                    <span class="css-control-indicator"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputLimitDueDate" class="col-2 col-form-label">{{ $t('fields.limit_due_date') }}</label>
                            <div class="col-md-10">
                                <Field id="inputLimitDueDate" name="limit_due_date" as="input" :class="{'form-control':true, 'is-invalid': errors['limit_due_date']}" :placeholder="$t('fields.limit_due_date')" :label="$t('fields.limit_due_date')" v-model="customergroup.limit_due_date" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="limit_due_date" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputTerm" class="col-2 col-form-label">{{ $t('fields.term') }}</label>
                            <div class="col-md-10">
                                <Field id="inputTerm" name="term" as="input" :class="{'form-control':true, 'is-invalid': errors['term']}" :placeholder="$t('fields.term')" :label="$t('fields.term')" v-model="customergroup.term" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="term" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.term }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputSellingPoint" class="col-2 col-form-label">{{ $t('fields.selling_point') }}</label>
                            <div class="col-md-10">
                                <Field id="inputSellingPoint" name="selling_point" as="input" :class="{'form-control':true, 'is-invalid': errors['selling_point']}" :placeholder="$t('fields.selling_point')" :label="$t('fields.selling_point')" v-model="customergroup.selling_point" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="selling_point" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.selling_point }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputSellingPointMiltiple" class="col-2 col-form-label">{{ $t('fields.selling_point_multiple') }}</label>
                            <div class="col-md-10">
                                <Field id="inputSellingPointMiltiple" name="selling_point_multiple" as="input" :class="{'form-control':true, 'is-invalid': errors['selling_point_multiple']}" :placeholder="$t('fields.selling_point_multiple')" :label="$t('fields.selling_point_multiple')" v-model="customergroup.selling_point_multiple" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="selling_point_multiple" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.selling_point_multiple }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputSellAtCapitalPrice" class="col-2 col-form-label">{{ $t('fields.sell_at_capital_price') }}</label>
                            <div class="col-md-10">
                                <Field id="inputSellAtCapitalPrice" name="sell_at_capital_price" as="input" :class="{'form-control':true, 'is-invalid': errors['sell_at_capital_price']}" :placeholder="$t('fields.sell_at_capital_price')" :label="$t('fields.sell_at_capital_price')" v-model="customergroup.sell_at_capital_price" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="sell_at_capital_price" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.sell_at_capital_price }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputGlobalMarkupPercent" class="col-2 col-form-label">{{ $t('fields.global_markup_percent') }}</label>
                            <div class="col-md-10">
                                <Field id="inputGlobalMarkupPercent" name="global_markup_percent" as="input" :class="{'form-control':true, 'is-invalid': errors['global_markup_percent']}" :placeholder="$t('fields.global_markup_percent')" :label="$t('fields.global_markup_percent')" v-model="customergroup.global_markup_percent" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="global_markup_percent" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.global_markup_percent }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputGlobalMarkupNominal" class="col-2 col-form-label">{{ $t('fields.global_markup_nominal') }}</label>
                            <div class="col-md-10">
                                <Field id="inputGlobalMarkupNominal" name="global_markup_nominal" as="input" :class="{'form-control':true, 'is-invalid': errors['global_markup_nominal']}" :placeholder="$t('fields.global_markup_nominal')" :label="$t('fields.global_markup_nominal')" v-model="customergroup.global_markup_nominal" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="global_markup_nominal" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.global_markup_nominal }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputGlobalDiscountPercent" class="col-2 col-form-label">{{ $t('fields.global_discount_percent') }}</label>
                            <div class="col-md-10">
                                <Field id="inputGlobalDiscountPercent" name="global_discount_percent" as="input" :class="{'form-control':true, 'is-invalid': errors['global_discount_percent']}" :placeholder="$t('fields.global_discount_percent')" :label="$t('fields.global_discount_percent')" v-model="customergroup.global_discount_percent" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="global_discount_percent" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.global_discount_percent }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputGlobalDiscountNominal" class="col-2 col-form-label">{{ $t('fields.global_discount_nominal') }}</label>
                            <div class="col-md-10">
                                <Field id="inputGlobalDiscountNominal" name="global_discount_nominal" as="input" :class="{'form-control':true, 'is-invalid': errors['global_discount_nominal']}" :placeholder="$t('fields.global_discount_nominal')" :label="$t('fields.global_discount_nominal')" v-model="customergroup.global_discount_nominal" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="global_discount_nominal" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.global_discount_nominal }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputIsRounding" class="col-2 col-form-label">{{ $t('fields.is_rounding') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">
                                    <input type="checkbox" class="css-control-input">
                                    <span class="css-control-indicator"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="example-select">Round On</label>
                            <div class="col-md-10">
                                <select class="form-control" id="example-select" name="example-select">
                                    <option value="0">Please select Round On</option>
                                    <option value="1">High</option>
                                    <option value="2">Low</option>
                                </select>             
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputRoundDigit" class="col-2 col-form-label">{{ $t('fields.round_digit') }}</label>
                            <div class="col-md-10">
                                <Field id="inputRoundDigit" name="round_digit" as="input" :class="{'form-control':true, 'is-invalid': errors['round_digit']}" :placeholder="$t('fields.round_digit')" :label="$t('fields.round_digit')" v-model="customergroup.round_digit" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="round_digit" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.round_digit }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputRemarks" class="col-2 col-form-label">{{ $t('fields.remarks') }}</label>
                            <div class="col-md-10">
                                <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="$t('fields.remarks')" v-model="customergroup.remarks" v-if="this.mode === 'create' || this.mode === 'edit'" rows="3"></textarea>
                                <ErrorMessage name="remarks" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ group.remarks }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="example-select">Default Cash Payment</label>
                            <div class="col-md-10">
                                <select class="form-control" id="example-select" name="example-select">
                                    <option value="0">Please select Default Cash Payment</option>
                                    <option value="1">BCA</option>
                                    <option value="2">Mandiri</option>
                                    <option value="3">CIMB</option>
                                </select>             
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-7 col-form-label"></label>
                            <div class="col-md-5">
                                <div v-if="this.mode === 'create' || this.mode === 'edit'">
                                    <button type="submit" class="btn btn-primary min-width-125" data-toggle="click-ripple">{{ $t("buttons.submit") }}</button>&nbsp;&nbsp;&nbsp;
                                    <button type="button" class="btn btn-secondary min-width-125" data-toggle="click-ripple" v-on:click="handleReset">{{ $t("buttons.reset") }}</button>
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
            <div v-if="this.mode !== 'list'">
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
            limit_outstanding_notes: 'required',
            limit_payable_nominal: 'required',
            limit_due_date: 'required',
            term: 'required',
            selling_point: 'required',
            selling_point_multiple: 'required',
            sell_at_capital_price: 'required',
            global_markup_percent: 'required',
            global_markup_nominal: 'required',
            global_discount_percent: 'required',
            global_discount_nominal: 'required',
            round_digit: 'required',
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
            customergroupList: [],
            customergroup: {
                customergroup: [],
                selectedCustomerGroups: [],
                profile: {
                    status: 'ACTIVE',
                },
                selectedSettings: {
                    theme: 'corporate',
                    dateFormat: '',
                    timeFormat: ''
                }
            },
        }
    },
    created() {
    },

    mounted() {
        this.mode = 'list';
        this.getAllCustomerGroup(1);
        //this.getAllCompanies();
    },
    methods: {
        getAllCustomerGroup(page) {
            this.loading = true;
            axios.get('/api/get/admin/customergroup/read?page=' + page).then(response => {
                this.customergroupList = response.data;
                this.loading = false;
            });
        },
        onPaginationChangePage(page) {
            if (page === 'next') {
                this.getAllCustomerGroup(this.customergroupList.current_page + 1);
            } else if (page === 'prev') {
                this.getAllCustomerGroup(this.customergroupList.current_page - 1);
            } else {
                this.getAllCustomerGroup(page);
            }
        },
        emptyCustomerGroup() {
            return {
                customergroup: [],
                selectedCustomerGroups: [],
                profile: {
                    img_path: '',
                    country: '',
                    status: 'ACTIVE',
                },
                selectedSettings: {
                    theme: 'corporate',
                    dateFormat: 'yyyy_MM_dd',
                    timeFormat: 'hh_mm_ss'
                }
            }
        },
        createNew() {
            this.mode = 'create';
            this.customergroup = this.emptyCustomerGroup();
        },
        editSelected(idx) {
            this.mode = 'edit';
            this.customergroup = this.customergroupList.data[idx];
        },
        showSelected(idx) {
            this.mode = 'show';
            this.customergroup = this.customergroupList.data[idx];
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post('/api/post/admin/customergroup/save', new FormData($('#customergroupForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else if (this.mode === 'edit') {
                axios.post('/api/post/admin/customergroup/edit/' + this.customergroup.hId, new FormData($('#customergroupForm')[0]), {
                    headers: {
                        'content-type': 'multipart/form-data'
                    }
                }).then(response => {
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
        handleUpload(e) {
            const files = e.target.files;

            let filename = files[0].name;

            const fileReader = new FileReader()
            fileReader.addEventListener('load', () => {
                this.customergroup.profile.img_path = fileReader.result
            })
            fileReader.readAsDataURL(files[0])
        },
        backToList() {
            this.mode = 'list';
            this.getAllCustomerGroup(this.customergroupList.current_page);
            this.customergroup = this.emptyCustomerGroup();
        },
        toggleFullScreen() {
            this.fullscreen = !this.fullscreen;
        },
        toggleContentHidden() {
            this.contentHidden = !this.contentHidden;
        },
        refreshList() {
            this.getAllCustomerGroup(this.customergroupList.current_page);
        },
    },
    computed: {
        getPages() {
            if (this.customergroupList.current_page == null) return 0;

            return Math.ceil(this.customergroupList.total / this.customergroupList.per_page);
        },
        retrieveImage()
        {
            if (this.customergroup.profile.img_path && this.customergroup.profile.img_path !== '') {
                if (this.customergroup.profile.img_path.includes('data:image')) {
                    return this.customergroup.profile.img_path;
                } else {
                    return '/storage/' + this.customergroup.profile.img_path;
                }
            } else {
                return '/images/def-customergroup.png';
            }
        }
    }
}
</script>