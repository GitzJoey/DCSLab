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
                                <th>{{ $t("table.cols.display_name") }}</th>
                                <th>{{ $t("table.cols.description") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(r, rIdx) in roleList.data">
                                <td>{{ r.name }}</td>
                                <td>{{ r.display_name }}</td>
                                <td>{{ r.description }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.show')" v-on:click="showSelected(rIdx)">
                                            <i class="fa fa-info"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.edit')" v-on:click="editSelected(rIdx)">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-end">
                            <li :class="{'page-item':true, 'disabled': this.roleList.prev_page_url == null}">
                                <a class="page-link" href="#" aria-label="Previous" v-on:click="onPaginationChangePage('prev')">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li :class="{'page-item': true, 'active': n === this.roleList.current_page}" v-for="n in getPages">
                                <a class="page-link" href="#" v-on:click="onPaginationChangePage(n)">{{ n }}</a>
                            </li>
                            <li :class="{'page-item':true, 'disabled': this.roleList.next_page_url == null}">
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
                    <Form id="roleForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
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
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="role.name" v-if="this.mode === 'create' || this.mode === 'edit'" :readonly="this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ role.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputDisplayName" class="col-2 col-form-label">{{ $t('fields.display_name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputDisplayName" name="display_name" as="input" :class="{'form-control':true, 'is-invalid': errors['display_name']}" v-model="role.display_name" :placeholder="$t('fields.display_name')" :label="$t('fields.display_name')" v-if="this.mode === 'create' || this.mode === 'edit'" />
                                <ErrorMessage name="display_name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ role.display_name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputDescription" class="col-2 col-form-label">{{ $t('fields.description') }}</label>
                            <div class="col-md-10">
                                <input id="inputDescription" name="description" type="text" class="form-control" v-model="role.description" :placeholder="$t('fields.description')" v-if="this.mode === 'create' || this.mode === 'edit'" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{role.description }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputPermissions" class="col-2 col-form-label">{{ $t('fields.permissions') }}</label>
                            <div class="col-md-10">
                                <select multiple :class="{'form-control':true, 'is-invalid':errors['permissions']}" id="inputPermissions" name="permissions[]" size="25" v-model="role.selectedPermissionIds" :readonly="this.mode === 'show'">
                                    <option v-for="(p, pIdx) in permissionsDDL" v-bind:value="p.hId">{{ p.display_name }}</option>
                                </select>
                                <ErrorMessage name="permissions" class="invalid-feedback" />
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
import { map } from 'lodash';

configure({
    validateOnInput: true,
    generateMessage: localize({ en, id }),
})

setLocale(document.documentElement.lang);

defineRule('required', required);

export default {
    components: {
        Form, Field, ErrorMessage,
    },
    setup() {
        const schema = {
            name: 'required',
            display_name: 'required',
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
            role: {
                selectedPermissionIds: []
            },
            roleList: { },
            permissionsDDL: [],
        }
    },
    mounted() {
        this.mode = 'list';
        this.getAllRole(1);
        this.getPermissions();
    },
    methods: {
        getAllRole(page) {
            this.loading = true;
            axios.get('/api/get/admin/role/read?page=' + page).then(response => {
                this.roleList = response.data;
                this.loading = false;
            });
        },
        getPermissions() {
            axios.get('/api/get/admin/role/permissions/read').then(response => {
                this.permissionsDDL = response.data;
            });
        },
        onPaginationChangePage(page) {
            if (page === 'next') {
                this.getAllRole(this.roleList.current_page + 1);
            } else if (page === 'prev') {
                this.getAllRole(this.roleList.current_page - 1);
            } else {
                this.getAllRole(page);
            }
        },
        createNew() {
            this.mode = 'create';
        },
        editSelected(idx) {
            this.mode = 'edit';
            this.role = this.roleList.data[idx];
            this.role.selectedPermissionIds = map(this.roleList.data[idx].permissions, 'hId');
        },
        showSelected(idx) {
            this.mode = 'show';
            this.role = this.roleList.data[idx];
            this.role.selectedPermissionIds = map(this.roleList.data[idx].permissions, 'hId');
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post('/api/post/admin/role/save', new FormData($('#roleForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else if (this.mode === 'edit') {
                axios.post('/api/post/admin/role/edit/' + this.role.hId, new FormData($('#roleForm')[0])).then(response => {
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
        backToList() {
            this.mode = 'list';
            this.getAllRole(this.roleList.current_page);
        },
        toggleFullScreen() {
            this.fullscreen = !this.fullscreen;
        },
        toggleContentHidden() {
            this.contentHidden = !this.contentHidden;
        },
        refreshList() {
            this.getAllRole(this.roleList.current_page);
        }
    },
    computed: {
        getPages() {
            if (this.roleList.current_page == null) return 0;

            return Math.ceil(this.roleList.total / this.roleList.per_page);
        }
    }
};
</script>
