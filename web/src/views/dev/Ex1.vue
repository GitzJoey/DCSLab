<template>
    <BasicTreeview v-model="data" v-on:triggerAction="triggerAction"/>
</template>

<script setup>
import { ref } from "vue";
import BasicTreeview from "@/global-components/basic-treeview/Main.vue";

const triggerAction = (data) => {
    console.log(data);
}

const datanotfound = ref({});

const data = ref({
    code: '00',
    name: 'Root',
    nodes: [
        {
            hId: '012',
            uuid: '012-012-012-012',
            code: '012',
            name: 'Level 1.2',
            status: 'ACTIVE',
            nodes: [
                {
                    hId: '0121',
                    uuid: '0121-0121-0121-0121',
                    code: '0121',
                    name: 'Level 1.2.1',
                }
            ]            
        },
        {
            hId: '013',
            uuid: '013-013-013-013',
            code: '013',
            name: 'Level 1.3',
            status: 'INACTIVE',
            nodes: [
                {
                    hId: '0131',
                    uuid: '0131-0131-0131-0131',
                    code: '0131',
                    name: 'Level 1.3.1',
                    status: 'ACTIVE',
                },
                {
                    hId: '0132',
                    uuid: '0132-0132-0132-0132',
                    code: '0132',
                    name: 'Level 1.3.2',
                    status: 'ACTIVE',
                    nodes: [
                        {
                            hId: '01321',
                            uuid: '01321-01321-01321-01321',
                            code: '01321',
                            name: 'Level 1.3.2.1',
                            status: 'INACTIVE',
                        },
                        {
                            hId: '01322',
                            uuid: '01322-01322-01322-01322',
                            code: '01322',
                            name: 'Level 1.3.2.2',
                            status: 'ACTIVE',
                            nodes: [
                                {
                                    hId: '013221',
                                    uuid: '013221-013221-013221-013221',
                                    code: '013221',
                                    name: 'Level 1.3.2.2.1',
                                    status: 'ACTIVE',
                                },
                                {
                                    hId: '013222',
                                    uuid: '013222-013222-013222-013222',
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
            hId: '014',
            uuid: '014-014-014-014',
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
