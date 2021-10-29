<template>
    <form @submit.prevent="onSubmit">
        <input type="text" name="name" v-model="model"/>
        <button type="submit" class="btn">aaaaaa</button>
        {{ errors }}
    </form>
</template>

<script setup>
import {inject, onMounted, ref} from "vue";
import { useForm, defineRule } from 'vee-validate';
import { required } from '@vee-validate/rules';

defineRule('required', required);

const model = ref('');

const { handleSubmit, errors } = useForm({
    initialValues: {
        name: ''
    },
   validationSchema: {
       name: 'required'
   },
    validateOnMount: false
});

const onSubmit = handleSubmit(values => {
    console.log('a');
});

onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);
});
</script>
