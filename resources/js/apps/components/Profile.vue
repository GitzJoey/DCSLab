<template>
    <div class="block">
        <div class="block-header block-header-default">
            <h3 class="block-title">
                <i class="fa fa-user-circle mr-5 text-muted"></i> {{ $t('title') }}
            </h3>
        </div>
        <div class="block-content">
            <Form id="profileForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
                <div class="row items-push">
                    <div class="col-lg-3">
                        <div class="list-group push">
                            <a :class="{'list-group-item list-group-item-action d-flex justify-content-between align-items-center':true, 'active':this.mode === 'tabs_profile'}" @click="toggleTabs">
                                {{ $t('tabs.profile') }}
                            </a>
                            <a :class="{'list-group-item list-group-item-action d-flex justify-content-between align-items-center':true, 'active':this.mode === 'tabs_settings'}" @click="toggleTabs">
                                {{ $t('tabs.settings') }}
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-7 offset-lg-1">
                        <div class="alert alert-warning alert-dismissable" role="alert" v-if="Object.keys(errors).length !== 0">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h3 class="alert-heading font-size-h5 font-w700 mb-5"><i class="fa fa-warning"></i>&nbsp;{{ $t('errors.warning') }}</h3>
                            <ul>
                                <li v-for="e in errors">{{ e }}</li>
                            </ul>
                        </div>
                        <transition name="fade">
                            <div v-show="this.mode === 'tabs_profile'">
                                <div class="form-group row">
                                    <div class="col-12">
                                        <label for="name">{{ $t('fields.name') }}</label>
                                        <Field as="input" :class="{'form-control form-control-lg':true, 'is-invalid':errors['name']}" :label="$t('fields.name')" id="name" name="name" v-model="user.name"/>
                                        <ErrorMessage name="name" class="invalid-feedback" />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12">
                                        <label for="email">{{ $t('fields.email') }}</label>
                                        <input type="text" :class="{'form-control form-control-lg':true, 'is-invalid':errors['email']}" :label="$t('fields.email')" id="email" name="email" readonly v-model="user.email"/>
                                        <ErrorMessage name="email" class="invalid-feedback" />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-10 col-xl-6">
                                        <div class="push">
                                            <img class="img-avatar" :src="retrieveImage">
                                        </div>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="inputImg" name="img_path" data-toggle="custom-file-input" v-on:change="handleUpload"/>
                                            <label class="custom-file-label" for="avatar">Browse...</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12">
                                        <label for="firstName">{{ $t('fields.first_name') }}</label>
                                        <input type="text" class="form-control form-control-lg" id="firstName" name="first_name" v-model="user.profile.first_name"/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12">
                                        <label for="lastName">{{ $t('fields.last_name') }}</label>
                                        <input type="text" class="form-control form-control-lg" id="lastName" name="last_name" v-model="user.profile.last_name"/>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-12">
                                        <label for="address">{{ $t('fields.address') }}</label>
                                        <input type="text" class="form-control form-control-lg" id="address" name="address" v-model="user.profile.address"/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12">
                                        <label for="city">{{ $t('fields.city') }}</label>
                                        <input type="text" class="form-control form-control-lg" id="city" name="city" v-model="user.profile.city"/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12">
                                        <label for="postal_code">{{ $t('fields.postal_code') }}</label>
                                        <input type="text" class="form-control form-control-lg" id="postal_code" name="postal_code" v-model="user.profile.postal_code"/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12">
                                        <label for="country">{{ $t('fields.country') }}</label>
                                        <input type="text" class="form-control form-control-lg" id="country" name="country" readonly v-model="user.profile.country"/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12">
                                        <label for="tax_id">{{ $t('fields.tax_id') }}</label>
                                        <Field as="input" :class="{'form-control form-control-lg':true, 'is-invalid':errors['tax_id']}" :label="$t('fields.tax_id')" id="tax_id" name="tax_id" v-model="user.profile.tax_id"/>
                                        <ErrorMessage name="tax_id" class="invalid-feedback" />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12">
                                        <label for="ic_num">{{ $t('fields.ic_num') }}</label>
                                        <Field as="input" :class="{'form-control form-control-lg':true, 'is-invalid':errors['ic_num']}" :label="$t('fields.ic_num')" id="ic_num" name="ic_num" v-model="user.profile.ic_num"/>
                                        <ErrorMessage name="ic_num" class="invalid-feedback" />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12">
                                        <label for="remarks">{{ $t('fields.remarks') }}</label>
                                        <textarea type="text" rows="3" class="form-control form-control-lg" id="remarks" name="remarks" v-model="user.profile.remarks"/>
                                    </div>
                                </div>
                            </div>
                        </transition>
                        <transition name="fade">
                            <div v-show="this.mode === 'tabs_settings'">
                                <div class="row">
                                    <div class="col-6">
                                        <span>{{ $t('fields.settings.theme') }}</span>
                                        <select id="selectTheme" class="form-control" name="theme" v-model="user.selectedSettings.theme">
                                            <option value="corporate">Corporate</option>
                                            <option value="earth">Earth</option>
                                            <option value="elegance">Elegance</option>
                                            <option value="flat">Flat</option>
                                            <option value="pulse">Pulse</option>
                                        </select>
                                        <br/>
                                    </div>
                                    <div class="col-6">
                                        &nbsp;
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <span>{{ $t('fields.settings.dateFormat') }}</span>
                                        <select id="selectDate" class="form-control" name="dateFormat" v-model="user.selectedSettings.dateFormat">
                                            <option value="yyyy_MM_dd">{{ moment(new Date()).format('yyyy-MM-DD') }}</option>
                                            <option value="dd_MMM_yyyy">{{ moment(new Date()).format('DD-MMM-yyyy') }}</option>
                                        </select>
                                        <br/>
                                    </div>
                                    <div class="col-6">
                                        <span>{{ $t('fields.settings.timeFormat') }}</span>
                                        <select id="selectTime" class="form-control" name="timeFormat" v-model="user.selectedSettings.timeFormat">
                                            <option value="hh_mm_ss">{{ moment(new Date()).format('HH:mm:ss') }}</option>
                                            <option value="h_m_A">{{ moment(new Date()).format('h:m A') }}</option>
                                        </select>
                                        <br/>
                                    </div>
                                </div>
                            </div>
                        </transition>
                        <div class="form-group row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-alt-primary">{{ $t('buttons.update') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </Form>
        </div>
    </div>
</template>
<script>
import { Form, Field, ErrorMessage, defineRule, configure } from "vee-validate";
import { required, email } from '@vee-validate/rules';
import { localize, setLocale } from '@vee-validate/i18n';
import en from '@vee-validate/i18n/dist/locale/en.json';
import id from '@vee-validate/i18n/dist/locale/id.json';
import moment from 'moment';

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
            tax_id: 'required',
            ic_num: 'required',
        };

        return {
            schema
        };
    },
    data() {
        return {
            mode: 'tabs_profile',
            user: {
                profile: {

                },
                selectedSettings: {
                    theme: '',
                }
            },
        }
    },
    created() {
        this.moment = moment;
    },
    mounted() {
        this.getProfile();
    },
    methods: {
        getProfile() {
            axios.get('/api/get/profile/read').then(response => {
                this.user = response.data;
            });
        },
        onSubmit(values, actions) {
            axios.post('/api/post/profile/update', new FormData($('#profileForm')[0]), {
                headers: {
                    'content-type': 'multipart/form-data'
                }
            }).then(response => {

            }).catch(e => {
                this.handleError(e, actions);
            });
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
        toggleTabs() {
            if (this.mode === 'tabs_profile') {
                this.mode = 'tabs_settings';
            } else {
                this.mode = 'tabs_profile';
            }
        },
    },
    computed: {
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
