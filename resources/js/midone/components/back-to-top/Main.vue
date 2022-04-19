<template>
    <div class="shadow-md fixed bottom-0 right-0 box border rounded-full h-6 px-2 flex items-center justify-center z-50 mb-5 mr-5" v-if="visible">
        <div class="cursor-pointer hover:animate-bounce" @click.prevent="goTop" @contextmenu.prevent="easterClick">
            <ChevronUpIcon />
        </div>
    </div>
</template>

<script setup>
import { ref, toRef } from "vue";

const props = defineProps({
    visible: {type: Boolean, default: false},
});

const emit = defineEmits(['easterClick']);

const visible = toRef(props, 'visible');

const easter = ref(0);

function goTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function easterClick() {
    easter.value++;
    if (easter.value % 5 === 0) emit('easterClick');
}
</script>
