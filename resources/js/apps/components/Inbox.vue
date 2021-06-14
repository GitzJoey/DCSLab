<template>
    <div :class="{'block block-bordered block-themed block-mode-loading-refresh':true, 'block-mode-loading':this.loading, 'block-mode-fullscreen':this.fullscreen, 'block-mode-hidden':this.contentHidden}">
        <div class="block-header bg-gray-dark">
            <h3 class="block-title" v-if="this.mode === 'list'"><strong>{{ $t('table.title') }}</strong></h3>
            <h3 class="block-title" v-if="this.mode === 'create'"><strong>{{ $t('actions.create') }}</strong></h3>
            <h3 class="block-title" v-if="this.mode === 'edit'"><strong>{{ this.subjectEdited }}</strong></h3>
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
                    <table class="js-table-checkable table table-hover table-vcenter">
                        <tbody>
                            <tr v-for="(t, tIdx) in inboxList">
                                <td class="text-center" style="width: 40px;">
                                    <label class="css-control css-control-primary css-checkbox">
                                        <input type="checkbox" class="css-control-input">
                                        <span class="css-control-indicator"></span>
                                    </label>
                                </td>
                                <td class="d-none d-sm-table-cell font-w600" style="width: 240px;">{{ t.participants }}</td>
                                <td>
                                    <a class="font-w600" href="" @click.prevent="editSelected(t.hId, tIdx)">{{ t.subject }}</a>
                                    <div class="text-muted mt-5">{{ t.body }}</div>
                                </td>
                                <td class="d-none d-xl-table-cell font-w600 font-size-sm text-muted" style="width: 120px;">{{ t.updated_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </transition>
            <transition name="fade">
                <div id="crud_create" v-if="this.mode === 'create'">
                    <Form id="inboxForm" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
                        <div class="alert alert-warning alert-dismissable" role="alert" v-if="Object.keys(errors).length !== 0">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close" v-on:click="handleReset">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h3 class="alert-heading font-size-h5 font-w700 mb-5"><i class="fa fa-warning"></i>&nbsp;{{ $t('errors.warning') }}</h3>
                            <ul>
                                <li v-for="e in errors">{{ e }}</li>
                            </ul>
                        </div>
                        <div class="row">
                            <div class="col-md-10">
                                <div class="form-group row">
                                    <label for="inputTo" class="col-2 col-form-label">{{ $t('fields.to') }}</label>
                                    <div class="col-md-10">
                                        <div id="inputTo" :class="{'tagsinput':true, 'not_valid':errors['to']}" style="width: 100%; min-height: 34px; height: 34px;">
                                            <span class="tag" v-for="(t, tIdx) in tagsTo">
                                                <span>{{ t.full_name }}&nbsp;&nbsp;</span>
                                                <a href="" :title="$t('placeholder.removing') + ' ' + t.full_name" @click.prevent="removeTag(tIdx)">x</a>
                                            </span>
                                            <div id="inputTo_addTag">
                                                <input id="inputTo_tag" v-model="newTag" :placeholder="$t('placeholder.addnew')" @focus="clearNewTag" @blur="addTagByName" v-on:keyup.enter="addTagByName"/>
                                            </div>
                                            <div class="tags_clear"></div>
                                            <Field as="input" type="hidden" name="to" v-model="inbox.to" :label="$t('fields.to')" />
                                        </div>
                                        <ErrorMessage name="to" class="invalid-feedback" />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputSubject" class="col-2 col-form-label">{{ $t('fields.subject') }}</label>
                                    <div class="col-md-10">
                                        <Field id="inputSubject" name="subject" as="input" :class="{'form-control':true, 'is-invalid': errors['subject']}" :placeholder="$t('fields.subject')" :label="$t('fields.subject')" v-model="inbox.subject" v-if="this.mode === 'create'"/>
                                        <ErrorMessage name="subject" class="invalid-feedback" />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputMessage" class="col-2 col-form-label">{{ $t('fields.message') }}</label>
                                    <div class="col-md-10">
                                        <textarea id="inputMessage" name="message" type="text" class="form-control" :placeholder="$t('fields.message')" v-model="inbox.message" v-if="this.mode === 'create'" rows="5"></textarea>
                                        <ErrorMessage name="message" class="invalid-feedback" />
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
                            </div>
                            <div class="col-md-2">
                                <ul class="nav-users">
                                    <li v-for="(u, uIdx) in userList">
                                        <a @click="addTag(uIdx)">
                                            <img class="img-avatar" :src="retrieveImage(this.userList[uIdx].img_path)" alt="">
                                            <i class="fa fa-circle text-success"></i> {{ u.full_name }}
                                            <div class="font-w400 font-size-xs text-muted">{{ u.roles }}</div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </Form>
                </div>
            </transition>
            <transition name="fade">
                <div id="crud_edit" v-if="this.mode === 'edit'">
                    <div class="js-chat-window p-15 bg-light flex-grow-1 text-wrap-break-word overflow-y-auto">
                        <div :class="{'d-flex':true,'flex-row-reverse':m.reverse,'mb-20':true}" v-for="(m, mIdx) in this.messageList">
                            <div>
                                <a class="img-link img-status" href="">
                                    <img class="img-avatar img-avatar32" :src="retrieveImage(m.img_path)" alt="Avatar">
                                </a>
                            </div>
                            <div :class="{'mx-10':true,'text-right':m.reverse}">
                                <div>
                                    <p class="bg-primary-lighter text-primary-darker rounded px-15 py-10 mb-5 d-inline-block" v-if="m.reverse">
                                        {{ m.message }}
                                    </p>
                                    <p class="bg-body-dark text-dark rounded px-15 py-10 mb-5" v-else>
                                        {{ m.message }}
                                    </p>
                                </div>
                                <div class="text-muted font-size-xs font-italic">{{ m.full_name }} - {{ m.updated_at }}</div>
                            </div>
                        </div>
                    </div>
                    <hr/>
                    <div class="input-group input-group-lg">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fa fa-comment text-primary"></i>
                            </span>
                        </div>
                        <input class="form-control" type="text" v-model="newMessage" :placeholder="$t('placeholder.chat')" @keyup.enter="addMessage">
                    </div>
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
import { find, forEach } from 'lodash';
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
            to: 'required',
            subject: 'required',
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
            userList: [],
            inboxList: [],
            messageList: [],
            tagsTo: [],
            newTag: '',
            inbox: {
                hId: '',
                to: '',
                subject: '',
                message: ''
            },
            newMessage: '',
            subjectEdited: '',
        }
    },
    created() {
        this.moment = moment;
    },
    mounted() {
        this.mode = 'list';
        this.getInbox();
        this.getUserList();
    },
    methods: {
        getInbox(page) {
            this.loading = true;
            axios.get('/api/get/inbox/read').then(response => {
                this.inboxList = response.data;
                this.loading = false;
            }).catch(e => {
                console.log(e.message);
                this.loading = false;
            });
        },
        getUserList() {
            axios.get('/api/get/inbox/user/list/read').then(response => {
                this.userList = response.data;
            }).catch(e => {
                console.log(e.message);
            });
        },
        createNew() {
            this.mode = 'create';
        },
        editSelected(hId, idx) {
            this.mode = 'edit';
            this.loading = true;
            axios.get('/api/get/inbox/show/' + hId).then(response => {
                this.messageList = response.data;
                this.inbox.hId = this.inboxList[idx].hId;
                this.inbox.to = this.inboxList[idx].participant_user_ids;
                this.inbox.subject = this.inboxList[idx].subject;
                this.loading = false;
            }).catch(e => {
                console.log(e.message);
                this.loading = false;
            });
        },
        onSubmit(values, actions) {
            this.loading = true;
            if (this.mode === 'create') {
                axios.post('/api/post/inbox/save', new FormData($('#inboxForm')[0])).then(response => {
                    this.backToList();
                }).catch(e => {
                    console.log(e.message);
                    this.loading = false;
                });
            } else { }
        },
        handleError(e, actions) {
            if (e.response.data.errors !== undefined && Object.keys(e.response.data.errors).length > 0) {
                for (var key in e.response.data.errors) {
                    for (var i = 0; i < e.response.data.errors[key].length; i++) {
                        actions.setFieldError(key, e.response.data.errors[key][i]);
                    }
                }
            } else {
                actions.setFieldError('', e.response.data.message + ' (' + e.response.status + ' ' + e.response.statusText + ')');
            }
        },
        backToList() {
            this.mode = 'list';
            this.getInbox();
        },
        addMessage() {
            if (this.mode === 'edit') {
                axios.post('/api/post/inbox/edit/' + this.inbox.hId, {
                    hId: this.inbox.hId,
                    to: this.inbox.to,
                    subject: this.inbox.subject,
                    message: this.newMessage
                }).then(response => {
                    this.backToList();
                }).catch(e => {
                    this.handleError(e);
                    this.loading = false;
                });
            } else { }
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
        addTag(idx) {
            this.tagsTo.push({
                hId: this.userList[idx].hId,
                full_name: this.userList[idx].full_name
            });
        },
        addTagByName() {
            this.newTag = '';
        },
        clearTag() {
            this.newTag = '';
        },
        removeTag(idx) {
            this.tagsTo.splice(idx, 1);
        },
        retrieveImage(path)
        {
            if (path && path !== '') {
                return '/storage/' + path;
            } else {
                return '/images/def-user.png';
            }
        }
    },
    watch: {
        tagsTo: {
            handler(val, oldVal) {
                this.inbox.to = '';
                let s = '';
                _.forEach(this.tagsTo, function(val) {
                    s += val.hId + ',';
                });
                this.inbox.to = s.substring(0, s.length - 1);
            },
            deep: true
        }
    }
}
</script>
