<template>
    <div class="relative">
        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 xl:col-span-9 2xl:col-span-9 z-10">
            </div>
            <div class="report-box-3 px-5 pt-8 pb-14 col-span-12 z-10">
            </div>
        </div>
    </div>
    <div class="report-box-3 report-box-3--content grid grid-cols-12 gap-6 xl:-mt-5 2xl:-mt-8 -mb-10 z-40 2xl:z-10">
        <div class="col-span-12 2xl:col-span-9">
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 xl:col-span-4 mt-6">
                    <div class="intro-y flex items-center h-10">
                    </div>
                    <div class="mt-5">
                        <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
                    </div>
                </div>
                <div class="col-span-12 mt-6">
                    <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                    </div>
                    <div class="intro-y flex flex-wrap sm:flex-row sm:flex-nowrap items-center mt-3">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-3 relative z-10">
            <div class="2xl:border-l border-theme-31 pb-10 intro-y">
                <div class="2xl:pl-6 grid grid-cols-12 gap-6">
                </div>
            </div>
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
