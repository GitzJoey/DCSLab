<template>
    <div v-if="!isEmptyObject(errors)">
        <div :class="{'alert':true, 'alert-primary':alertType === 'primary', 'alert-secondary':alertType === 'secondary', 'alert-success':alertType === 'success', 'alert-warning':alertType === 'warning', 'alert-danger':alertType === 'danger', 'alert-dark':alertType === 'dark', 'show mb-2':true}" role="alert">
            <div class="flex items-center">
                <div class="font-medium text-lg">
                    <AlertCircleIcon class="w-6 h-6 mr-2"  /> {{ title }}
                </div>
            </div>
            <div class="mt-3">
                <ul class="list-disc ml-5">
                    <template v-for="e in errors">
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
const props = defineProps({
    alertType: { type: String, default: 'danger' },
    errors: { type: Object, default: {} },
    title: { type: String }
});

import { computed, toRef } from 'vue'
import mainMixins from '../../mixins';

const { isEmptyObject } = mainMixins();

const errors = toRef(props, 'errors');

const title = toRef(props, 'title');

</script>
