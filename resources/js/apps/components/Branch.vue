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
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(b, bIdx) in branchList.data">
                                <td>{{ b.company.name }}</td>
                                <td>{{ b.code }}</td>
                                <td>{{ b.name }}</td>
                                <td>{{ b.address }}</td>
                                <td>{{ b.city }}</td>
                                <td>{{ b.contact }}</td>
                                <td>{{ b.remarks }}</td>
                                <td>
                                    <span v-if="b.status === 1">{{ $t('statusDDL.active') }}</span>
                                    <span v-if="b.status === 0">{{ $t('statusDDL.inactive') }}</span>
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
                            <li :class="{'page-item':true, 'disabled': this.branchList.prev_page_url == null}">
                                <a class="page-link" href="#" aria-label="Previous" v-on:click="onPaginationChangePage('prev')">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li :class="{'page-item':true, 'disabled': this.branchList.next_page_url == null}">
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
                    <Form id="branchForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
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
                                    <option :value="c.hId" v-for="c in this.companyDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                </select>             
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputCode" class="col-2 col-form-label">{{ $t('fields.code') }}</label>
                            <div class="col-md-10">
                                <Field id="inputCode" name="code" as="input" :class="{'form-control':true, 'is-invalid': errors['code']}" :placeholder="$t('fields.code')" :label="$t('fields.code')" v-model="branch.code" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="code" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ branch.code }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputName" class="col-2 col-form-label">{{ $t('fields.name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="branch.name" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ branch.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputAddress" class="col-2 col-form-label">{{ $t('fields.address') }}</label>
                            <div class="col-md-10">
                                <Field id="inputAddress" name="address" as="input" :class="{'form-control':true, 'is-invalid': errors['address']}" :placeholder="$t('fields.address')" :label="$t('fields.address')" v-model="branch.address" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ branch.address }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputCity" class="col-2 col-form-label">{{ $t('fields.city') }}</label>
                            <div class="col-md-10">
                                <Field id="inputCity" name="city" as="input" :class="{'form-control':true, 'is-invalid': errors['city']}" :placeholder="$t('fields.city')" :label="$t('fields.city')" v-model="branch.city" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ branch.city }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputContact" class="col-2 col-form-label">{{ $t('fields.contact') }}</label>
                            <div class="col-md-10">
                                <Field id="inputContact" name="contact" as="input" :class="{'form-control':true, 'is-invalid': errors['contact']}" :placeholder="$t('fields.contact')" :label="$t('fields.contact')" v-model="branch.contact" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ branch.contact }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputRemarks" class="col-2 col-form-label">{{ $t('fields.remarks') }}</label>
                            <div class="col-md-10">
                                <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="$t('fields.remarks')" v-model="branch.remarks" v-if="this.mode === 'create' || this.mode === 'edit'" rows="3"></textarea>
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ branch.remarks }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputStatus" class="col-2 col-form-label">{{ $t('fields.status') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <div>
                                    <select class="form-control" id="inputStatus" name="status" v-model="branch.status" v-show="this.mode === 'create' || this.mode === 'edit'">
                                        <option value= '1'>{{ $t('statusDDL.active') }}</option>
                                        <option value= '0'>{{ $t('statusDDL.inactive') }}</option>
                                    </select>
                                    <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                        <span v-if="branch.status === 1">{{ $t('statusDDL.active') }}</span>
                                        <span v-if="branch.status === 0">{{ $t('statusDDL.inactive') }}</span>
                                    </div>
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
            branchList: [],
            branch: {
                company_id: '',
                code: '',
                name: '',
                address: '',
                city: '',
                contact: '',
                remarks: '',
                status: '',
            },
            companyDDL: [],
        }
    },
    created() {

    },
    mounted() {
        this.mode = 'list';
        this.getAllBranch(1);
        this.getAllCompany();
    },
    methods: {
        getAllBranch(page) {
            this.loading = true;
            axios.get('/api/get/dashboard/branch/read?page=' + page).then(response => {
                this.branchList = response.data;
                this.loading = false;
            });
        },
        getAllCompany() {
            axios.get('/api/get/dashboard/company/read/all/active').then(response => {
                this.companyDDL = response.data;
            });
        },
        onPaginationChangePage(page) {
            if (page === 'next') {
                this.getAllBranch(this.branchList.current_page + 1);
            } else if (page === 'prev') {
                this.getAllBranch(this.branchList.current_page - 1);
            } else {
                this.getAllBranch(page);
            }
        },
        emptyBranch() {
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
            this.branch = this.emptyBranch();
        },
        editSelected(idx) {
            this.mode = 'edit';
            this.branch = this.branchList.data[idx];
        },
        showSelected(idx) {
            this.mode = 'show';
            this.branch = this.branchList.data[idx];
        },
        deleteSelected(idx) {
            this.mode = 'delete';
            this.branch = this.branchList.data[idx];

            this.loading = true;
            axios.post('/api/post/dashboard/company/branches/delete/'  + this.branch.hId).then(response => {
                this.backToList();
            }).catch(e => {
                this.handleError(e, actions);
                this.loading = false;
            });
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post('/api/post/dashboard/company/branches/save', new FormData($('#branchForm')[0])). then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else if (this.mode === 'edit') {
                axios.post('/api/post/dashboard/company/branches/edit/' + this.branch.hId, new FormData($('#branchForm')[0])).then(response => {
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
                this.branch.profile.img_path = fileReader.result
            })
            fileReader.readAsDataURL(files[0])
        },
        backToList() {
            this.mode = 'list';
            this.getAllBranch(this.branchList.current_page);
            this.branch = this.emptyBranch();
        },
        toggleFullScreen() {
            this.fullscreen = !this.fullscreen;
        },
        toggleContentHidden() {
            this.contentHidden = !this.contentHidden;
        },
        refreshList() {
            this.getAllBranch(this.branchList.current_page);
        },
    },
    computed: {
        getPages() {
            if (this.branchList.current_page == null) return 0;

            return Math.ceil(this.branchList.total / this.branchList.per_page);
        },
        retrieveImage()
        {
            if (this.branch.profile.img_path && this.branch.profile.img_path !== '') {
                if (this.branch.profile.img_path.includes('data:image')) {
                    return this.branch.profile.img_path;
                } else {
                    return '/storage/' + this.branch.profile.img_path;
                }
            } else {
                return '/images/def-branch.png';
            }
        }
    }
}
</script>