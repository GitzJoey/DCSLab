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
        <div class="flex flex-row h-50">
            <ChevronDownIcon /> <div>{{ name }}</div>            
        </div>
        <BasicTreeviewNodes v-for="item in nodes" :code="item.code" :name="item.name" :nodes="item.nodes" />
    </div>
</template>