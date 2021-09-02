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
                                <th>{{ $t("table.cols.remarks") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(c, cIdx) in customergroupList.data">
                                <td>{{ c.code }}</td>
                                <td>{{ c.name }}</td>
                                <td>{{ c.is_member_card }}</td>
                                <td>{{ c.remarks }}</td>
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
                                <Field id="inputCode" name="code" as="input" :class="{'form-control':true, 'is-invalid': errors['code']}" :placeholder="$t('fields.code')" :label="$t('fields.code')" v-model="customergroup.code" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="code" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customergroup.code }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputName" class="col-2 col-form-label">{{ $t('fields.name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="customergroup.name" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customergroup.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputIs_Member_Card" class="col-2 col-form-label">{{ $t('fields.is_member_card') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">                              
                                    <span v-show="this.mode === 'create' || this.mode === 'edit'">
                                        <input type="checkbox" class="css-control-input" id="is_member_card" name="is_member_card" v-model="customergroup.is_member_card" true-value="1" false-value="0">
                                        <span class="css-control-indicator"></span>
                                    </span>
                                    <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                        <span v-if="customergroup.is_member_card === 1">{{ $t('is_member_card.active') }}</span>
                                        <span v-if="customergroup.is_member_card === 0">{{ $t('is_member_card.inactive') }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputUse_limit_outstanding_notes" class="col-2 col-form-label">{{ $t('fields.use_limit_outstanding_notes') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">                              
                                    <span v-show="this.mode === 'create' || this.mode === 'edit'">
                                        <input type="checkbox" class="css-control-input" id="use_limit_outstanding_notes" name="use_limit_outstanding_notes" v-model="customergroup.use_limit_outstanding_notes" true-value="1" false-value="0">
                                        <span class="css-control-indicator"></span>
                                    </span>
                                    <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                        <span v-if="customergroup.use_limit_outstanding_notes === 1">{{ $t('use_limit_outstanding_notes.active') }}</span>
                                        <span v-if="customergroup.use_limit_outstanding_notes === 0">{{ $t('use_limit_outstanding_notes.inactive') }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputLimitOutstandingNotes" class="col-2 col-form-label">{{ $t('fields.limit_outstanding_notes') }}</label>
                            <div class="col-md-10">
                                <Field id="inputLimitOutstandingNotes" name="limit_outstanding_notes" as="input" :class="{'form-control':true, 'is-invalid': errors['limit_outstanding_notes']}" :placeholder="$t('fields.limit_outstanding_notes')" :label="$t('fields.limit_outstanding_notes')" v-model="customergroup.limit_outstanding_notes" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customergroup.limit_outstanding_notes }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputUse_Limit_Outstanding_Notes" class="col-2 col-form-label">{{ $t('fields.use_limit_payable_nominal') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">                              
                                    <span v-show="this.mode === 'create' || this.mode === 'edit'">
                                        <input type="checkbox" class="css-control-input" id="use_limit_payable_nominal" name="use_limit_payable_nominal" v-model="customergroup.use_limit_payable_nominal" true-value="1" false-value="0">
                                        <span class="css-control-indicator"></span>
                                    </span>
                                    <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                        <span v-if="customergroup.use_limit_payable_nominal === 1">{{ $t('use_limit_payable_nominal.active') }}</span>
                                        <span v-if="customergroup.use_limit_payable_nominal === 0">{{ $t('use_limit_payable_nominal.inactive') }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputLimitPayableNominal" class="col-2 col-form-label">{{ $t('fields.limit_payable_nominal') }}</label>
                            <div class="col-md-10">
                                <Field id="inputLimitPayableNominal" name="limit_payable_nominal" as="input" :class="{'form-control':true, 'is-invalid': errors['limit_payable_nominal']}" :placeholder="$t('fields.limit_payable_nominal')" :label="$t('fields.name')" v-model="customergroup.limit_payable_nominal" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customergroup.limit_payable_nominal }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputUse_Limit_Age_Notes" class="col-2 col-form-label">{{ $t('fields.use_limit_age_notes') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">                              
                                    <span v-show="this.mode === 'create' || this.mode === 'edit'">
                                        <input type="checkbox" class="css-control-input" id="use_limit_age_notes" name="use_limit_age_notes" v-model="customergroup.use_limit_age_notes" true-value="1" false-value="0">
                                        <span class="css-control-indicator"></span>
                                    </span>
                                    <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                        <span v-if="customergroup.use_limit_age_notes === 1">{{ $t('use_limit_age_notes.active') }}</span>
                                        <span v-if="customergroup.use_limit_age_notes === 0">{{ $t('use_limit_age_notes.inactive') }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputLimitAgeNotes" class="col-2 col-form-label">{{ $t('fields.limit_age_notes') }}</label>
                            <div class="col-md-10">
                                <Field id="inputLimitAgeNotes" name="limit_age_notes" as="input" :class="{'form-control':true, 'is-invalid': errors['limit_age_notes']}" :placeholder="$t('fields.limit_age_notes')" :label="$t('fields.limit_age_notes')" v-model="customergroup.limit_age_notes" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customergroup.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputTerm" class="col-2 col-form-label">{{ $t('fields.term') }}</label>
                            <div class="col-md-10">
                                <Field id="inputTerm" name="term" as="input" :class="{'form-control':true, 'is-invalid': errors['term']}" :placeholder="$t('fields.term')" :label="$t('fields.term')" v-model="customergroup.term" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customergroup.term }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputSellingPoint" class="col-2 col-form-label">{{ $t('fields.selling_point') }}</label>
                            <div class="col-md-10">
                                <Field id="inputSellingPoint" name="selling_point" as="input" :class="{'form-control':true, 'is-invalid': errors['selling_point']}" :placeholder="$t('fields.selling_point')" :label="$t('fields.selling_point')" v-model="customergroup.selling_point" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customergroup.selling_point }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputSellingPointMiltiple" class="col-2 col-form-label">{{ $t('fields.selling_point_multiple') }}</label>
                            <div class="col-md-10">
                                <Field id="inputSellingPointMiltiple" name="selling_point_multiple" as="input" :class="{'form-control':true, 'is-invalid': errors['selling_point_multiple']}" :placeholder="$t('fields.selling_point_multiple')" :label="$t('fields.selling_point_multiple')" v-model="customergroup.selling_point_multiple" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customergroup.selling_point_multiple }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputSell_At_Capital_Price" class="col-2 col-form-label">{{ $t('fields.sell_at_capital_price') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">                              
                                    <span v-show="this.mode === 'create' || this.mode === 'edit'">
                                        <input type="checkbox" class="css-control-input" id="sell_at_capital_price" name="sell_at_capital_price" v-model="customergroup.sell_at_capital_price" true-value="1" false-value="0">
                                        <span class="css-control-indicator"></span>
                                    </span>
                                    <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                        <span v-if="customergroup.sell_at_capital_price === 1">{{ $t('sell_at_capital_price.active') }}</span>
                                        <span v-if="customergroup.sell_at_capital_price === 0">{{ $t('sell_at_capital_price.inactive') }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputGlobalMarkupPercent" class="col-2 col-form-label">{{ $t('fields.global_markup_percent') }}</label>
                            <div class="col-md-10">
                                <Field id="inputGlobalMarkupPercent" name="global_markup_percent" as="input" :class="{'form-control':true, 'is-invalid': errors['global_markup_percent']}" :placeholder="$t('fields.global_markup_percent')" :label="$t('fields.global_markup_percent')" v-model="customergroup.global_markup_percent" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customergroup.global_markup_percent }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputGlobalMarkupNominal" class="col-2 col-form-label">{{ $t('fields.global_markup_nominal') }}</label>
                            <div class="col-md-10">
                                <Field id="inputGlobalMarkupNominal" name="global_markup_nominal" as="input" :class="{'form-control':true, 'is-invalid': errors['global_markup_nominal']}" :placeholder="$t('fields.global_markup_nominal')" :label="$t('fields.global_markup_nominal')" v-model="customergroup.global_markup_nominal" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customergroup.global_markup_nominal }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputGlobalDiscountPercent" class="col-2 col-form-label">{{ $t('fields.global_discount_percent') }}</label>
                            <div class="col-md-10">
                                <Field id="inputGlobalDiscountPercent" name="global_discount_percent" as="input" :class="{'form-control':true, 'is-invalid': errors['global_discount_percent']}" :placeholder="$t('fields.global_discount_percent')" :label="$t('fields.global_discount_percent')" v-model="customergroup.global_discount_percent" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customergroup.global_discount_percent }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputGlobalDiscountNominal" class="col-2 col-form-label">{{ $t('fields.global_discount_nominal') }}</label>
                            <div class="col-md-10">
                                <Field id="inputGlobalDiscountNominal" name="global_discount_nominal" as="input" :class="{'form-control':true, 'is-invalid': errors['global_discount_nominal']}" :placeholder="$t('fields.global_discount_nominal')" :label="$t('fields.global_discount_nominal')" v-model="customergroup.global_discount_nominal" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customergroup.global_discount_nominal }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputIs_Rounding" class="col-2 col-form-label">{{ $t('fields.is_rounding') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">                              
                                    <span v-show="this.mode === 'create' || this.mode === 'edit'">
                                        <input type="checkbox" class="css-control-input" id="is_rounding" name="is_rounding" v-model="customergroup.is_rounding" true-value="1" false-value="0">
                                        <span class="css-control-indicator"></span>
                                    </span>
                                    <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                        <span v-if="customergroup.is_rounding === 1">{{ $t('is_rounding.active') }}</span>
                                        <span v-if="customergroup.is_rounding === 0">{{ $t('is_rounding.inactive') }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="round_on" class="col-2 col-form-label">{{ $t('fields.round_on') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <select class="form-control" id="round_on" name="round_on" v-model="customergroup.round_on" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value="">{{ $t('placeholder.please_select') }}</option>
                                    <option value="1">{{ $t('round_onDLL.high') }}</option>
                                    <option value="2">{{ $t('round_onDLL.low') }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    <span v-if="customergroup.round_on === 1">{{ $t('round_onDLL.high') }}</span>
                                    <span v-if="customergroup.round_on === 2">{{ $t('round_onDLL.low') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputRoundDigit" class="col-2 col-form-label">{{ $t('fields.round_digit') }}</label>
                            <div class="col-md-10">
                                <Field id="inputRoundDigit" name="round_digit" as="input" :class="{'form-control':true, 'is-invalid': errors['round_digit']}" :placeholder="$t('fields.round_digit')" :label="$t('fields.round_digit')" v-model="customergroup.round_digit" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customergroup.round_digit }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputRemarks" class="col-2 col-form-label">{{ $t('fields.remarks') }}</label>
                            <div class="col-md-10">
                                <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="$t('fields.remarks')" v-model="customergroup.remarks" v-show="this.mode === 'create' || this.mode === 'edit'" rows="3"></textarea>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customergroup.remarks }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="cash_id">{{ $t('fields.cash_id') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="cash_id" name="cash_id" v-model="customergroup.cash.hId" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option :value="c.hId" v-for="c in this.cashDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                </select> 
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    {{ customergroup.cash.name }}
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
                code: '',
                name: '',
                is_member_card: '',
                use_limit_outstanding_notes: '',
                limit_outstanding_notes: '',
                use_limit_payable_nominal: '',
                limit_payable_nominal: '',
                use_limit_age_notes: '',
                limit_age_notes: '',
                term: '',
                selling_point: '',
                selling_point_multiple: '',
                sell_at_capital_price: '',
                global_markup_percent: '',
                global_markup_nominal: '',
                global_discount_percent: '',
                global_discount_nominal: '',
                is_rounding: '',
                round_on: '',
                round_digit: '',
                remarks: '',
                cash: {id:''}
            },
            cashDDL: [],
        }
    },
    created() {
    },

    mounted() {
        this.mode = 'list';
        this.getAllCustomerGroup(1);
        this.getAllCash();
    },
    methods: {
        getAllCustomerGroup(page) {
            this.loading = true;
            axios.get('/api/get/dashboard/customergroup/read?page=' + page).then(response => {
                this.customergroupList = response.data;
                this.loading = false;
            });
        },
        getAllCash() {
            axios.get('/api/get/dashboard/cash/read/all/active').then(response => {
                this.cashDDL = response.data;
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
                code: '',
                name: '',
                is_member_card: '',
                use_limit_outstanding_notes: '',
                limit_outstanding_notes: '',
                use_limit_payable_nominal: '',
                limit_payable_nominal: '',
                use_limit_age_notes: '',
                limit_age_notes: '',
                term: '',
                selling_point: '',
                selling_point_multiple: '',
                sell_at_capital_price: '',
                global_markup_percent: '',
                global_markup_nominal: '',
                global_discount_percent: '',
                global_discount_nominal: '',
                is_rounding: '',
                round_on: '',
                round_digit: '',
                remarks: '',
                cash: {id:''}
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
        deleteSelected(idx) {
            this.mode = 'delete';
            this.customergroup = this.customergroupList.data[idx];

            this.loading = true;
            axios.post('/api/post/dashboard/customergroup/delete/'  + this.customergroup.hId).then(response => {
                this.backToList();
            }).catch(e => {
                this.handleError(e, actions);
                this.loading = false;
            });
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post('/api/post/dashboard/customergroup/save', new FormData($('#customergroupForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else if (this.mode === 'edit') {
                axios.post('/api/post/dashboard/customergroup/edit/' + this.customergroup.hId, new FormData($('#customergroupForm')[0])).then(response => {
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