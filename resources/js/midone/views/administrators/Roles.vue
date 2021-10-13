<template>

</template>

<script>
import {inject, onMounted} from "vue";

export default {
    name: "Roles",
    setup() {
        onMounted(() => {
            const setDashboardLayout = inject('setDashboardLayout')
            setDashboardLayout(false);
        });
    }
}
</script>
