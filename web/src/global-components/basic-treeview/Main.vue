<script setup lang="ts">
import { computed, ref, toRef } from "vue";
import type { Ref } from "vue";
import BasicTreeviewNodes from "@/global-components/basic-treeview/Nodes.vue";

interface RootNode {
    code: string,
    name: string,
    status: string,
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
        <div class="border border-gray-300 flex flex-row h-50 items-center hover:bg-gray-400 cursor-pointer rounded-xl p-2">
            <div class="border border-gray-400 rounded-xl" @click.prevent="toggleCollapse" v-if="modelValue.nodes != null">
                <div v-if="collapseState">
                    <ChevronDownIcon />
                </div>
                <div class="-rotate-90" v-else="!collapseState">
                    <ChevronDownIcon />
                </div>
            </div>
            <div class="border border-gray-400 rounded-xl" v-if="status != null">
                <CheckIcon class="text-success" v-if="status == 'ACTIVE'" />
                <XIcon class="text-danger" v-else />
            </div>
            <div class="ml-3 mr-10">{{ modelValue.name }}</div>
            <div class="border border-gray-400 rounded-xl" @click.prevent="toggleCollapse" v-if="nodes != null">
                <PlusIcon class="w-4 h-4" v-if="collapseState"/>
                <MinusIcon class="w-4 h-4" v-else="!collapseState"/>
            </div>
            <div v-else>
                <CircleIcon class="w-4 h-4" />
            </div>
        </div>        
        <div class="p-2" v-if="!collapseState">
            <BasicTreeviewNodes v-for="item in modelValue.nodes" :code="item.code" :name="item.name" :status="item.status" :nodes="item.nodes" />
        </div>
    </div>
</template>