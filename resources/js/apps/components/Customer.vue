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
                                <th>{{ $t("table.cols.sales_customer_group") }}</th>
                                <th>{{ $t("table.cols.sales_territory") }}</th>
                                <th>{{ $t("table.cols.limit_outstanding_notes") }}</th>
                                <th>{{ $t("table.cols.limit_payable_nominal") }}</th>
                                <th>{{ $t("table.cols.limit_due_date") }}</th>
                                <th>{{ $t("table.cols.term") }}</th>
                                <th>{{ $t("table.cols.address") }}</th>
                                <th>{{ $t("table.cols.city") }}</th>
                                <th>{{ $t("table.cols.contact") }}</th>
                                <th>{{ $t("table.cols.tax_id") }}</th>
                                <th>{{ $t("table.cols.status") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(c, cIdx) in customerList.data">
                                <td>{{ c.code }}</td>
                                <td>{{ c.name }}</td>
                                <td>{{ c.sales_customer_group }}</td>
                                <td>{{ c.sales_territory }}</td>
                                <td>{{ c.limit_outstanding_notes }}</td>
                                <td>{{ c.limit_payable_nominal }}</td>
                                <td>{{ c.limit_due_date }}</td>
                                <td>{{ c.term }}</td>
                                <td>{{ c.address }}</td>
                                <td>{{ c.city }}</td>
                                <td>{{ c.contact }}</td>
                                <td>{{ c.tax_id }}</td>
                                <td>{{ c.status }}</td>
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
                <div id="crud" v-if="this.mode !== 'list'">
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
                                <Field id="inputCode" name="code" as="input" :class="{'form-control':true, 'is-invalid': errors['code']}" :placeholder="$t('fields.code')" :label="$t('fields.code')" v-model="customer.code" v-if="this.mode === 'create' || this.mode === 'edit'" :readonly="this.mode === 'edit'"/>
                                <ErrorMessage name="code" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ customer.code }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputName" class="col-2 col-form-label">{{ $t('fields.name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="customer.name" v-if="this.mode === 'create' || this.mode === 'edit'" :readonly="this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ customer.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="example-select">Sales Customer Group</label>
                            <div class="col-md-10">
                                <select class="form-control" id="example-select" name="example-select">
                                    <option value="0">Please select Sales Customer Group</option>
                                    <option value="1">Retail</option>
                                    <option value="2">Wholesale Price</option>
                                    <option value="3">Distributor</option>
                                </select>             
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputSalesTerritory" class="col-2 col-form-label">{{ $t('fields.sales_territory') }}</label>
                            <div class="col-md-10">
                                <Field id="inputSalesTerritory" name="sales_territory" as="input" :class="{'form-control':true, 'is-invalid': errors['sales_territory']}" :placeholder="$t('fields.sales_territory')" :label="$t('fields.sales_territory')" v-model="customer.sales_territory" v-if="this.mode === 'create' || this.mode === 'edit'" :readonly="this.mode === 'edit'"/>
                                <ErrorMessage name="sales_territory" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ customer.sales_territory }}</div>
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
                                <Field id="inputLimitOutstandingNotes" name="limit_outstanding_notes" as="input" :class="{'form-control':true, 'is-invalid': errors['limit_outstanding_notes']}" :placeholder="$t('fields.limit_outstanding_notes')" :label="$t('fields.limit_outstanding_notes')" v-model="customer.limit_outstanding_notes" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="limit_outstanding_notes" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ customer.name }}</div>
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
                                <input id="inputLimitPayableNominal" name="limit_payable_nominal" type="number" class="form-control" :placeholder="$t('fields.limit_payable_nominal')" v-model="customer.limit_payable_nominal" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="limit_payable_nominal" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ customer.limit_payable_nominal }}</div>
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
                                <input id="inputLimitDueDate" name="limit_due_date" type="number" class="form-control" :placeholder="$t('fields.limit_due_date')" v-model="customer.limit_due_date" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="limit_due_date" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ customer.limit_due_date }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputTerm" class="col-2 col-form-label">{{ $t('fields.term') }}</label>
                            <div class="col-md-10">
                                <Field id="inputTerm" name="term" as="input" :class="{'form-control':true, 'is-invalid': errors['term']}" :placeholder="$t('fields.term')" :label="$t('fields.term')" v-model="customer.term" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="term" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ customer.term }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputAddress" class="col-2 col-form-label">{{ $t('fields.address') }}</label>
                            <div class="col-md-10">
                                <input id="inputAddress" name="address" type="text" class="form-control" :placeholder="$t('fields.address')" v-model="customer.address" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ customer.address }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputCity" class="col-2 col-form-label">{{ $t('fields.city') }}</label>
                            <div class="col-md-10">
                                <input id="inputCity" name="city" type="text" class="form-control" :placeholder="$t('fields.city')" v-model="customer.city" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ customer.city }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputContact" class="col-2 col-form-label">{{ $t('fields.contact') }}</label>
                            <div class="col-md-10">
                                <input id="inputContact" name="contact" type="number" class="form-control" :placeholder="$t('fields.contact')" v-model="customer.contact" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ customer.contact }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputTaxId" class="col-2 col-form-label">{{ $t('fields.tax_id') }}</label>
                            <div class="col-md-10">
                                <input id="inputTaxId" name="tax_id" type="number" class="form-control" :placeholder="$t('fields.tax_id')" v-model="customer.tax_id" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ customer.tax_id }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputStatus" class="col-2 col-form-label">{{ $t('fields.status') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <div>
                                    <select class="form-control" id="inputStatus" name="status" v-model="customer.status" v-if="this.mode === 'create' || this.mode === 'edit'">
                                        <option value="ACTIVE">{{ $t('statusDDL.active') }}</option>
                                        <option value="INACTIVE">{{ $t('statusDDL.inactive') }}</option>
                                    </select>
                                    <div class="form-control-plaintext" v-if="this.mode === 'show'">
                                        <span v-if="customer.status === 'ACTIVE'">{{ $t('statusDDL.active') }}</span>
                                        <span v-if="customer.status === 'INACTIVE'">{{ $t('statusDDL.inactive') }}</span>
                                    </div>
                                </div>
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
            sales_customer_group: 'required',
            sales_territory: 'required',
            limit_outstanding_notes: 'required',
            limit_payable_nominal: 'required',
            limit_due_date: 'required',
            term: 'required',
            address: 'required',
            city: 'required',
            contact: 'required',
            status: 'required',

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
            customerList: { },
            customer: {
                customer: [],
                selectedCompanies: [],
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
        this.getAllCustomer(1);
        //this.getAllCompanies();
    },
    methods: {
        getAllCustomer(page) {
            this.loading = true;
            axios.get('/api/get/admin/customer/read?page=' + page).then(response => {
                this.customerList = response.data;
                this.loading = false;
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
                customer: [],
                selectedCompanies: [],
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
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post('/api/post/admin/customer/save', new FormData($('#customerForm')[0]), {
                    headers: {
                        'content-type': 'multipart/form-data'
                    }
                }).then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else if (this.mode === 'edit') {
                axios.post('/api/post/admin/customer/edit/' + this.customer.hId, new FormData($('#customerForm')[0]), {
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