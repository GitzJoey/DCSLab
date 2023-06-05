<script setup lang="ts">
import { toRef, computed } from "vue";
import Alert from "../Alert";
import Lucide from "../Lucide";
import _ from "lodash";

const props = defineProps({
    alertType: { type: String, default: 'danger' },
    messages: { type: Object, default: {} },
    title: { type: String, default: 'An unexpected error occurred.'}
});

const isEmptyObject = (obj: Object) => _.isEmpty(obj);

const alertType = toRef(props, 'alertType');
const messages = toRef(props, 'messages');
const title = toRef(props, 'title');

const computedVariant = computed(() => {
    if (alertType.value == 'danger') return 'soft-danger';
    if (alertType.value == 'success') return 'soft-success';
    if (alertType.value == 'warning') return 'soft-warning';
    if (alertType.value == 'pending') return 'soft-pending';
    if (alertType.value == 'dark') return 'soft-dark';
});
</script>

<template>
    <template v-if="!isEmptyObject(messages)">
        <Alert :variant="computedVariant" class="mb-2" v-slot="{ dismiss }">
            <div class="flex items-center">
                <Lucide icon="AlertCircle" class="w-6 h-6 mr-2" />
                <span class="font-medium">{{ title }}</span>
                <Alert.DismissButton type="button" class="btn-close" @click="dismiss" aria-label="Close">
                    <Lucide icon="X" class="w-4 h-4" />
                </Alert.DismissButton>
            </div>
            <div class="mt-3 ml-12">
                <ul class="list-disc">
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
</template>