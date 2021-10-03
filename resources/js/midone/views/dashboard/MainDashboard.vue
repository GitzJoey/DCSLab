<template>

</template>

<script>
import { defineComponent, inject, onMounted } from 'vue'

export default defineComponent({
    setup() {
        onMounted(() => {
            const setDashboardLayout = inject('setDashboardLayout')
            setDashboardLayout(true)
        });
    }
})
</script>
