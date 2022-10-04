<template>
    <Alert :show="!isEmptyObject(messages)" :class="{ 'alert-primary': alertType === 'primary', 'alert-secondary': alertType === 'secondary', 'alert-success': alertType === 'success', 'alert-warning': alertType === 'warning', 'alert-danger': alertType === 'danger' || alertType === '', 'alert-dark': alertType === 'dark', 'show mb-2':true }">
        <div class="flex flex-row">
            <div class="flex-none w-10">
                <AlertCircleIcon class="w-6 h-6 mr-2" />
            </div>
            <div class="flex-grow font-medium text-lg">{{ title }}</div>
            <div class="flex-none w-10">
                <div class="flex flex-row-reverse">

                </div>
            </div>
        </div>
        <div class="mt-3">
            <ul class="list-disc ml-5">
                <template v-for="e in messages">
                    <template v-if="Array.isArray(e)">
                        <li class="ml-5" v-for="ee in e">{{ ee }}</li>
                    </template>
                    <template v-else>
                        <li class="ml-5">{{ e }}</li>
                    </template>
                </template>
            </ul>
        </div>
    </Alert>
</template>

<script setup>
import { toRef, inject } from "vue";
import _ from "lodash";

const props = defineProps({
    alertType: { type: String, default: 'danger' },
    messages: { type: Object, default: {} },
    title: { type: String }
});

const isEmptyObject = (obj) => _.isEmpty(obj);

const messages = toRef(props, 'messages');

const title = toRef(props, 'title');
</script>
