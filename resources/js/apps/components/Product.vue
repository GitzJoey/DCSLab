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
                                <th>{{ $t("table.cols.group_name") }}</th>
                                <th>{{ $t("table.cols.brand_name") }}</th>
                                <th>{{ $t("table.cols.name") }}</th>
                                <th>{{ $t("table.cols.unit_name") }}</th>
                                <th>{{ $t("table.cols.price") }}</th>
                                <th>{{ $t("table.cols.tax") }}</th>
                                <th>{{ $t("table.cols.information") }}</th>
                                <th>{{ $t("table.cols.estimated_capital_price") }}</th>
                                <th>{{ $t("table.cols.point") }}</th>
                                <th>{{ $t("table.cols.point") }}</th>
                                <th>{{ $t("table.cols.is_use_serial") }}</th>
                                <th>{{ $t("table.cols.is_buy") }}</th>
                                <th>{{ $t("table.cols.is_production_material") }}</th>
                                <th>{{ $t("table.cols.is_production_result") }}</th>
                                <th>{{ $t("table.cols.is_sell") }}</th>
                                <th>{{ $t("table.cols.status") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(c, cIdx) in productList.data">
                                <td>{{ c.code }}</td>
                                <td>{{ c.group.name }}</td>
                                <td>{{ c.brand.name }} </td>
                                <td>{{ c.name }}</td>
                                <td>{{ c.unit.name }}</td>
                                <td>{{ c.price }}</td>
                                <td>{{ c.tax }}</td>
                                <td>{{ c.infortmation }}</td>
                                <td>{{ c.estimated_capital_price }}</td>
                                <td>{{ c.point }}</td>
                                <td>{{ c.is_use_serial }}</td>
                                <td>{{ c.is_buy }}</td>
                                <td>{{ c.is_production_material }}</td>
                                <td>{{ c.is_production_result }}</td>
                                <td>{{ c.is_sell }}</td>
                                <td>{{ c.status }}</td>
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
                            <li :class="{'page-item':true, 'disabled': this.productList.prev_page_url == null}">
                                <a class="page-link" href="#" aria-label="Previous" v-on:click="onPaginationChangePage('prev')">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li :class="{'page-item':true, 'disabled': this.productList.next_page_url == null}">
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
                    <Form id="productForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
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
                                <Field id="inputCode" name="code" as="input" :class="{'form-control':true, 'is-invalid': errors['code']}" :placeholder="$t('fields.code')" :label="$t('fields.code')" v-model="product.code" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="code" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ product.code }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="example-select">{{ $t('fields.group_name') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="example-select" name="example-select">
                                    <option value="0">Please select Group Name</option>
                                    <option :value="c.hId" v-for="c in this.groupDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                </select>             
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="example-select">{{ $t('fields.brand_name') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="example-select" name="example-select">
                                    <option value="0">Please select Brand Name</option>
                                    <option :value="c.hId" v-for="c in this.brandDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                </select>             
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputName" class="col-2 col-form-label">{{ $t('fields.name') }}</label>
                            <div class="col-md-10">
                                <Field id="inputName" name="name" as="input" :class="{'form-control':true, 'is-invalid': errors['name']}" :placeholder="$t('fields.name')" :label="$t('fields.name')" v-model="product.name" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="name" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ product.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="example-select">{{ $t('fields.unit_name') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="example-select" name="example-select">
                                    <option value="0">Please select Unit Name</option>
                                    <option :value="c.hId" v-for="c in this.unitDDL" v-bind:key="c.hId">{{ c.name }}</option>
                                </select>             
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputPrice" class="col-2 col-form-label">{{ $t('fields.price') }}</label>
                            <div class="col-md-10">
                                <Field id="inputPrice" name="price" as="input" :class="{'form-control':true, 'is-invalid': errors['price']}" :placeholder="$t('fields.price')" :label="$t('fields.price')" v-model="product.price" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="price" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ product.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label" for="example-select">{{ $t('fields.tax') }}</label>
                            <div class="col-md-10">
                                <select class="form-control" id="example-select" name="example-select">
                                    <option value="0">Please select Tax Option</option>
                                    <option value="1">No Tax</option>
                                    <option value="2">Exclude Tax</option>
                                    <option value="3">Include Tax</option>
                                </select>             
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputInformation" class="col-2 col-form-label">{{ $t('fields.information') }}</label>
                            <div class="col-md-10">
                                <textarea id="inputInformation" name="information" type="text" class="form-control" :placeholder="$t('fields.information')" v-model="product.information" v-if="this.mode === 'create' || this.mode === 'edit'" rows="3"></textarea>
                                <ErrorMessage name="information" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ product.information }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputPoint" class="col-2 col-form-label">{{ $t('fields.point') }}</label>
                            <div class="col-md-10">
                                <Field id="inputPoint" name="point" as="input" :class="{'form-control':true, 'is-invalid': errors['point']}" :placeholder="$t('fields.point')" :label="$t('fields.point')" v-model="product.point" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="point" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ product.point }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputEstimated_Capital_Price" class="col-2 col-form-label">{{ $t('fields.estimated_capital_price') }}</label>
                            <div class="col-md-10">
                                <Field id="inputEstimated_Capital_Price" name="estimated_capital_price" as="input" :class="{'form-control':true, 'is-invalid': errors['estimated_capital_price']}" :placeholder="$t('fields.estimated_capital_price')" :label="$t('fields.estimated_capital_price')" v-model="product.estimated_capital_price" v-if="this.mode === 'create' || this.mode === 'edit'"/>
                                <ErrorMessage name="estimatescapitalprice" class="invalid-feedback" />
                                <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ product.name }}</div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputStatus" class="col-2 col-form-label">{{ $t('fields.is_use_serial') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">
                                    <input type="checkbox" class="css-control-input">
                                    <span class="css-control-indicator"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputStatus" class="col-2 col-form-label">{{ $t('fields.is_buy') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">
                                    <input type="checkbox" class="css-control-input">
                                    <span class="css-control-indicator"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputStatus" class="col-2 col-form-label">{{ $t('fields.is_production_material') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">
                                    <input type="checkbox" class="css-control-input">
                                    <span class="css-control-indicator"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputStatus" class="col-2 col-form-label">{{ $t('fields.is_production_result') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">
                                    <input type="checkbox" class="css-control-input">
                                    <span class="css-control-indicator"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputStatus" class="col-2 col-form-label">{{ $t('fields.is_sell') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <label class="css-control css-control-primary css-checkbox">
                                    <input type="checkbox" class="css-control-input">
                                    <span class="css-control-indicator"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputStatus" class="col-2 col-form-label">{{ $t('fields.status') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <div>
                                    <select class="form-control" id="inputStatus" name="status" v-model="product.status" v-if="this.mode === 'create' || this.mode === 'edit'">
                                        <option value="ACTIVE">{{ $t('statusDDL.active') }}</option>
                                        <option value="INACTIVE">{{ $t('statusDDL.inactive') }}</option>
                                    </select>
                                    <div class="form-control-plaintext" v-if="this.mode === 'show'">
                                        <span v-if="product.status === 'ACTIVE'">{{ $t('statusDDL.active') }}</span>
                                        <span v-if="product.status === 'INACTIVE'">{{ $t('statusDDL.inactive') }}</span>
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
            price: 'required',
            estimated_capital_price: 'required',
            point: 'required',
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
            productList: [],
            product: {
                code: '',
                group_name: '',
                brand_name: '',
                name: '',
                unit_name: '',
                price: '',
                tax: '',
                information: '',
                point: '',
                estimated_capital_price: '',
                is_use_serial: '',
                is_buy: '',
                is_production_material: '',
                is_production_result: '',
                is_sell: '',
                status: '',
            },
            groupDDL: [],
            brandDDL: [],
            unitDDL: [],
        }
    },
    created() {
    },

    mounted() {
        this.mode = 'list';
        this.getAllProduct(1);
    },
    methods: {
        getAllProduct(page) {
            this.loading = true;
            axios.get('/api/get/admin/product/read?page=' + page).then(response => {
                this.productList = response.data;
                this.loading = false;
            });
        },
        onPaginationChangePage(page) {
            if (page === 'next') {
                this.getAllProduct(this.productList.current_page + 1);
            } else if (page === 'prev') {
                this.getAllProduct(this.productList.current_page - 1);
            } else {
                this.getAllProduct(page);
            }
        },
        emptyProduct() {
            return {
                code: '',
                group_name: '',
                brand_name: '',
                name: '',
                unit_name: '',
                price: '',
                tax: '',
                information: '',
                point: '',
                estimated_capital_price: '',
                is_use_serial: '',
                is_buy: '',
                is_production_material: '',
                is_production_result: '',
                is_sell: '',
                status: '',
            }
        },
        createNew() {
            this.mode = 'create';
            this.product = this.emptyProduct();
        },
        editSelected(idx) {
            this.mode = 'edit';
            this.product = this.productList.data[idx];
        },
        showSelected(idx) {
            this.mode = 'show';
            this.product = this.productList.data[idx];
        },
        deleteSelected(idx) {
            this.mode = 'delete';
            this.product = this.productList.data[idx];

            this.loading = true;
            axios.post('/api/post/admin/product/delete/'  + this.product.hId).then(response => {
                this.backToList();
            }).catch(e => {
                this.handleError(e, actions);
                this.loading = false;
            });
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post('/api/post/admin/product/save', new FormData($('#productForm')[0])). then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e, actions);
                    this.loading = false;
                });
            } else if (this.mode === 'edit') {
                axios.post('/api/post/admin/product/edit/' + this.product.hId, new FormData($('#productForm')[0])) .then(response => {
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
                this.product.profile.img_path = fileReader.result
            })
            fileReader.readAsDataURL(files[0])
        },
        backToList() {
            this.mode = 'list';
            this.getAllProduct(this.productList.current_page);
            this.product = this.emptyProduct();
        },
        toggleFullScreen() {
            this.fullscreen = !this.fullscreen;
        },
        toggleContentHidden() {
            this.contentHidden = !this.contentHidden;
        },
        refreshList() {
            this.getAllProduct(this.productList.current_page);
        },
    },
    computed: {
        getPages() {
            if (this.productList.current_page == null) return 0;

            return Math.ceil(this.productList.total / this.productList.per_page);
        },
        retrieveImage()
        {
            if (this.product.profile.img_path && this.product.profile.img_path !== '') {
                if (this.product.profile.img_path.includes('data:image')) {
                    return this.product.profile.img_path;
                } else {
                    return '/storage/' + this.product.profile.img_path;
                }
            } else {
                return '/images/def-product.png';
            }
        }
    }
}
</script>