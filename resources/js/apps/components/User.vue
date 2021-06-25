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
                                <th>{{ $t("table.cols.name") }}</th>
                                <th>{{ $t("table.cols.email") }}</th>
                                <th>{{ $t("table.cols.roles") }}</th>
                                <th>{{ $t("table.cols.status") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(u, uIdx) in userList.data">
                                <td>{{ u.name }}</td>
                                <td>{{ u.email }}</td>
                                <td>
                                    <span v-for="(r, rIdx) in u.roles">{{ r.display_name }}</span><br/>
                                </td>
                                <td>
                                    <span v-if="u.profile.status === 1">{{ $t('statusDDL.active') }}</span>
                                    <span v-if="u.profile.status === 0">{{ $t('statusDDL.inactive') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.show')" v-on:click="showSelected(uIdx)">
                                            <i class="fa fa-info"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.edit')" v-on:click="editSelected(uIdx)">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        &nbsp;
                                        &nbsp;
                                        &nbsp;
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.reset_password')" v-on:click="resetPassword(u.email)">
                                            <i class="fa fa-ticket"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-end">
                            <li :class="{'page-item':true, 'disabled': this.userList.prev_page_url == null}">
                                <a class="page-link" href="#" aria-label="Previous" v-on:click="onPaginationChangePage('prev')">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li :class="{'page-item': true, 'active': n === this.userList.current_page}" v-for="n in getPages">
                                <a class="page-link" href="#" v-on:click="onPaginationChangePage(n)">{{ n }}</a>
                            </li>
                            <li :class="{'page-item':true, 'disabled': this.userList.next_page_url == null}">
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
                    <Form id="userForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
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
                            <label for="inputName" class="col-2 col-form-label">{{ $t('fields.name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="user.name" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputEmail" class="col-2 col-form-label">{{ $t('fields.email') }}</label>
                            <div class="col-md-10">
                                <Field id="inputEmail" name="email" as="input" :class="{'form-control':true, 'is-invalid': errors['email']}" :placeholder="$t('fields.email')" :label="$t('fields.email')" v-model="user.email" v-show="this.mode === 'create' || this.mode === 'edit'" :readonly="this.mode === 'edit'"/>
                                <ErrorMessage name="email" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.email }}</div>
                            </div>
                        </div>
                        <hr/>
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
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.first_name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputFirstName" class="col-2 col-form-label">{{ $t('fields.first_name') }}</label>
                            <div class="col-md-10">
                                <input id="inputFirstName" name="first_name" type="text" class="form-control" :placeholder="$t('fields.first_name')" v-model="user.profile.first_name" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.profile.first_name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputLastName" class="col-2 col-form-label">{{ $t('fields.last_name') }}</label>
                            <div class="col-md-10">
                                <input id="inputLastName" name="last_name" type="text" class="form-control" :placeholder="$t('fields.last_name')" v-model="user.profile.last_name" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.profile.last_name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputAddress" class="col-2 col-form-label">{{ $t('fields.address') }}</label>
                            <div class="col-md-10">
                                <input id="inputAddress" name="address" type="text" class="form-control" :placeholder="$t('fields.address')" v-model="user.profile.address" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.profile.address }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputCity" class="col-2 col-form-label">{{ $t('fields.city') }}</label>
                            <div class="col-md-10">
                                <input id="inputCity" name="city" type="text" class="form-control" :placeholder="$t('fields.city')" v-model="user.profile.city" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.profile.city }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputPostalCode" class="col-2 col-form-label">{{ $t('fields.postal_code') }}</label>
                            <div class="col-md-10">
                                <input id="inputPostalCode" name="postal_code" type="text" class="form-control" :placeholder="$t('fields.postal_code')" v-model="user.profile.postal_code" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.profile.postal_code }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputCountry" class="col-2 col-form-label">{{ $t('fields.country') }}</label>
                            <div class="col-md-10">
                                <select id="inputCountry" name="country" class="form-control" v-model="user.profile.country" :placeholder="$t('fields.country')" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value="">{{ $t('placeholder.please_select') }}</option>
                                    <option v-for="c in countriesDDL" :key="c.name">{{ c.name }}</option>
                                </select>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.profile.country }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputTaxId" class="col-2 col-form-label">{{ $t('fields.tax_id') }}</label>
                            <div class="col-md-10">
                                <Field id="inputTaxId" name="tax_id" as="input" :class="{'form-control':true, 'is-invalid': errors['tax_id']}" :placeholder="$t('fields.tax_id')" :label="$t('fields.tax_id')" v-model="user.profile.tax_id" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="tax_id" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.profile.tax_id }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputICNum" class="col-2 col-form-label">{{ $t('fields.ic_num') }}</label>
                            <div class="col-md-10">
                                <Field id="inputICNum" name="ic_num" as="input" :class="{'form-control':true, 'is-invalid': errors['ic_num']}" :placeholder="$t('fields.ic_num')" :label="$t('fields.ic_num')" v-model="user.profile.ic_num" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="ic_num" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.profile.ic_num }}</div>
                            </div>
                        </div>
                        <hr/>
                        <div class="form-group row">
                            <label for="inputRoles" class="col-2 col-form-label">{{ $t('fields.roles') }}</label>
                            <div class="col-md-10">
                                <select multiple :class="{'form-control':true, 'is-invalid':errors['roles']}" id="inputRoles" name="roles[]" size="6" v-model="user.selectedRoles" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option v-for="(r, rIdx) in rolesDDL" :value="rIdx">{{ r }}</option>
                                </select>
                                <ErrorMessage name="roles" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">
                                    <span v-for="r in user.roles">{{ r.display_name }}&nbsp;</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputStatus" class="col-2 col-form-label">{{ $t('fields.status') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <div>
                                    <select class="form-control" id="inputStatus" name="status" v-model="user.profile.status" v-show="this.mode === 'create' || this.mode === 'edit'">
                                        <option value="1">{{ $t('statusDDL.active') }}</option>
                                        <option value="0">{{ $t('statusDDL.inactive') }}</option>
                                    </select>
                                    <div class="form-control-plaintext" v-if="this.mode === 'show'">
                                        <span v-if="user.profile.status === 1">{{ $t('statusDDL.active') }}</span>
                                        <span v-if="user.profile.status === 0">{{ $t('statusDDL.inactive') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputRemarks" class="col-2 col-form-label">{{ $t('fields.remarks') }}</label>
                            <div class="col-md-10">
                                <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="$t('fields.remarks')" v-model="user.profile.remarks" v-show="this.mode === 'create' || this.mode === 'edit'" rows="3"></textarea>
                                <ErrorMessage name="remarks" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.profile.remarks }}</div>
                            </div>
                        </div>
                        <hr/>
                        <div class="form-group row">
                            <label for="inputSettings" class="col-2 col-form-label">{{ $t('fields.settings.settings') }}</label>
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-6">
                                        <span>{{ $t('fields.settings.theme') }}</span>
                                        <select id="selectTheme" class="form-control" name="theme" v-model="user.selectedSettings.theme" v-show="this.mode === 'create' || this.mode === 'edit'">
                                            <option value="corporate">Corporate</option>
                                            <option value="earth">Earth</option>
                                            <option value="elegance">Elegance</option>
                                            <option value="flat">Flat</option>
                                            <option value="pulse">Pulse</option>
                                        </select>
                                        <div class="form-control-plaintext" v-if="this.mode === 'show' && this.user.selectedSettings.theme === 'corporate'">Corporate</div>
                                        <div class="form-control-plaintext" v-if="this.mode === 'show' && this.user.selectedSettings.theme === 'earth'">Earth</div>
                                        <div class="form-control-plaintext" v-if="this.mode === 'show' && this.user.selectedSettings.theme === 'elegance'">Elegance</div>
                                        <div class="form-control-plaintext" v-if="this.mode === 'show' && this.user.selectedSettings.theme === 'flat'">Flat</div>
                                        <div class="form-control-plaintext" v-if="this.mode === 'show' && this.user.selectedSettings.theme === 'pulse'">Pulse</div>
                                        <br/>
                                    </div>
                                    <div class="col-6">
                                        &nbsp;
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <span>{{ $t('fields.settings.dateFormat') }}</span>
                                        <select id="selectDate" class="form-control" name="dateFormat" v-model="user.selectedSettings.dateFormat" v-show="this.mode === 'create' || this.mode === 'edit'">
                                            <option value="yyyy_MM_dd">{{ moment(new Date()).format('yyyy-MM-DD') }}</option>
                                            <option value="dd_MMM_yyyy">{{ moment(new Date()).format('DD-MMM-yyyy') }}</option>
                                        </select>
                                        <div class="form-control-plaintext" v-if="this.mode === 'show' && this.user.selectedSettings.dateFormat === 'yyyy_MM_dd'">{{ moment(new Date()).format('yyyy-MM-DD') }}</div>
                                        <div class="form-control-plaintext" v-if="this.mode === 'show' && this.user.selectedSettings.dateFormat === 'dd_MMM_yyyy'">{{ moment(new Date()).format('DD-MMM-yyyy') }}</div>
                                        <br/>
                                    </div>
                                    <div class="col-6">
                                        <span>{{ $t('fields.settings.timeFormat') }}</span>
                                        <select id="selectTime" class="form-control" name="timeFormat" v-model="user.selectedSettings.timeFormat" v-show="this.mode === 'create' || this.mode === 'edit'">
                                            <option value="hh_mm_ss">{{ moment(new Date()).format('HH:mm:ss') }}</option>
                                            <option value="h_m_A">{{ moment(new Date()).format('h:m A') }}</option>
                                        </select>
                                        <div class="form-control-plaintext" v-if="this.mode === 'show' && this.user.selectedSettings.timeFormat === 'hh_mm_ss'">{{ moment(new Date()).format('HH:mm:ss') }}</div>
                                        <div class="form-control-plaintext" v-if="this.mode === 'show' && this.user.selectedSettings.timeFormat === 'h_m_A'">{{ moment(new Date()).format('h:m A') }}</div>
                                        <br/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label"></label>
                            <div class="col-md-10">
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
import { required, email } from '@vee-validate/rules';
import { localize, setLocale } from '@vee-validate/i18n';
import en from '@vee-validate/i18n/dist/locale/en.json';
import id from '@vee-validate/i18n/dist/locale/id.json';
import { find } from 'lodash';
import moment from 'moment';

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
            tax_id: 'required',
            ic_num: 'required',
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
            userList: { },
            user: {
                roles: [],
                selectedRoles: [],
                profile: {
                    country: '',
                    status: 1,
                },
                selectedSettings: {
                    theme: 'corporate',
                    dateFormat: '',
                    timeFormat: ''
                }
            },
            countriesDDL: [],
            rolesDDL: [],
        }
    },
    created() {
        this.moment = moment;
    },
    mounted() {
        this.mode = 'list';
        this.getAllUser(1);
        this.getAllRoles();
        this.getCountries();
    },
    methods: {
        getAllUser(page) {
            this.loading = true;
            axios.get('/api/get/admin/user/read?page=' + page).then(response => {
                this.userList = response.data;
                this.loading = false;
            });
        },
        onPaginationChangePage(page) {
            if (page === 'next') {
                this.getAllUser(this.userList.current_page + 1);
            } else if (page === 'prev') {
                this.getAllUser(this.userList.current_page - 1);
            } else {
                this.getAllUser(page);
            }
        },
        emptyUser() {
            return {
                roles: [],
                selectedRoles: [],
                profile: {
                    img_path: '',
                    country: '',
                    status: 1,
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
            this.user = this.emptyUser();
        },
        editSelected(idx) {
            this.mode = 'edit';
            this.user = this.userList.data[idx];
        },
        showSelected(idx) {
            this.mode = 'show';
            this.user = this.userList.data[idx];
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post('/api/post/admin/user/save', new FormData($('#userForm')[0]), {
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
                axios.post('/api/post/admin/user/edit/' + this.user.hId, new FormData($('#userForm')[0]), {
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
                this.user.profile.img_path = fileReader.result
            })
            fileReader.readAsDataURL(files[0])
        },
        backToList() {
            this.mode = 'list';
            this.getAllUser(this.userList.current_page);
            this.user = this.emptyUser();
        },
        toggleFullScreen() {
            this.fullscreen = !this.fullscreen;
        },
        toggleContentHidden() {
            this.contentHidden = !this.contentHidden;
        },
        refreshList() {
            this.getAllUser(this.userList.current_page);
        },
        getAllRoles() {
            axios.get('/api/get/admin/user/roles/read').then(response => {
                this.rolesDDL = response.data;
            }).catch(e => {
                console.log(e);
            });
        },
        getCountries() {
            axios.get('/api/get/common/countries/read').then(response => {
                this.countriesDDL = response.data;
            }).catch(e => {
                console.log(e.message);
            });
        },
        resetPassword(email) {

        }
    },
    computed: {
        getPages() {
            if (this.userList.current_page == null) return 0;

            return Math.ceil(this.userList.total / this.userList.per_page);
        },
        retrieveImage()
        {
            if (this.user.profile.img_path && this.user.profile.img_path !== '') {
                if (this.user.profile.img_path.includes('data:image')) {
                    return this.user.profile.img_path;
                } else {
                    return '/storage/' + this.user.profile.img_path;
                }
            } else {
                return '/images/def-user.png';
            }
        }
    }
}
</script>
