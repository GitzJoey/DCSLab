<template>
    <div :class="{'block block-bordered block-themed block-mode-loading-refresh':true, 'block-mode-loading':this.loading, 'block-mode-fullscreen':this.fullscreen, 'block-mode-hidden':this.contentHidden}">
        <div class="block-header bg-gray-dark">
            <h3 class="block-title" v-if="this.mode === 'list'"><strong>{{ $t('table.title') }}</strong></h3>
            <h3 class="block-title" v-if="this.mode === 'create'"><strong>{{ $t('actions.create') }}</strong></h3>
            <h3 class="block-title" v-if="this.mode === 'edit'"><strong>{{ $t('actions.edit') }}</strong></h3>
            <h3 class="block-title" v-if="this.mode === 'show'"><strong>{{ $t('actions.show') }}</strong></h3>
            <h3 class="block-title" v-if="this.mode === 'error'"><strong>&nbsp;</strong></h3>
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
                                <th>{{ $t("table.cols.name") }}</th>
                                <th>{{ $t("table.cols.customer_group_id") }}</th>
                                <th>{{ $t("table.cols.remarks") }}</th>
                                <th>{{ $t("table.cols.status") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(c, cIdx) in customerList.data">
                                <td>{{ c.code }}</td>
                                <td>{{ c.name }}</td>
                                <td>{{ c.customer_group.name }}</td>
                                <td>{{ c.remarks }}</td>
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
                            <li :class="{'page-item':true, 'disabled': this.customerList.prev_page_url == null}">
                                <a class="page-link" href="#" aria-label="Previous" v-on:click="onPaginationChangePage('prev')">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li :class="{'page-item':true, 'disabled': this.customerList.next_page_url == null}">
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
                <div id="crud" v-if="this.mode !== 'list' && this.mode !== 'error'">
                    <Form id="customerForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
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
                                <Field id="inputCode" name="code" as="input" :class="{'form-control':true, 'is-invalid': errors['code']}" :placeholder="$t('fields.code')" :label="$t('fields.code')" v-model="customer.code" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="code" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customer.code }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputName" class="col-2 col-form-label">{{ $t('fields.name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="customer.name" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customer.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="customer_group_id">{{ $t('fields.customer_group_id') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="customer_group_id" name="customer_group_id" v-model="customer.customer_group.hId" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value="">{{ $t('placeholder.please_select') }}</option>
                                    <option :value="c.hId" v-for="c in this.customergroupDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    {{ customer.customer_group.name }}
                                </div>            
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputSalesTerritory" class="col-2 col-form-label">{{ $t('fields.sales_territory') }}</label>
                            <div class="col-md-10">
                                <Field id="inputSalesTerritory" name="sales_territory" as="input" :class="{'form-control':true, 'is-invalid': errors['sales_territory']}" :placeholder="$t('fields.sales_territory')" :label="$t('fields.sales_territory')" v-model="customer.sales_territory" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customer.sales_territory }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputMaxOpenInvoice" class="col-2 col-form-label">{{ $t('fields.max_open_invoice') }}</label>
                            <div class="col-md-5">
                                <Field id="inputMaxOpenInvoice" name="max_open_invoice" as="input" :class="{'form-control':true, 'is-invalid': errors['max_open_invoice']}" :placeholder="$t('fields.max_open_invoice')" :label="$t('fields.max_open_invoice')" v-model="customer.max_open_invoice" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ customer.max_open_invoice }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputMaxOustandingInvoice" class="col-2 col-form-label">{{ $t('fields.max_outstanding_invoice') }}</label>
                            <div class="col-md-5">
                                <Field id="inputMaxOustandingInvoice" name="max_outstanding_invoice" as="input" :class="{'form-control':true, 'is-invalid': errors['max_outstanding_invoice']}" :placeholder="$t('fields.max_outstanding_invoice')" :label="$t('fields.name')" v-model="customer.max_outstanding_invoice" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customer.max_outstanding_invoice }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputMaxInvoiceAge" class="col-2 col-form-label">{{ $t('fields.max_invoice_age') }}</label>
                            <div class="col-md-5">
                                <Field id="inputMaxInvoiceAge" name="max_invoice_age" as="input" :class="{'form-control':true, 'is-invalid': errors['max_invoice_age']}" :placeholder="$t('fields.max_invoice_age')" :label="$t('fields.max_invoice_age')" v-model="customer.max_invoice_age" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customer.max_invoice_age }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputPaymentTerm" class="col-2 col-form-label">{{ $t('fields.payment_term') }}</label>
                            <div class="col-md-10">
                                <Field id="inputPaymentTerm" name="payment_term" as="input" :class="{'form-control':true, 'is-invalid': errors['payment_term']}" :placeholder="$t('fields.payment_term')" :label="$t('fields.payment_term')" v-model="customer.payment_term" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customer.payment_term }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputAddress" class="col-2 col-form-label">Address</label>
                            <div class="col-md-10">
                                <div :class="{'block block-bordered block-themed block-mode-loading-refresh':true, 'block-mode-loading':this.loading, 'block-mode-fullscreen':this.fullscreen, 'block-mode-hidden':this.contentHidden}">
                                    <div class="block-content">
                                        <table class="table table-vcenter">
                                            <!-- <thead class="thead-light">
                                                <tr>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                            </thead> -->
                                            <tbody>
                                                <input type="hidden" v-model="customer.customer_address">
                                                <tr v-for="(c, cIdx) in customer.customer_address">
                                                    <input type="hidden" v-model="c.hId" name="customer_address_hId[]"/>
                                                    <td>
                                                        <div class="form-group row">
                                                            <label class="col-2 col-form-label">{{ $t('fields.address') }}</label>
                                                            <div class="col-md-10">
                                                                <input type="text" class="form-control" v-model="c.address" id="inputAddress" name="address[]" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ c.address }}</div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row">
                                                            <label class="col-2 col-form-label">{{ $t('fields.city') }}</label>
                                                            <div class="col-md-4">
                                                                <input type="text" class="form-control" v-model="c.city" id="inputCity" name="city[]" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ c.city }}</div>
                                                            </div>

                                                            <label class="col-2 col-form-label">{{ $t('fields.contact') }}</label>
                                                            <div class="col-md-4">
                                                                <input type="text" class="form-control" v-model="c.contact" id="inputContact" name="contact[]" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ c.contact }}</div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row">
                                                            <label for="inputAddressRemarks" class="col-2 col-form-label">{{ $t('fields.remarks') }}</label>
                                                            <div class="col-md-10">
                                                                <textarea id="inputAddressRemarks" name="address_remarks[]" type="text" class="form-control" :placeholder="$t('fields.remarks')" rows="3" v-model="c.remarks" v-show="this.mode === 'create' || this.mode === 'edit'"></textarea>
                                                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ c.remarks }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    
                                                    <!-- Delete Button -->
                                                    <td class="text-center">
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.delete')" v-on:click="deleteSelectedCustomerAddress(cIdx)">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="block-content block-content-full block-content-sm bg-body-light font-size-sm" v-if="this.mode === 'create' || this.mode === 'edit'">
                                        <button type="button" class="btn btn-primary min-width-125" data-toggle="click-ripple" v-on:click="createNewCustomerAddress"><i class="fa fa-plus-square"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputTaxId" class="col-2 col-form-label">{{ $t('fields.tax_id') }}</label>
                            <div class="col-md-10">
                                <Field id="inputTaxId" name="tax_id" type="text" class="form-control" :placeholder="$t('fields.tax_id')" v-model="customer.tax_id" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customer.tax_id }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputRemarks" class="col-2 col-form-label">{{ $t('fields.remarks') }}</label>
                            <div class="col-md-10">
                                <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="$t('fields.remarks')" v-model="customer.remarks" v-show="this.mode === 'create' || this.mode === 'edit'" rows="3"></textarea>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ customer.remarks }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="status" class="col-2 col-form-label">{{ $t('fields.status') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <select class="form-control" id="status" name="status" v-model="customer.status" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value='1'>{{ $t('statusDDL.active') }}</option>
                                    <option value='0'>{{ $t('statusDDL.inactive') }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    <span v-if="customer.status === 1">{{ $t('statusDDL.active') }}</span>
                                    <span v-if="customer.status === 0">{{ $t('statusDDL.inactive') }}</span>
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
            customerList: [],
            customer: {
                code: 'AUTO',
                name: '',
                customer_group: { hId: '' },
                sales_territory: '',
                max_open_invoice: '0',
                max_outstanding_invoice: '0',
                max_invoice_age: '0',
                payment_term: '0',
                customer_address: [
                    {
                        hId: '',
                        address: '',
                        city: '',
                        contact: '',
                        remarks: ''
                    }
                ],
                tax_id: '',
                remarks: '',
                status: '1',
            },
            customergroupDDL: [],
            listErrors: [],
            tableListErrors: [],
        }
    },
    created() {
    },

    mounted() {
        this.mode = 'list';
        this.getAllCustomer(1);
        this.getAllCustomerGroup();
    },
    methods: {
        getAllCustomer(page) {
            this.loading = true;
            axios.get(route('api.get.dashboard.customer.read') + '?page=' + page).then(response => {
                this.customerList = response.data;
                this.loading = false;
            }).catch(e => {
                this.handleListError(e);
                this.loading = false;
            });
        },
        getAllCustomerGroup() {
            axios.get(route('api.get.dashboard.customergroup.read.all_active')).then(response => {
                this.customergroupDDL = response.data;
            });
        },
        onPaginationChangePage(page) {
            if (page === 'next') {
                this.getAllCustomer(this.customerList.current_page + 1);
            } else if (page === 'prev') {
                this.getAllCustomer(this.customerList.current_page - 1);
            } else {
                this.getAllCustomer(page);
            }
        },
        emptyCustomer() {
            return {
                code: 'AUTO',
                name: '',
                customer_group: { hId: '' },
                sales_territory: '',
                max_open_invoice: '0',
                max_outstanding_invoice: '0',
                max_invoice_age: '0',
                payment_term: '0',
                customer_address: [
                    {
                        hId: '',
                        address: '',
                        city: '',
                        contact: '',
                        remarks: ''
                    }
                ],
                tax_id: '',
                remarks: '',
                status: '1',
            }
        },
        createNew() {
            this.mode = 'create';
            this.customer = this.emptyCustomer();
        },
        editSelected(idx) {
            this.mode = 'edit';
            this.customer = this.customerList.data[idx];
        },
        showSelected(idx) {
            this.mode = 'show';
            this.customer = this.customerList.data[idx];
        },
        deleteSelected(idx) {
            this.mode = 'delete';
            this.customer = this.customerList.data[idx];

            this.loading = true;
            axios.post(route('api.post.dashboard.customer.delete', this.customer.hId)).then(response => {
                this.backToList();
            }).catch(e => {
                this.handleError(e, actions);
                this.loading = false;
            });
        },
        createNewCustomerAddress() {
            var customer_address = {
                hId: '',
                address: '',
                city: '',
                contact: '',
                remarks: ''
            };
            this.customer.customer_address.push(customer_address);
        },
        deleteSelectedCustomerAddress(idx) {
            this.loading = true;
            if (this.mode === 'create') {
                this.customer.customer_address.splice(idx, 1);
                this.loading = false;
            } else if (this.mode === 'edit') {
                if (this.customer.customer_address[idx].hId !== '') {
                    this.customer.customer_address.splice(idx, 1);
                    this.loading = false;
                } else { 
                    this.customer.customer_address.splice(idx, 1);
                    this.loading = false;
                }
            } else { }
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post(route('api.post.dashboard.customer.save'), new FormData($('#customerForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else if (this.mode === 'edit') {
                axios.post(route('api.post.dashboard.customer.edit', this.customer.hId), new FormData($('#customerForm')[0])).then(response => {
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
                this.customer.profile.img_path = fileReader.result
            })
            fileReader.readAsDataURL(files[0])
        },
        backToList() {
            this.mode = 'list';
            this.getAllCustomer(this.customerList.current_page);
            this.customer = this.emptyCustomer();
        },
        toggleFullScreen() {
            this.fullscreen = !this.fullscreen;
        },
        toggleContentHidden() {
            this.contentHidden = !this.contentHidden;
        },
        refreshList() {
            this.getAllCustomer(this.customerList.current_page);
        },
    },
    computed: {
        getPages() {
            if (this.customerList.current_page == null) return 0;

            return Math.ceil(this.customerList.total / this.customerList.per_page);
        },
        retrieveImage()
        {
            if (this.customer.profile.img_path && this.customer.profile.img_path !== '') {
                if (this.customer.profile.img_path.includes('data:image')) {
                    return this.customer.profile.img_path;
                } else {
                    return '/storage/' + this.customer.profile.img_path;
                }
            } else {
                return '/images/def-customer.png';
            }
        }
    }
}
</script>