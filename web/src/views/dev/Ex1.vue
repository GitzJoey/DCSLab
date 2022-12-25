<template>
    <BasicTreeview v-model="data"/>
</template>

<script setup>
import { ref } from "vue";
import BasicTreeview from "@/global-components/basic-treeview/Main.vue";

const datanotfound = ref({});

const data = ref({
    code: '00',
    name: 'Root',
    nodes: [
        {
            code: '012',
            name: 'Level 1.2',
            status: 'ACTIVE',
            nodes: [
                {
                    code: '0121',
                    name: 'Level 1.2.1',
                }
            ]            
        },
        {
            code: '013',
            name: 'Level 1.3',
            status: 'INACTIVE',
            nodes: [
                {
                    code: '0131',
                    name: 'Level 1.3.1',
                    status: 'ACTIVE',
                },
                {
                    code: '0132',
                    name: 'Level 1.3.2',
                    status: 'ACTIVE',
                    nodes: [
                        {
                            code: '01321',
                            name: 'Level 1.3.2.1',
                            status: 'INACTIVE',
                        },
                        {
                            code: '01322',
                            name: 'Level 1.3.2.2',
                            status: 'ACTIVE',
                            nodes: [
                                {
                                    code: '013221',
                                    name: 'Level 1.3.2.2.1',
                                    status: 'ACTIVE',
                                },
                                {
                                    code: '013222',
                                    name: 'Level 1.3.2.2.2',
                                    status: 'INACTIVE',
                                },
                            ]
                        },
                    ]
                },
            ]
        },
        {
            code: '014',
            name: 'Level 1.4',
            status: 'ACTIVE',
        }
    ]
});

//#region Imports
//#endregion

//#region Declarations
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
//#endregion

//#region Data - Views
//#endregion

//#region onMounted
//#endregion

//#region Methods
//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>
