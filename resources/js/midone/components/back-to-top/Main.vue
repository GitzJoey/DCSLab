<template>
    <div class="flex justify-center" v-if="visible">
        <button class="btn-elevated-rounded" @click.prevent="goTop">
            <chevron-up-icon/>
        </button>
    </div>
</template>

<script>
import { defineComponent, toRef } from "vue";

export default defineComponent({
    name: "BackToTop",
    props: {
        visible: {type: Boolean, default: false},
    },
    setup(props) {
        const visible = toRef(props, 'visible');

        function goTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        return {
            goTop,
            visible
        }
    }
});
</script>
