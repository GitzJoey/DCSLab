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
                                <th>{{ $t("table.cols.company_id") }}</th>
                                <th>{{ $t("table.cols.name") }}</th>
                                <th>{{ $t("table.cols.email") }}</th>
                                <th>{{ $t("table.cols.status") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(c, cIdx) in employeeList.data">
                                <td>{{ c.company.name }}</td>
                                <td>{{ c.user.name }}</td>
                                <td>{{ c.user.email }}</td>
                                <td>
                                    <span v-if="c.user.profile.status === 1">{{ $t('statusDDL.active') }}</span>
                                    <span v-if="c.user.profile.status === 0">{{ $t('statusDDL.inactive') }}</span>
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
                            <li :class="{'page-item':true, 'disabled': this.employeeList.prev_page_url == null}">
                                <a class="page-link" href="#" aria-label="Previous" v-on:click="onPaginationChangePage('prev')">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li :class="{'page-item':true, 'disabled': this.employeeList.next_page_url == null}">
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
                    <Form id="employeeForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
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
                            <label class="col-2 col-form-label" for="company_id">{{ $t('fields.company_id') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="company_id" name="company_id" v-model="employee.company.hId" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option :value="c.hId" v-for="c in this.companyDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    {{ employee.company.name }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputName" class="col-2 col-form-label">{{ $t('fields.name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="employee.user.name" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ employee.user.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputEmail" class="col-2 col-form-label">{{ $t('fields.email') }}</label>
                            <div class="col-md-10">
                                <Field id="inputEmail" name="email" as="input" :class="{'form-control':true, 'is-invalid': errors['email']}" :placeholder="$t('fields.email')" :label="$t('fields.email')" v-model="employee.user.email" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="email" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ employee.user.email }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputImg" class="col-2 col-form-label"></label>
                            <div class="col-md-10">
                                <div class="push">
                                    <img class="img-avatar" :src="retrieveImage">
                                </div>
                                <div :class="{'custom-file':true, 'd-none':this.mode === 'show'}">
                                    <input type="file" class="custom-file-input" id="inputImg" name="img_path" data-toggle="custom-file-input" v-show="this.mode === 'create' || this.mode === 'edit'" v-on:change="handleUpload">
                                    <label class="custom-file-label" for="inputImg">Choose file</label>
                                </div>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ employee.user.profile.address }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputAddress" class="col-2 col-form-label">{{ $t('fields.address') }}</label>
                            <div class="col-md-10">
                                <input id="inputAddress" name="address" type="text" class="form-control" :placeholder="$t('fields.address')" v-model="employee.user.profile.address" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ employee.user.profile.address }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputCity" class="col-2 col-form-label">{{ $t('fields.city') }}</label>
                            <div class="col-md-10">
                                <input id="inputCity" name="city" type="text" class="form-control" :placeholder="$t('fields.city')" v-model="employee.user.profile.city" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ employee.user.profile.city }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputPostalCode" class="col-2 col-form-label">{{ $t('fields.postal_code') }}</label>
                            <div class="col-md-10">
                                <input id="inputPostalCode" name="postal_code" type="text" class="form-control" :placeholder="$t('fields.postal_code')" v-model="employee.user.profile.postal_code" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ employee.user.profile.postal_code }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputCountry" class="col-2 col-form-label">{{ $t('fields.country') }}</label>
                            <div class="col-md-10">
                                <select id="inputCountry" name="country" class="form-control" v-model="employee.user.profile.country" :placeholder="$t('fields.country')" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value="">{{ $t('placeholder.please_select') }}</option>
                                    <option v-for="c in countriesDDL" :key="c.name">{{ c.name }}</option>
                                </select>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ employee.user.profile.country }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputTaxId" class="col-2 col-form-label">{{ $t('fields.tax_id') }}</label>
                            <div class="col-md-10">
                                <Field id="inputTaxId" name="tax_id" as="input" :class="{'form-control':true, 'is-invalid': errors['tax_id']}" :placeholder="$t('fields.tax_id')" :label="$t('fields.tax_id')" v-model="employee.user.profile.tax_id" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="tax_id" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ employee.user.profile.tax_id }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputICNum" class="col-2 col-form-label">{{ $t('fields.ic_num') }}</label>
                            <div class="col-md-10">
                                <Field id="inputICNum" name="ic_num" as="input" :class="{'form-control':true, 'is-invalid': errors['ic_num']}" :placeholder="$t('fields.ic_num')" :label="$t('fields.ic_num')" v-model="employee.user.profile.ic_num" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="ic_num" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ employee.user.profile.ic_num }}</div>
                            </div>
                        </div>
                        <hr/>
                        <div class="form-group row">
                            <label for="inputStatus" class="col-2 col-form-label">{{ $t('fields.status') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <div>
                                    <select class="form-control" id="inputStatus" name="status" v-model="employee.user.profile.status" v-show="this.mode === 'create' || this.mode === 'edit'">
                                        <option value="1">{{ $t('statusDDL.active') }}</option>
                                        <option value="0">{{ $t('statusDDL.inactive') }}</option>
                                    </select>
                                    <div class="form-control-plaintext" v-if="this.mode === 'show'">
                                        <span v-if="employee.user.profile.status === 1">{{ $t('statusDDL.active') }}</span>
                                        <span v-if="employee.user.profile.status === 0">{{ $t('statusDDL.inactive') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputRemarks" class="col-2 col-form-label">{{ $t('fields.remarks') }}</label>
                            <div class="col-md-10">
                                <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="$t('fields.remarks')" v-model="employee.user.profile.remarks" v-show="this.mode === 'create' || this.mode === 'edit'" rows="3"></textarea>
                                <ErrorMessage name="remarks" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ employee.user.profile.remarks }}</div>
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
import { required, email } from '@vee-validate/rules';
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
defineRule('email', email);

export default {
    components: {
        Form, Field, ErrorMessage
    },
    setup() {
        const schema = {
            name: 'required',
            email: 'required|email',
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
            employeeList: [],
            employee: {
                company: {hId: ''},
                user: {
                    hId: '',
                    name: '',
                    email: '',
                    profile: {
                        hId: '',
                        address: '',
                        city: '',
                        postal_code: '',
                        country: '',
                        tax_id: '',
                        ic_num: '',
                        status: 1,
                        remarks: '',
                    },
                },
            },
            companyDDL: [],
            countriesDDL: [],
            listErrors: [],
            tableListErrors: [],
        }
    },
    created() {
    },

    mounted() {
        this.mode = 'list';
        this.getAllEmployee(1);
        this.getAllCompany();
        this.getCountries();
    },
    methods: {
        getAllEmployee(page) {
            this.loading = true;
            axios.get(route('api.get.dashboard.employee.read') + '?page=' + page).then(response => {
                this.employeeList = response.data;
                this.loading = false;
            });
        },
        getAllCompany() {
            axios.get(route('api.get.dashboard.company.read.all_active')).then(response => {
                this.companyDDL = response.data;
            });
        },
        getCountries() {
            axios.get(route('api.get.common.countries.read')).then(response => {
                this.countriesDDL = response.data;
            }).catch(e => {
                console.log(e.message);
            });
        },
        onPaginationChangePage(page) {
            if (page === 'next') {
                this.getAllEmployee(this.employeeList.current_page + 1);
            } else if (page === 'prev') {
                this.getAllEmployee(this.employeeList.current_page - 1);
            } else {
                this.getAllEmployee(page);
            }
        },
        emptyEmployee() {
            return {
                company: {hId: ''},
                user: {
                    hId: '',
                    name: '',
                    email: '',
                    profile: {
                        hId: '',
                        address: '',
                        city: '',
                        postal_code: '',
                        country: '',
                        tax_id: '',
                        ic_num: '',
                        img_path: '',
                        status: 1,
                        remarks: '',
                    },
                },
            }
        },
        createNew() {
            this.mode = 'create';
            this.employee = this.emptyEmployee();
        },
        editSelected(idx) {
            this.mode = 'edit';
            this.employee = this.employeeList.data[idx];
        },
        showSelected(idx) {
            this.mode = 'show';
            this.employee = this.employeeList.data[idx];
        },
        deleteSelected(idx) {
            this.mode = 'delete';
            this.employee = this.employeeList.data[idx];

            this.loading = true;
            axios.post(route('api.post.dashboard.company.employees.delete', this.employee.hId)).then(response => {
                this.backToList();
            }).catch(e => {
                this.handleError(e, actions);
                this.loading = false;
            });
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post(route('api.post.dashboard.company.employees.save'), new FormData($('#employeeForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else if (this.mode === 'edit') {
                axios.post(route('api.post.dashboard.company.employees.edit', this.employee.hId), new FormData($('#employeeForm')[0])).then(response => {
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
                this.employee.user.profile.img_path = fileReader.result
            })
            fileReader.readAsDataURL(files[0])
        },
        backToList() {
            this.mode = 'list';
            this.getAllEmployee(this.employeeList.current_page);
            this.employee = this.emptyEmployee();
        },
        toggleFullScreen() {
            this.fullscreen = !this.fullscreen;
        },
        toggleContentHidden() {
            this.contentHidden = !this.contentHidden;
        },
        refreshList() {
            this.getAllEmployee(this.employeeList.current_page);
        },
    },
    computed: {
        getPages() {
            if (this.employeeList.current_page == null) return 0;

            return Math.ceil(this.employeeList.total / this.employeeList.per_page);
        },
        retrieveImage()
        {
            if (this.employee.user.profile.img_path && this.employee.user.profile.img_path !== '') {
                if (this.employee.user.profile.img_path.includes('data:image')) {
                    return this.employee.user.profile.img_path;
                } else {
                    return '/storage/' + this.employee.user.profile.img_path;
                }
            } else {
                return '/images/def-user.png';
            }
        }
    }
}
</script>