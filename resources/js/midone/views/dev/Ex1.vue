<template>

    <vee-form @submit="onSubmit" :validationSchema="validationSchema" v-slot="{ errors }">
        <input type="text" name="name" v-model="model"/>
        <button type="submit" class="btn">aaaaaa</button>
        {{ errors }}
    </vee-form>
</template>

<script setup>
import {inject, onMounted, ref} from "vue";

const model = ref('');

const validationSchema = {
    name: 'required'
};

function onSubmit() {
    console.log('a');
}

onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);
});
</script>
