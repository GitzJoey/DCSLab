<script lang="ts" setup>
import { onMounted, computed } from "vue"
import { useUserContextStore } from '@/stores/user-context'
import _ from "lodash"
import AuthService from "@/services/AuthService"

const userContextStore = useUserContextStore()
const authService = new AuthService()

const userContext = computed(() => userContextStore.userContext)

const welcomeName = computed(() => userContext.value?.profile.first_name)

onMounted(async () => {

});
</script>

<template>
    <div class="col-span-12 mt-8">
        <div class="flex items-center h-10 intro-y">
            <h2 class="mr-5 text-lg font-medium truncate">Welcome, {{ welcomeName }}</h2>
        </div>
    </div>
</template>