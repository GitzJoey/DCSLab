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
                                <th>{{ $t("table.cols.code") }}</th>
                                <th>{{ $t("table.cols.name") }}</th>
                                <th>{{ $t("table.cols.address") }}</th>
                                <th>{{ $t("table.cols.city") }}</th>
                                <th>{{ $t("table.cols.contact") }}</th>
                                <th>{{ $t("table.cols.remarks") }}</th>
                                <th>{{ $t("table.cols.status") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(w, wIdx) in warehouseList.data">
                                <td>{{ w.company.name }}</td>
                                <td>{{ w.code }}</td>
                                <td>{{ w.name }}</td>
                                <td>{{ w.address }}</td>
                                <td>{{ w.city }}</td>
                                <td>{{ w.contact }}</td>
                                <td>{{ w.remarks }}</td>
                                <td>
                                    <span v-if="w.status === 1">{{ $t('statusDDL.active') }}</span>
                                    <span v-if="w.status === 0">{{ $t('statusDDL.inactive') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.show')" v-on:click="showSelected(wIdx)">
                                            <i class="fa fa-info"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.edit')" v-on:click="editSelected(wIdx)">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" :title="$t('actions.delete')" v-on:click="deleteSelected(wIdx)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-end">
                            <li :class="{'page-item':true, 'disabled': this.warehouseList.prev_page_url == null}">
                                <a class="page-link" href="#" aria-label="Previous" v-on:click="onPaginationChangePage('prev')">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li :class="{'page-item':true, 'disabled': this.warehouseList.next_page_url == null}">
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
                    <Form id="warehouseForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
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
                            <label class="col-2 col-form-label" for="example-select">{{ $t('fields.company_id') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="example-select" name="company_id">
                                    <option value="0">Please select Company Name</option>
                                    <option value="1">PT. ABC</option>
                                    <option value="2">PT. DEF</option>
                                    <option value="3">PT. GHI</option>
                                </select>             
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputCode" class="col-2 col-form-label">{{ $t('fields.code') }}</label>
                            <div class="col-md-10">
                                <Field id="inputCode" name="code" as="input" :class="{'form-control':true, 'is-invalid': errors['code']}" :placeholder="$t('fields.code')" :label="$t('fields.code')" v-model="warehouse.code" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="code" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ warehouse.code }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputName" class="col-2 col-form-label">{{ $t('fields.name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="warehouse.name" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ warehouse.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputAddress" class="col-2 col-form-label">{{ $t('fields.address') }}</label>
                            <div class="col-md-10">
                                <Field id="inputAddress" name="address" type="text" class="form-control" :placeholder="$t('fields.address')" v-model="warehouse.address" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="address" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ warehouse.address }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputCity" class="col-2 col-form-label">{{ $t('fields.city') }}</label>
                            <div class="col-md-10">
                                <Field id="inputCity" name="city" type="text" class="form-control" :placeholder="$t('fields.city')" v-model="warehouse.city" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="city" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ warehouse.city }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputContact" class="col-2 col-form-label">{{ $t('fields.contact') }}</label>
                            <div class="col-md-10">
                                <Field id="inputContact" name="contact" type="text" class="form-control" :placeholder="$t('fields.contact')" v-model="warehouse.contact" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="contact" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ warehouse.contact }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputRemarks" class="col-2 col-form-label">{{ $t('fields.remarks') }}</label>
                            <div class="col-md-10">
                                <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="$t('fields.remarks')" v-model="warehouse.remarks" v-show="this.mode === 'create' || this.mode === 'edit'" rows="3"></textarea>
                                <ErrorMessage name="remarks" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ warehouse.remarks }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputStatus" class="col-2 col-form-label">{{ $t('fields.status') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <div>
                                    <select class="form-control" id="inputStatus" name="status" v-model="warehouse.status" v-show="this.mode === 'create' || this.mode === 'edit'">
                                        <option value="1">{{ $t('statusDDL.active') }}</option>
                                        <option value="0">{{ $t('statusDDL.inactive') }}</option>
                                    </select>
                                    <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                        <span v-if="warehouse.status === 1">{{ $t('statusDDL.active') }}</span>
                                        <span v-if="warehouse.status === 0">{{ $t('statusDDL.inactive') }}</span>
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
            address: 'required',
            city: 'required',
            contact: 'required',
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
            warehouseList: { },
            warehouse: {
                company_id: '',
                code: '',
                name: '',
                address: '',
                city: '',
                contact: '',
                remarks: '',
                status: '',
            },
        }
    },
    created() {
    },

    mounted() {
        this.mode = 'list';
        this.getAllWarehouse(1);
    },
    methods: {
        getAllWarehouse(page) {
            this.loading = true;
            axios.get('/api/get/admin/warehouse/read?page=' + page).then(response => {
                this.warehouseList = response.data;
                this.loading = false;
            });
        },
        onPaginationChangePage(page) {
            if (page === 'next') {
                this.getAllWarehouse(this.warehouseList.current_page + 1);
            } else if (page === 'prev') {
                this.getAllWarehouse(this.warehouseList.current_page - 1);
            } else {
                this.getAllWarehouse(page);
            }
        },
        emptyWarehouse() {
            return {
                company_id:'',
                code: '',
                name: '',
                address: '',
                city: '',
                contact: '',
                remarks: '',
                status: '1',
            }
        },
        createNew() {
            this.mode = 'create';
            this.warehouse = this.emptyWarehouse();
        },
        editSelected(idx) {
            this.mode = 'edit';
            this.warehouse = this.warehouseList.data[idx];
        },
        showSelected(idx) {
            this.mode = 'show';
            this.warehouse = this.warehouseList.data[idx];
        },
        deleteSelected(idx) {
            this.mode = 'delete';
            this.warehouse = this.warehouseList.data[idx];

            this.loading = true;
            axios.post('/api/post/admin/warehouse/delete/'  + this.warehouse.hId).then(response => {
                this.backToList();
            }).catch(e => {
                this.handleError(e, actions);
                this.loading = false;
            });
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post('/api/post/admin/warehouse/save', new FormData($('#warehouseForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else if (this.mode === 'edit') {
                axios.post('/api/post/admin/warehouse/edit/' + this.warehouse.hId, new FormData($('#warehouseForm')[0]), {
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
                this.warehouse.profile.img_path = fileReader.result
            })
            fileReader.readAsDataURL(files[0])
        },
        backToList() {
            this.mode = 'list';
            this.getAllWarehouse(this.warehouseList.current_page);
            this.warehouse = this.emptyWarehouse();
        },
        toggleFullScreen() {
            this.fullscreen = !this.fullscreen;
        },
        toggleContentHidden() {
            this.contentHidden = !this.contentHidden;
        },
        refreshList() {
            this.getAllWarehouse(this.warehouseList.current_page);
        },
    },
    computed: {
        getPages() {
            if (this.warehouseList.current_page == null) return 0;

            return Math.ceil(this.warehouseList.total / this.warehouseList.per_page);
        },
        retrieveImage()
        {
            if (this.warehouse.profile.img_path && this.warehouse.profile.img_path !== '') {
                if (this.warehouse.profile.img_path.includes('data:image')) {
                    return this.warehouse.profile.img_path;
                } else {
                    return '/storage/' + this.warehouse.profile.img_path;
                }
            } else {
                return '/images/def-warehouse.png';
            }
        }
    }
}
</script>