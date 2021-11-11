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
                                <th>{{ $t("table.cols.remarks") }}</th>
                                <th>{{ $t("table.cols.status") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(c, cIdx) in serviceList.data">
                                <td>{{ c.code }}</td>
                                <td>{{ c.group.name }}</td>
                                <!-- <td>{{ c.unit.name }}</td> -->
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
                            <li :class="{'page-item':true, 'disabled': this.serviceList.prev_page_url == null}">
                                <a class="page-link" href="#" aria-label="Previous" v-on:click="onPaginationChangePage('prev')">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li :class="{'page-item':true, 'disabled': this.serviceList.next_page_url == null}">
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
                    <Form id="serviceForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
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
                                <Field id="inputCode" name="code" as="input" :class="{'form-control':true, 'is-invalid': errors['code']}" :placeholder="$t('fields.code')" :label="$t('fields.code')" v-model="service.code" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="code" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ service.code }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="group_id">{{ $t('fields.group_id') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="group_id" name="group_id" v-model="service.group.hId" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option :value="b.hId" v-for="b in this.groupDDL" v-bind:key="b.hId">{{ b.name }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    {{ service.group.name }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputName" class="col-2 col-form-label">{{ $t('fields.name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="service.name" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ service.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="unit_id">{{ $t('fields.unit_id') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="unit_id" name="unit_id" v-model="service.unit.hId" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option :value="c.hId" v-for="c in this.unitDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    {{ service.unit.name }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="tax_status" class="col-2 col-form-label">{{ $t('fields.tax_status') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <select class="form-control" id="tax_status" name="tax_status" v-model="service.tax_status" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value="">{{ $t('placeholder.please_select') }}</option>
                                    <option value="1">{{ $t('tax_statusDDL.notax') }}</option>
                                    <option value="2">{{ $t('tax_statusDDL.excudetax') }}</option>
                                    <option value="3">{{ $t('tax_statusDDL.includetax') }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    <span v-if="service.tax_status === 1">{{ $t('tax_statusDDL.notax') }}</span>
                                    <span v-if="service.tax_status === 2">{{ $t('tax_statusDDL.excudetax') }}</span>
                                    <span v-if="service.tax_status === 3">{{ $t('tax_statusDDL.includetax') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputRemarks" class="col-2 col-form-label">{{ $t('fields.remarks') }}</label>
                            <div class="col-md-10">
                                <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="$t('fields.remarks')" v-model="service.remarks" v-show="this.mode === 'create' || this.mode === 'edit'" rows="3"></textarea>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ service.remarks }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputPoint" class="col-2 col-form-label">{{ $t('fields.point') }}</label>
                            <div class="col-md-10">
                                <Field id="inputPoint" name="point" as="input" :class="{'form-control':true, 'is-invalid': errors['point']}" :placeholder="$t('fields.point')" :label="$t('fields.point')" v-model="service.point" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ service.point }}</div>
                            </div>
                        </div>
                        <!-- <div class="form-group row">
                            <label for="product_type" class="col-2 col-form-label">{{ $t('fields.product_type') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <select class="form-control" id="product_type" name="product_type" v-model="service.product_type" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value="">{{ $t('placeholder.please_select') }}</option>
                                    <option value="1">{{ $t('product_typeDDL.rawmaterial') }}</option>
                                    <option value="2">{{ $t('product_typeDDL.wip') }}</option>
                                    <option value="3">{{ $t('product_typeDDL.finishedgoods') }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    <span v-if="service.product_type === 1">{{ $t('product_typeDDL.rawmaterial') }}</span>
                                    <span v-if="service.product_type === 2">{{ $t('product_typeDDL.wip') }}</span>
                                    <span v-if="service.product_type === 3">{{ $t('product_typeDDL.finishedgoods') }}</span>
                                </div>
                            </div>
                        </div> -->
                        <td>
                            <input type="hidden" v-model="service.product_type" name="product_type"/>
                        </td>
                        <div class="form-group row">
                            <label for="status" class="col-2 col-form-label">{{ $t('fields.status') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <select class="form-control" id="status" name="status" v-model="service.status" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value='1'>{{ $t('statusDDL.active') }}</option>
                                    <option value='0'>{{ $t('statusDDL.inactive') }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    <span v-if="service.status === 1">{{ $t('statusDDL.active') }}</span>
                                    <span v-if="service.status === 0">{{ $t('statusDDL.inactive') }}</span>
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
            serviceList: [],
            service: {
                code: '',
                group: {hId: ''},
                name: '',
                unit: {hId: ''},
                tax_status: '',
                remarks: '',
                point: '',
                product_type: '',
                status: '',
            },
            groupDDL: [],
            unitDDL: [],
            statusDDL: [],
        }
    },
    created() {
    },

    mounted() {
        this.mode = 'list';
        this.getAllService(1);
        this.getAllProductGroup();
        this.getAllUnit();
        },
    methods: {
        getAllService(page) {
            this.loading = true;
            axios.get(route('api.get.dashboard.product.read.service') + '?page=' + page).then(response => {
                this.serviceList = response.data;
                this.loading = false;
            });
        },
        getAllProductGroup() {
            axios.get(route('api.get.dashboard.productgroup.read.all')).then(response => {
                this.groupDDL = response.data;
            });
        },
        getAllUnit() {
            axios.get(route('api.get.dashboard.unit.read.all')).then(response => {
                this.unitDDL = response.data;
            });
        },
        onPaginationChangePage(page) {
            if (page === 'next') {
                this.getAllService(this.serviceList.current_page + 1);
            } else if (page === 'prev') {
                this.getAllService(this.serviceList.current_page - 1);
            } else {
                this.getAllService(page);
            }
        },
        emptyService() {
            return {
                code: '',
                group: {hId: ''},
                name: '',
                unit: {hId: ''},
                tax_status: '',
                remarks: '',
                point: '',
                product_type: '4',
                status: '',
            }
        },
        createNew() {
            this.mode = 'create';
            this.service = this.emptyService();
        },
        editSelected(idx) {
            this.mode = 'edit';
            this.service = this.serviceList.data[idx];
        },
        showSelected(idx) {
            this.mode = 'show';
            this.service = this.serviceList.data[idx];
        },
        deleteSelected(idx) {
            this.mode = 'delete';
            this.service = this.serviceList.data[idx];

            this.loading = true;
            axios.post(route('api.post.dashboard.service.delete', this.service.hId)).then(response => {
                this.backToList();
            }).catch(e => {
                this.handleError(e, actions);
                this.loading = false;
            });
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post(route('api.post.dashboard.service.save'), new FormData($('#serviceForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else if (this.mode === 'edit') {
                axios.post(route('api.post.dashboard.service.edit', this.service.hId), new FormData($('#serviceForm')[0])).then(response => {
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
                this.service.profile.img_path = fileReader.result
            })
            fileReader.readAsDataURL(files[0])
        },
        backToList() {
            this.mode = 'list';
            this.getAllService(this.serviceList.current_page);
            this.service = this.emptyService();
        },
        toggleFullScreen() {
            this.fullscreen = !this.fullscreen;
        },
        toggleContentHidden() {
            this.contentHidden = !this.contentHidden;
        },
        refreshList() {
            this.getAllService(this.serviceList.current_page);
        },
    },
    computed: {
        getPages() {
            if (this.serviceList.current_page == null) return 0;

            return Math.ceil(this.serviceList.total / this.serviceList.per_page);
        },
        retrieveImage()
        {
            if (this.service.profile.img_path && this.service.profile.img_path !== '') {
                if (this.service.profile.img_path.includes('data:image')) {
                    return this.service.profile.img_path;
                } else {
                    return '/storage/' + this.service.profile.img_path;
                }
            } else {
                return '/images/def-service.png';
            }
        }
    }
}
</script>