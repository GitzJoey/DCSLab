<script lang="ts" setup>
import { computed } from "vue";
import { useUserContextStore } from '../../stores/user-context';
import _ from "lodash";

const userContextStore = useUserContextStore();
const userContext = computed(() => userContextStore.userContext);

const welcomeName = computed(() => userContext.value.profile.first_name);
</script>

<template>
    <div class="col-span-12 mt-8">
        <div class="flex items-center h-10 intro-y">
            <h2 class="mr-5 text-lg font-medium truncate">Welcome, {{ welcomeName }}</h2>
        </div>
    </div>
</template>