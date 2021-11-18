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
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(c, cIdx) in productbrandList.data">
                                <td>{{ c.code }}</td>
                                <td>{{ c.name }}</td>
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
                            <li :class="{'page-item':true, 'disabled': this.productbrandList.prev_page_url == null}">
                                <a class="page-link" href="#" aria-label="Previous" v-on:click="onPaginationChangePage('prev')">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li :class="{'page-item':true, 'disabled': this.productbrandList.next_page_url == null}">
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
                    <Form id="productbrandForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
                        <div class="alert alert-warning alert-dismissable" role="alert" v-if="Object.keys(errors).length !== 0">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close" v-on:click="handleReset">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h3 class="alert-heading font-size-h5 font-w700 mb-5"><i class="fa fa-warning"></i>&nbsp;{{ $t('errors.warning') }}</h3>
                            <ul>
                                <li v-for="e in errors">{{ e }}</li>
                            </ul>
                        </div>
                        <td>
                            <input type="hidden" v-model="productbrand.company" name="company_id"/>
                        </td>
                        <div class="form-group row">
                            <label for="inputCode" class="col-2 col-form-label">{{ $t('fields.code') }}</label>
                            <div class="col-md-10">
                                <Field id="inputCode" name="code" as="input" :class="{'form-control':true, 'is-invalid': errors['code']}" :placeholder="$t('fields.code')" :label="$t('fields.code')" v-model="productbrand.code" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="code" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ productbrand.code }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputName" class="col-2 col-form-label">{{ $t('fields.name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="productbrand.name" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ productbrand.name }}</div>
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
            productbrandList: [],
            productbrand: {
                company: {hId: ''},
                code: '',
                name: '',
            },
            listErrors: [],
            tableListErrors: [],
        }
    },
    created() {
    },

    mounted() {
        this.mode = 'list';
        this.getAllProductBrand(1);
        this.getAllCompany();
        },
    methods: {
        getAllProductBrand(page) {
            this.loading = true;
            axios.get(route('api.get.dashboard.productbrand.read') + '?page=' + page).then(response => {
                this.productbrandList = response.data;
                this.loading = false;
            }).catch(e => {
                this.handleListError(e);
                this.loading = false;
            });
        },
        getAllCompany() {
            axios.get(route('api.get.dashboard.company.read.all_active')).then(response => {
                this.companyDDL = response.data;
            });
        },
        onPaginationChangePage(page) {
            if (page === 'next') {
                this.getAllProductBrand(this.productbrandList.current_page + 1);
            } else if (page === 'prev') {
                this.getAllProductBrand(this.productbrandList.current_page - 1);
            } else {
                this.getAllProductBrand(page);
            }
        },
        emptyProductBrand() {
            return {
                company: {hId: ''},
                code: '',
                name: '',
            }
        },
        createNew() {
            this.mode = 'create';
            this.productbrand = this.emptyProductBrand();
        },
        editSelected(idx) {
            this.mode = 'edit';
            this.productbrand = this.productbrandList.data[idx];
        },
        showSelected(idx) {
            this.mode = 'show';
            this.productbrand = this.productbrandList.data[idx];
        },
        deleteSelected(idx) {
            this.mode = 'delete';
            this.productbrand = this.productbrandList.data[idx];

            this.loading = true;
            axios.post(route('api.post.dashboard.productbrand.delete', this.productbrand.hId)).then(response => {
                this.backToList();
            }).catch(e => {
                this.handleError(e, actions);
                this.loading = false;
            });
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post(route('api.post.dashboard.productbrand.save'), new FormData($('#productbrandForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else if (this.mode === 'edit') {
                axios.post(route('api.post.dashboard.productbrand.edit', this.productbrand.hId), new FormData($('#productbrandForm')[0])).then(response => {
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
                this.productbrand.profile.img_path = fileReader.result
            })
            fileReader.readAsDataURL(files[0])
        },
        backToList() {
            this.mode = 'list';
            this.getAllProductBrand(this.productbrandList.current_page);
            this.productbrand = this.emptyProductBrand();
        },
        toggleFullScreen() {
            this.fullscreen = !this.fullscreen;
        },
        toggleContentHidden() {
            this.contentHidden = !this.contentHidden;
        },
        refreshList() {
            this.getAllProductBrand(this.productbrandList.current_page);
        },
    },
    computed: {
        getPages() {
            if (this.productbrandList.current_page == null) return 0;

            return Math.ceil(this.productbrandList.total / this.productbrandList.per_page);
        },
        retrieveImage()
        {
            if (this.productbrand.profile.img_path && this.productbrand.profile.img_path !== '') {
                if (this.productbrand.profile.img_path.includes('data:image')) {
                    return this.productbrand.profile.img_path;
                } else {
                    return '/storage/' + this.productbrand.profile.img_path;
                }
            } else {
                return '/images/def-productbrand.png';
            }
        }
    }
}
</script>