<template>
    <div class="flex justify-center" v-if="visible">
        <button class="bg-white btn-elevated-rounded" @click.prevent="goTop">
            <chevron-up-icon/>
        </button>
    </div>
</template>

<script setup>
import { toRef } from "vue";

const props = defineProps({
    visible: {type: Boolean, default: false},
});

const visible = toRef(props, 'visible');

function goTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
