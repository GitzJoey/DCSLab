<template>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ t('views.inbox.title') }}
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-3 2xl:col-span-2">
            <div class="intro-y box bg-theme-1 p-5 mt-5">
                <button type="button" class="btn text-gray-700 dark:text-slate-300 w-full bg-white dark:bg-theme-1 mt-1 btn-secondary" @click="createNew">
                    <Edit3Icon class="w-4 h-4 mr-2" />
                    {{ t('components.buttons.compose')}}
                </button>
                <div class="border-t border-theme-3 dark:border-dark-5 mt-6 pt-6 text-white">
                    <a href="" @click.prevent="getMessages('inbox')" :class="{'flex items-center px-3 py-2 rounded-md':true, 'bg-theme-25 dark:bg-dark-1 font-medium': messageFolder === 'inbox'}"> <MailIcon class="w-4 h-4 mr-2" /> Inbox </a>
                    <a href="" @click.prevent="getMessages('trash')" :class="{'flex items-center px-3 py-2 mt-2 rounded-md':true, 'bg-theme-25 dark:bg-dark-1 font-medium': messageFolder === 'trash'}"> <TrashIcon class="w-4 h-4 mr-2" /> Trash </a>
                </div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-9 2xl:col-span-10">
            <div class="intro-y inbox box mt-5" v-if="mode === 'list'">
                <div class="p-5 flex flex-col-reverse sm:flex-row text-slate-600 border-b border-gray-200 dark:border-dark-1">
                    <div class="flex items-center mt-3 sm:mt-0 border-t sm:border-0 border-gray-200 pt-5 sm:pt-0 mt-5 sm:mt-0 -mx-5 sm:mx-0 px-5 sm:px-0">
                        <input class="form-check-input" type="checkbox" @click="checkAll">
                    </div>
                    <div class="flex items-center sm:ml-auto">
                        <div class="dark:text-slate-300">1 - 50 of 5,238</div>
                    </div>
                </div>
                <div class="overflow-x-auto sm:overflow-x-visible">
                    <div class="intro-y">
                        <div class="inbox__item inbox__item--active inline-block sm:block text-gray-700 dark:text-slate-500 bg-gray-100 dark:bg-dark-1 border-b border-gray-200 dark:border-dark-1">
                            <div class="flex px-5 py-3">
                                <div class="w-50 flex-none flex items-center mr-5">
                                    <input class="form-check-input flex-none" type="checkbox" >
                                    <div class="w-6 h-6 flex-none image-fit relative ml-5">
                                        <img alt="" class="rounded-full" :src="assetPath('def-user.png')">
                                    </div>
                                    <div class="inbox__item--sender truncate ml-3">Brad Pitt</div>
                                </div>
                                <div class="w-64 sm:w-auto truncate"> <span class="inbox__item--highlight">There are many variations of passages of Lorem Ips</span> There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomi </div>
                                <div class="inbox__item--time whitespace-nowrap ml-auto pl-10">01:10 PM</div>
                            </div>
                        </div>
                    </div>
                    <div class="intro-y">
                        <div class="inbox__item inbox__item--active inline-block sm:block text-gray-700 dark:text-slate-500 bg-gray-100 dark:bg-dark-1 border-b border-gray-200 dark:border-dark-1">
                            <div class="flex px-5 py-3">
                                <div class="w-50 flex-none flex items-center mr-5">
                                    <input class="form-check-input flex-none" type="checkbox" >
                                    <div class="w-6 h-6 flex-none image-fit relative ml-5">
                                        <img alt="" class="rounded-full" :src="assetPath('def-user.png')">
                                    </div>
                                    <div class="inbox__item--sender truncate ml-3">Russell Crowe</div>
                                </div>
                                <div class="w-64 sm:w-auto truncate"> <span class="inbox__item--highlight">Contrary to popular belief, Lo</span> Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 20 </div>
                                <div class="inbox__item--time whitespace-nowrap ml-auto pl-10">01:10 PM</div>
                            </div>
                        </div>
                    </div>
                    <div class="intro-y">
                        <div class="inbox__item inline-block sm:block text-gray-700 dark:text-slate-500 bg-gray-100 dark:bg-dark-1 border-b border-gray-200 dark:border-dark-1">
                            <div class="flex px-5 py-3">
                                <div class="w-50 flex-none flex items-center mr-5">
                                    <input class="form-check-input flex-none" type="checkbox" checked>
                                    <div class="w-6 h-6 flex-none image-fit relative ml-5">
                                        <img alt="" class="rounded-full" :src="assetPath('def-user.png')">
                                    </div>
                                    <div class="inbox__item--sender truncate ml-3">Morgan Freeman</div>
                                </div>
                                <div class="w-64 sm:w-auto truncate"> <span class="inbox__item--highlight">Contrary to popular belief, Lo</span> Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 20 </div>
                                <div class="inbox__item--time whitespace-nowrap ml-auto pl-10">05:09 AM</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-5 flex flex-col sm:flex-row items-center text-center sm:text-left text-slate-600">
                    <div class="dark:text-slate-300">4.41 GB (25%) of 17 GB used Manage</div>
                    <div class="sm:ml-auto mt-2 sm:mt-0 dark:text-slate-300">Last account activity: 36 minutes ago</div>
                </div>
            </div>
            <div class="intro-y box mt-5" v-if="mode === 'create'">
                <div class="container p-5">
                    <div class="loader-container">
                        <VeeForm id="inboxForm" @submit="onSubmit" @invalid-submit="invalidSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
                            <div class="mb-3">
                                <label for="tagsTo" class="form-label">{{ t('views.inbox.fields.to') }}</label>
                                <TomSelectAjax v-model="message.to" class="w-full" multiple :searchURL="route('api.get.db.core.inbox.search.users')"></TomSelectAjax>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">{{ t('views.inbox.fields.subject') }}</label>
                                <input type="text" id="subject" class="form-control" v-model="message.subject" />
                            </div>
                            <div class="mb-3">
                                <label for="messages" class="form-label">{{ t('views.inbox.fields.message') }}</label>
                                <textarea id="messages" class="form-control" v-model="message.text" rows="5"></textarea>
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary mt-5 mr-3">{{ t('components.buttons.submit') }}</button>
                                <button class="btn btn-secondary" @click="backToList">{{ t('components.buttons.cancel') }}</button>
                            </div>
                        </VeeForm>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
//#region Imports
import { inject, onMounted, ref } from "vue";
import axios from "@/axios";
import { useI18n } from "vue-i18n";
import { assetPath } from "@/mixins";
import { route } from "@/ziggy";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - UI
const mode = ref('list');
//#endregion

//#region Data - Views
const messageFolder = ref('inbox');
const messageList = ref([]);
const message = ref({
    to: [],
    subject: '',
    text: ''
});
//#endregion

//#region onMounted
onMounted(() => {
    getMessages('inbox');
});
//#endregion

//#region Methods
function createNew() {
    mode.value = 'create';
    message.value = emptyMessage();
}

function emptyMessage() {
    return {
        to: [],
        subject: '',
        text: ''
    };
}

function getMessages(folder) {
    messageFolder.value = folder;
    if (folder === 'inbox') {
        axios.get(route('api.get.db.core.inbox.list.thread')).then(response => {
            messageList.value = response.data;
        }).catch(e => {

        });
    } else if (folder === 'trash') {
        axios.get(route('api.get.db.core.inbox.list.thread')).then(response => {
            messageList.value = response.data;
        }).catch(e => {

        });
    }
}

function checkAll() {

}

function backToList() {
    mode.value = 'list';
}
//#endregion
</script>
