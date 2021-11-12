<template>
    <div v-if="!isEmptyObject(messages)">
        <div :class="{'alert':true, 'alert-primary':alertType === 'primary', 'alert-secondary':alertType === 'secondary', 'alert-success':alertType === 'success', 'alert-warning':alertType === 'warning', 'alert-danger':alertType === 'danger' || alertType === '', 'alert-dark':alertType === 'dark', 'show mb-2':true}" role="alert">
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
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, toRef, ref, watch } from 'vue'
import mainMixins from '../../mixins';

const props = defineProps({
    alertType: { type: String, default: 'danger' },
    messages: { type: Object, default: {} },
    title: { type: String }
});

const { isEmptyObject } = mainMixins();

const messages = toRef(props, 'messages');

const title = toRef(props, 'title');
</script>
