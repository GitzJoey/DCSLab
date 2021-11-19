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
                                <th>{{ $t("table.cols.category") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(b, bIdx) in unitList.data">
                                <td>{{ b.code }}</td>
                                <td>{{ b.name }}</td>
                                <td>
                                    <span v-if="b.category === 1">{{ $t('categoryDDL.productunit') }}</span>
                                    <span v-if="b.category === 2">{{ $t('categoryDDL.serviceunit') }}</span>
                                    <span v-if="b.category === 3">{{ $t('categoryDDL.productandserviceunit') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.show')" v-on:click="showSelected(bIdx)">
                                            <i class="fa fa-info"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.edit')" v-on:click="editSelected(bIdx)">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.delete')" v-on:click="deleteSelected(bIdx)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-end">
                            <li :class="{'page-item':true, 'disabled': this.unitList.prev_page_url == null}">
                                <a class="page-link" href="#" aria-label="Previous" v-on:click="onPaginationChangePage('prev')">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li :class="{'page-item':true, 'disabled': this.unitList.next_page_url == null}">
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
                    <Form id="unitForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
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
                                <Field id="inputCode" name="code" as="input" :class="{'form-control':true, 'is-invalid': errors['code']}" :placeholder="$t('fields.code')" :label="$t('fields.code')" v-model="unit.code" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="code" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ unit.code }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputName" class="col-2 col-form-label">{{ $t('fields.name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="unit.name" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ unit.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="category" class="col-2 col-form-label">{{ $t('fields.category') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <select class="form-control" id="category" name="category" v-model="unit.category" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value="">{{ $t('placeholder.please_select') }}</option>
                                    <option value="1">{{ $t('categoryDDL.productunit') }}</option>
                                    <option value="2">{{ $t('categoryDDL.serviceunit') }}</option>
                                    <option value="3">{{ $t('categoryDDL.productandserviceunit') }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    <span v-if="unit.category === 1">{{ $t('categoryDDL.productunit') }}</span>
                                    <span v-if="unit.category === 2">{{ $t('categoryDDL.serviceunit') }}</span>
                                    <span v-if="unit.category === 3">{{ $t('categoryDDL.productandserviceunit') }}</span>
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
            unitList: [],
            unit: {
                code: 'AUTO',
                name: '',
                category: '',
            },
            listErrors: [],
            tableListErrors: [],
        }
    },
    created() {
    },

    mounted() {
        this.mode = 'list';
        this.getAllUnit(1);
    },
    methods: {
        getAllUnit(page) {
            this.loading = true;
            axios.get(route('api.get.dashboard.unit.read') + '?page=' + page).then(response => {
                this.unitList = response.data;
                this.loading = false;
            }).catch(e => {
                this.handleListError(e);
                this.loading = false;
            });
        },
        onPaginationChangePage(page) {
            if (page === 'next') {
                this.getAllUnit(this.unitList.current_page + 1);
            } else if (page === 'prev') {
                this.getAllUnit(this.unitList.current_page - 1);
            } else {
                this.getAllUnit(page);
            }
        },
        emptyUnit() {
            return {
                code: 'AUTO',
                name: '',
                category: '',
            }
        },
        createNew() {
            this.mode = 'create';
            this.unit = this.emptyUnit();
        },
        editSelected(idx) {
            this.mode = 'edit';
            this.unit = this.unitList.data[idx];
        },
        showSelected(idx) {
            this.mode = 'show';
            this.unit = this.unitList.data[idx];
        },
        deleteSelected(idx) {
            this.mode = 'delete';
            this.unit = this.unitList.data[idx];

            this.loading = true;
            axios.post(route('api.post.dashboard.unit.delete', this.unit.hId)).then(response => {
                this.backToList();
            }).catch(e => {
                this.handleError(e, actions);
                this.loading = false;
            });
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post(route('api.post.dashboard.unit.save'), new FormData($('#unitForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else if (this.mode === 'edit') {
                axios.post(route('api.post.dashboard.unit.edit', this.unit.hId), new FormData($('#unitForm')[0])).then(response => {
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
                this.unit.profile.img_path = fileReader.result
            })
            fileReader.readAsDataURL(files[0])
        },
        backToList() {
            this.mode = 'list';
            this.getAllUnit(this.unitList.current_page);
            this.unit = this.emptyUnit();
        },
        toggleFullScreen() {
            this.fullscreen = !this.fullscreen;
        },
        toggleContentHidden() {
            this.contentHidden = !this.contentHidden;
        },
        refreshList() {
            this.getAllUnit(this.unitList.current_page);
        },
    },
    computed: {
        getPages() {
            if (this.unitList.current_page == null) return 0;

            return Math.ceil(this.unitList.total / this.unitList.per_page);
        },
        retrieveImage()
        {
            if (this.unit.profile.img_path && this.unit.profile.img_path !== '') {
                if (this.unit.profile.img_path.includes('data:image')) {
                    return this.unit.profile.img_path;
                } else {
                    return '/storage/' + this.unit.profile.img_path;
                }
            } else {
                return '/images/def-unit.png';
            }
        }
    }
}
</script>