<script setup lang="ts">
import { computed, ref, toRef } from "vue";
import type { Ref } from "vue";
import BasicTreeviewNodes from "@/global-components/basic-treeview/Nodes.vue";

interface RootNode {
    code: string,
    name: string,
    nodes?: Array<Nodes>,
}

interface Nodes {
    code: string,
    name: string,
    status: string,
    nodes?: Array<Nodes>,
}

defineProps<{
    modelValue: RootNode
}>();

const collapseState: Ref<boolean> = ref(true);

const toggleCollapse = () => {
    collapseState.value = !collapseState.value;
}
</script>

<template>
    <div class="basictreeview">
        <div class="flex flex-row h-50 items-center bg-sky-500 hover:bg-gray-400 cursor-pointer rounded-xl p-2">
            <div @click.prevent="toggleCollapse" v-if="modelValue.nodes != null">
                <PlusIcon class="w-4 h-4" v-if="collapseState"/>
                <MinusIcon class="w-4 h-4" v-else="!collapseState"/>
            </div>
            <div class="mr-auto">{{ modelValue.name }}</div>
        </div>        
        <div class="bg-white z-10 rounded-xl p-5" v-if="!collapseState">
            <BasicTreeviewNodes v-for="item in modelValue.nodes" :code="item.code" :name="item.name" :status="item.status" :nodes="item.nodes" />
        </div>
    </div>
</template>