<script setup lang="ts">
import BasicTreeviewNodes from "@/global-components/basic-treeview/Nodes.vue";

interface Nodes {
    code: string,
    name: string,
    nodes?: Array<Nodes>
}

defineProps<{
    code: string,
    name: string,
    nodes?: Array<Nodes>
}>();

</script>

<template>
    <div class="basictreeview_nodes">
        <div class="flex flex-row h-50 items-center hover:bg-gray-400 cursor-pointer rounded-xl p-2">
            <ChevronDownIcon /> <div class="mr-auto">{{ name }}</div>   <CheckIcon class="text-success" /> <PlusIcon class="w-4 h-4" /> <MinusIcon class="w-4 h-4" />   
        </div>
        <BasicTreeviewNodes v-for="item in nodes" :code="item.code" :name="item.name" :nodes="item.nodes" />
    </div>
</template>