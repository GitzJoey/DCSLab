<template>
    <div class="relative">
        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 xl:col-span-9 2xl:col-span-9 z-10">
                <br/><br/><br/>
            </div>
            <div class="report-box-3 px-5 pt-8 pb-14 col-span-12 z-10">
                <br/><br/><br/><br/><br/><br/><br/>
            </div>
        </div>
    </div>
    <div class="report-box-3 report-box-3--content grid grid-cols-12 gap-6 xl:-mt-5 2xl:-mt-8 -mb-10 z-40 2xl:z-10">
        <div class="col-span-12 2xl:col-span-9">
            <div class="grid grid-cols-12 gap-6">
                <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>                <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-3 relative z-10">
            <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
        </div>
    </div>
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
