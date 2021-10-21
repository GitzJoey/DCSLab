<template>
    <div class="intro-y box bg-white" v-if="visible">
        <div class="flex justify-center">
            <button class="btn btn-secondary rounded mr-1 mb-2" @click.prevent="goTop">
                <chevron-up-icon/>
            </button>
        </div>
    </div>
</template>

<script>
import {defineComponent, toRef} from "vue";

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
