<script setup lang="ts">
import { computed, ref, toRef } from "vue";
import BasicTreeviewNodes from "@/global-components/basic-treeview/Nodes.vue";

interface Nodes {
    code: string,
    name: string,
    nodes?: Array<Nodes>
}

defineProps<{
    modelValue: Nodes
}>();

</script>

<template>
    <div class="basictreeview">
        <div class="flex flex-row h-50">
            <ChevronDownIcon /> <div>{{ modelValue.name }}</div>            
        </div>
        <div class="nodes">
            <BasicTreeviewNodes v-for="item in modelValue.nodes" :code="item.code" :name="item.name" :nodes="item.nodes" />
        </div>
    </div>
</template>