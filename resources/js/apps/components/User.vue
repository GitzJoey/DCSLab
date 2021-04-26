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
                                <th>{{ $t("table.cols.banned") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(u, uIdx) in userList.data">
                                <td>{{ u.name }}</td>
                                <td>{{ u.email }}</td>
                                <td>
                                    <span v-for="(r, rIdx) in u.roles">{{ r.display_name }}</span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.ban')">
                                        <i class="fa fa-check-circle"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.unban')">
                                        <i class="fa fa-xing-square"></i>
                                    </button>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.show')" v-on:click="showSelected(uIdx)">
                                            <i class="fa fa-info"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.edit')" v-on:click="editSelected(uIdx)">
                                            <i class="fa fa-pencil"></i>
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
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="user.name" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputEmail" class="col-2 col-form-label">{{ $t('fields.email') }}</label>
                            <div class="col-md-10">
                                <Field id="inputEmail" name="email" as="input" :class="{'form-control':true, 'is-invalid': errors['email']}" :placeholder="$t('fields.email')" :label="$t('fields.email')" v-model="user.email" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="email" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.email }}</div>
                            </div>
                        </div>
                        <hr/>
                        <div class="form-group row">
                            <label for="inputImg" class="col-2 col-form-label"></label>
                            <div class="col-md-10">
                                <img class="img" src="/images/def-user.png"/>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input js-custom-file-input-enabled" id="inputImg" name="img_path" data-toggle="custom-file-input" v-if="this.mode === 'create' || this.mode === 'edit'">
                                    <label class="custom-file-label" for="inputImg">Choose file</label>
                                </div>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.first_name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputFirstName" class="col-2 col-form-label">{{ $t('fields.first_name') }}</label>
                            <div class="col-md-10">
                                <input id="inputFirstName" name="first_name" type="text" class="form-control" :placeholder="$t('fields.first_name')" v-model="user.first_name" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.first_name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputLastName" class="col-2 col-form-label">{{ $t('fields.last_name') }}</label>
                            <div class="col-md-10">
                                <input id="inputLastName" name="last_name" type="text" class="form-control" :placeholder="$t('fields.last_name')" v-model="user.last_name" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.last_name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputCompanyName" class="col-2 col-form-label">{{ $t('fields.company_name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputCompanyName" name="company_name" as="input" :class="{'form-control':true, 'is-invalid': errors['company_name']}" :placeholder="$t('fields.company_name')" :label="$t('fields.company_name')" v-model="user.email" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="company_name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.company_name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputAddress" class="col-2 col-form-label">{{ $t('fields.address') }}</label>
                            <div class="col-md-10">
                                <input id="inputAddress" name="address" type="text" class="form-control" :placeholder="$t('fields.address')" v-model="user.address" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.address }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputCity" class="col-2 col-form-label">{{ $t('fields.city') }}</label>
                            <div class="col-md-10">
                                <input id="inputCity" name="city" type="text" class="form-control" :placeholder="$t('fields.city')" v-model="user.city" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.city }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputPostalCode" class="col-2 col-form-label">{{ $t('fields.postal_code') }}</label>
                            <div class="col-md-10">
                                <input id="inputPostalCode" name="postal_code" type="text" class="form-control" :placeholder="$t('fields.postal_code')" v-model="user.postal_code" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.postal_code }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputCountry" class="col-2 col-form-label">{{ $t('fields.country') }}</label>
                            <div class="col-md-10">
                                <select id="inputCountry" name="country" class="form-control" v-model="user.country" :placeholder="$t('fields.country')">
                                    <option value="">{{ $t('placeholder.please_select') }}</option>
                                    <option v-for="c in countriesDDL" :key="c.name">{{ c.name }}</option>
                                </select>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.country }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputTaxId" class="col-2 col-form-label">{{ $t('fields.tax_id') }}</label>
                            <div class="col-md-10">
                                <Field id="inputTaxId" name="tax_id" as="input" :class="{'form-control':true, 'is-invalid': errors['tax_id']}" :placeholder="$t('fields.tax_id')" :label="$t('fields.tax_id')" v-model="user.tax_id" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="tax_id" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.tax_id }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputICNum" class="col-2 col-form-label">{{ $t('fields.ic_num') }}</label>
                            <div class="col-md-10">
                                <Field id="inputICNum" name="ic_num" as="input" :class="{'form-control':true, 'is-invalid': errors['ic_num']}" :placeholder="$t('fields.ic_num')" :label="$t('fields.ic_num')" v-model="user.ic_num" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="ic_num" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.ic_num }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputRoles" class="col-2 col-form-label">{{ $t('fields.roles') }}</label>
                            <div class="col-md-10">
                                <select multiple class="form-control" id="inputRoles" name="roles[]" size="6" v-model="user.roles" :readonly="this.mode === 'show'">
                                    <option v-for="(r, rIdx) in rolesDDL" v-bind:value="rIdx">{{ r }}</option>
                                </select>
                                <ErrorMessage name="roles" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.roles }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputRemarks" class="col-2 col-form-label">{{ $t('fields.remarks') }}</label>
                            <div class="col-md-10">
                                <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="$t('fields.remarks')" v-model="user.remarks" v-if="this.mode === 'create' || this.mode === 'edit'" rows="3"></textarea>
                                <ErrorMessage name="remarks" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.remarks }}</div>
                            </div>
                        </div>
                        <hr/>
                        <div class="form-group row">
                            <label for="inputIsBanned" class="col-2 col-form-label">{{ $t('fields.banned') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <div>
                                    <select multiple class="form-control" id="inputIsBanned" name="is_banned" v-model="user.is_banned" :readonly="this.mode === 'show'">
                                        <option value="no">No</option>
                                        <option value="yes">Yes</option>
                                    </select>
                                    <ErrorMessage name="is_banned" class="invalid-feedback" />
                                    <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.is_banned }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputBannedReason" class="col-2 col-form-label">{{ $t('fields.banned_reason') }}</label>
                            <div class="col-md-10">
                                <Field id="inputBannedReason" name="banned_reason" as="input" :class="{'form-control':true, 'is-invalid': errors['banned_reason']}" :placeholder="$t('fields.banned_reason')" :label="$t('fields.banned_reason')" v-model="user.banned_reason" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="banned_reason" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ user.banned_reason }}</div>
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
import { required } from '@vee-validate/rules';
import { localize, setLocale } from '@vee-validate/i18n';
import en from '@vee-validate/i18n/dist/locale/en.json';
import id from '@vee-validate/i18n/dist/locale/id.json';


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
            name: 'required',
            email: 'required',
            company_name: 'required',
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
                country: ''
            },
            countriesDDL: [],
            rolesDDL: [],
        }
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
        createNew() {
            this.mode = 'create';
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
                axios.post('/api/post/admin/user/save', new FormData($('#useForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    console.log(e);
                });
            } else if (this.mode === 'edit') {
                axios.post('/api/post/admin/user/edit/' + this.role.hId, new FormData($('#userForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    console.log(e);
                });
            } else { }
        },
        backToList() {
            this.mode = 'list';
            this.getAllUser(this.userList.current_page);
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
                console.log(e);
            });
        }
    },
    computed: {
        getPages() {
            if (this.userList.current_page == null) return 0;

            return Math.ceil(this.userList.total / this.userList.per_page);
        }
    }
}
</script>
