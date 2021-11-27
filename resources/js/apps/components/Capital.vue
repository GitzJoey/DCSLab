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
                                <th>{{ $t("table.cols.ref_number") }}</th>
                                <th>{{ $t("table.cols.date") }}</th>
                                <th>{{ $t("table.cols.investor") }}</th>
                                <th>{{ $t("table.cols.capital_group") }}</th>
                                <th>{{ $t("table.cols.cash_id") }}</th>
                                <th>{{ $t("table.cols.capital_status") }}</th>
                                <th>{{ $t("table.cols.amount") }}</th>
                                <th>{{ $t("table.cols.remarks") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(c, cIdx) in capitalList.data">
                                <td>{{ c.ref_number }}</td>
                                <td>{{ c.date }}</td>
                                <td>{{ c.investor.name }}</td> 
                                <td>{{ c.group.name }} </td> 
                                <td>{{ c.cash.name }}</td>
                                <td>
                                    <span v-if="c.capital_status === 1">{{ $t('capital_statusDDL.active') }}</span>
                                    <span v-if="c.capital_status === 0">{{ $t('capital_statusDDL.inactive') }}</span>
                                </td>
                                <td>{{ c.amount }}</td>
                                <td>{{ c.remarks }}</td>
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
                            <li :class="{'page-item':true, 'disabled': this.capitalList.prev_page_url == null}">
                                <a class="page-link" href="#" aria-label="Previous" v-on:click="onPaginationChangePage('prev')">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li :class="{'page-item':true, 'disabled': this.capitalList.next_page_url == null}">
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
                    <Form id="capitalForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
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
                            <label for="inputRefNumber" class="col-2 col-form-label">{{ $t('fields.ref_number') }}</label>
                            <div class="col-md-10">
                                <Field id="inputRefNumber" name="ref_number" as="input" :class="{'form-control':true, 'is-invalid': errors['ref_number']}" :placeholder="$t('fields.ref_number')" :label="$t('fields.ref_number')" v-model="capital.ref_number" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="ref_number" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ capital.ref_number }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="investor_id">{{ $t('fields.investor') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="investor_id" name="investor_id" v-model="capital.investor.hId" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option :value="c.hId" v-for="c in this.investorDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    {{ capital.investor.name }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="group_id">{{ $t('fields.capital_group') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="group_id" name="group_id" v-model="capital.group.hId" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option :value="c.hId" v-for="c in this.capitalgroupDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                </select>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    {{ capital.group.name }}
                                </div>            
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="cash_id">{{ $t('fields.cash_id') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="cash_id" name="cash_id" v-model="capital.cash.hId" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option :value="c.hId" v-for="c in this.cashDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                </select> 
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                    {{ capital.cash.name }}
                                </div>            
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputCapital_Status" class="col-2 col-form-label">{{ $t('fields.capital_status') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <div>
                                    <select class="form-control" id="inputStatus" name="capital_status" v-model="capital.capital_status" v-show="this.mode === 'create' || this.mode === 'edit'">
                                        <option value="1">{{ $t('capital_statusDDL.active') }}</option>
                                        <option value="0">{{ $t('capital_statusDDL.inactive') }}</option>
                                    </select>
                                    <div class="form-control-plaintext" v-show="this.mode === 'show'">
                                        <span v-if="capital.capital_status === 1">{{ $t('capital_statusDDL.active') }}</span>
                                        <span v-if="capital.capital_status === 0">{{ $t('capital_statusDDL.inactive') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputAmount" class="col-2 col-form-label">{{ $t('fields.amount') }}</label>
                            <div class="col-md-10">
                                <Field id="inputAmount" name="amount" as="input" :class="{'form-control':true, 'is-invalid': errors['amount']}" :placeholder="$t('fields.amount')" :label="$t('fields.amount')" v-model="capital.amount" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="amount" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ capital.amount }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputRemarks" class="col-2 col-form-label">{{ $t('fields.remarks') }}</label>
                            <div class="col-md-10">
                                <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="$t('fields.remarks')" v-model="capital.remarks" v-show="this.mode === 'create' || this.mode === 'edit'" rows="3"></textarea>
                                <div class="form-control-plaintext" v-show="this.mode === 'show'">{{ capital.remarks }}</div>
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
            ref_number: 'required',
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
            capitalList: [],
            capital: {
                ref_number: '',
                investor: {hId: ''},
                group: {hId: ''},
                cash: {hId: ''},
                capital_status: '1',
                amount: '',
                remarks: '',
                date: {
                    dateFormat: '',
                    timeFormat: '',
                }
            },
            investorDDL: [],
            capitalgroupDDL: [],
            cashDDL: [],
            capital_statusDDL: [],
            listErrors: [],
            tableListErrors: [],
        }
    },
    created() {
    },

    mounted() {
        this.mode = 'list';
        this.getAllCapital(1);
        this.getAllInvestor();
        this.getAllCapitalGroup();
        this.getAllCash();
    },
    methods: {
        getAllCapital(page) {
            this.loading = true;
            axios.get(route('api.get.dashboard.capital.read') + '?page=' + page) .then(response => {
                this.capitalList = response.data;
                this.loading = false;
            }).catch(e => {
                this.handleListError(e);
                this.loading = false;
            });
        },
        getAllInvestor() {
            axios.get(route('api.get.dashboard.investor.read.all_active')) .then(response => {
                this.investorDDL = response.data;
            });
        },
        getAllCapitalGroup() {
            axios.get(route('api.get.dashboard.capitalgroup.read.all_active')).then(response => {
                this.capitalgroupDDL = response.data;
            });
        },
        getAllCash() {
            axios.get(route('api.get.dashboard.cash.read.all_active')).then(response => {
                this.cashDDL = response.data;
            });
        },
        onPaginationChangePage(page) {
            if (page === 'next') {
                this.getAllCapital(this.capitalList.current_page + 1);
            } else if (page === 'prev') {
                this.getAllCapital(this.capitalList.current_page - 1);
            } else {
                this.getAllCapital(page);
            }
        },
        emptyCapital() {
            return {
                ref_number: '',
                investor: {hId: ''},
                group: {hId: ''},
                cash: {hId: ''},
                capital_status: '1',
                amount: '',
                remarks: '',
            }
        },
        createNew() {
            this.mode = 'create';
            this.capital = this.emptyCapital();
        },
        editSelected(idx) {
            this.mode = 'edit';
            this.capital = this.capitalList.data[idx];
        },
        showSelected(idx) {
            this.mode = 'show';
            this.capital = this.capitalList.data[idx];
        },
        deleteSelected(idx) {
            this.mode = 'delete';
            this.capital = this.capitalList.data[idx];

            this.loading = true;
            axios.post(route('api.post.dashboard.capital.delete', this.capital.hId)).then(response => {
                this.backToList();
            }).catch(e => {
                this.handleError(e, actions);
                this.loading = false;
            });
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post(route('api.post.dashboard.capital.save'), new FormData($('#capitalForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else if (this.mode === 'edit') {
                axios.post(route('api.post.dashboard.capital.edit', this.capital.hId), new FormData($('#capitalForm')[0])) .then(response => {
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
                this.capital.profile.img_path = fileReader.result
            })
            fileReader.readAsDataURL(files[0])
        },
        backToList() {
            this.mode = 'list';
            this.getAllCapital(this.capitalList.current_page);
            this.capital = this.emptyCapital();
        },
        toggleFullScreen() {
            this.fullscreen = !this.fullscreen;
        },
        toggleContentHidden() {
            this.contentHidden = !this.contentHidden;
        },
        refreshList() {
            this.getAllCapital(this.capitalList.current_page);
        },
    },
    computed: {
        getPages() {
            if (this.capitalList.current_page == null) return 0;

            return Math.ceil(this.capitalList.total / this.capitalList.per_page);
        },
        retrieveImage()
        {
            if (this.capital.profile.img_path && this.capital.profile.img_path !== '') {
                if (this.capital.profile.img_path.includes('data:image')) {
                    return this.capital.profile.img_path;
                } else {
                    return '/storage/' + this.capital.profile.img_path;
                }
            } else {
                return '/images/def-capital.png';
            }
        }
    }
}
</script>