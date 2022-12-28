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
    hId: string,
    uuid: string,
    code: string,
    name: string,
    status: string,
    nodes?: Array<Nodes>,
}

interface actionType {
    hId: string,
    uuid: string
}

defineProps<{
    modelValue: RootNode
}>();

const emit = defineEmits<{
    (e: 'triggerAction', data: actionType): void
}>()

const collapseState: Ref<boolean> = ref(true);

const toggleCollapse = () => {
    collapseState.value = !collapseState.value;
}

const triggerAction = (data: actionType) => {
    emit('triggerAction', data);
}
</script>

<template>
    <div class="basictreeview">
        <div class="border border-gray-300 flex flex-row h-50 items-center hover:border-gray-800 cursor-pointer rounded-xl px-2 py-1">
            <div class="border border-gray-400 rounded-xl" @click.prevent="toggleCollapse" v-if="modelValue.nodes != null">
                <div v-if="collapseState">
                    <ChevronsDownIcon />
                </div>
                <div v-else="!collapseState">
                    <ChevronsRightIcon />
                </div>
            </div>
            <div class="border border-gray-400 rounded-xl mr-2" v-else>
                <ChevronRightIcon />
            </div>

            <div class="ml-3 mr-10">{{ modelValue.name }}</div>
        </div>        
        <div class="p-2" v-if="!collapseState">
            <BasicTreeviewNodes v-for="item in modelValue.nodes" v-on:triggerAction="triggerAction" :hId="item.hId" :uuid="item.uuid" :code="item.code" :name="item.name" :status="item.status" :nodes="item.nodes" />
        </div>
    </div>
</template>