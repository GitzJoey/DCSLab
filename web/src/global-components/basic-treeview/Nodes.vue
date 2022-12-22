<script setup lang="ts">
import { ref } from "vue";
import type { Ref } from "vue";
import BasicTreeviewNodes from "@/global-components/basic-treeview/Nodes.vue";

interface Nodes {
    code: string,
    name: string,
    status: string,
    nodes?: Array<Nodes>
}

defineProps<{
    code: string,
    name: string,
    status: string,
    nodes?: Array<Nodes>
}>();

const collapseState: Ref<boolean> = ref(true);

const toggleCollapse = () => {
    collapseState.value = !collapseState.value;
}
</script>

<template>
    <div class="basictreeview_nodes">
        <div class="flex flex-row h-50 items-center hover:bg-gray-400 cursor-pointer rounded-xl p-2">
            <div @click.prevent="toggleCollapse" v-if="nodes != null">
                <PlusIcon class="w-4 h-4" v-if="collapseState"/>
                <MinusIcon class="w-4 h-4" v-else="!collapseState"/>
            </div>
            <div v-else>
                <CircleIcon class="w-4 h-4" />
            </div>
            <div class="mr-auto">{{ code }} {{ name }}</div>
            <div v-if="status != null">
                <CheckIcon class="text-success" v-if="status == 'ACTIVE'" />
                <XIcon class="text-danger" v-else />
            </div>
        </div>
        <div>
            <BasicTreeviewNodes v-if="!collapseState" v-for="item in nodes" :code="item.code" :name="item.name" :status="item.status" :nodes="item.nodes" />
        </div>
    </div>
</template>