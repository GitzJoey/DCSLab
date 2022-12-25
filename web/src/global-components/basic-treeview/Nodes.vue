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

const triggerAction = () => {
    alert('Trigger Action!');
}
</script>

<template>
    <div class="basictreeview_nodes">
        <div class="border border-gray-300 flex flex-row h-50 items-center hover:border-gray-800 cursor-pointer rounded-xl px-2 py-1">
            <div @click.prevent="toggleCollapse" class="border border-gray-400 rounded-xl mr-2" v-if="nodes != null">
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

            <div class="grid grid-cols-2 gap-2">
                <div class="border border-gray-400 rounded-xl" v-if="status != null">
                    <CheckIcon class="w-4 h-4 text-success" v-if="status == 'ACTIVE'" />
                    <XIcon class="w-4 h-4 text-danger" v-else />
                </div>
                <div class="border border-gray-400 rounded-xl" v-else>
                    <XIcon class="w-4 h-4 text-danger" />
                </div>

                <div class="border border-gray-400 rounded-xl" @click.prevent="triggerAction">
                    <PlusIcon class="w-4 h-4" />
                </div>
            </div>

            <div class="ml-3 mr-10">{{ code }} {{ name }}</div>
        </div>
        <div>
            <BasicTreeviewNodes v-if="!collapseState" v-for="item in nodes" :code="item.code" :name="item.name" :status="item.status" :nodes="item.nodes" />
        </div>
    </div>
</template>