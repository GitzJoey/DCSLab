<script setup lang="ts">
import { ref } from "vue";
import Pagination from "../../base-components/Pagination";
import Button from "../../base-components/Button";
import {
    FormInput
} from "../../base-components/Form";

const is_active_1 = ref<boolean>(false);
const is_active_2 = ref<boolean>(true);

const inputTestVal = ref<number>(1);

const changeInputTriggered = () => {
    console.log('changeInputTriggered' + Date());
}
</script>

<template>
    <div>
        <div>
            <p>Pagination Test</p>
            <Pagination>
                <Pagination.Link :active="is_active_1" @click="is_active_1 = !is_active_1">test 1</Pagination.Link>
                <Pagination.Link :active="is_active_2" @click="is_active_2 = !is_active_2">test 2</Pagination.Link>
            </Pagination>

            <Button variant="primary" @click="is_active_1 = true">Set Test 1 as Active</Button>
        </div>
        <br />
        <div>
            <p>FormInput Test</p>
            <FormInput id="ic_num" v-model="inputTestVal" name="ic_num" type="text" @change="changeInputTriggered" />

            <Button variant="primary" @click="inputTestVal = 2">Change Input Value To 2</Button>
        </div>
    </div>
</template>