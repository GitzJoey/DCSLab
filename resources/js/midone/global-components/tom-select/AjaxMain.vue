<template>
    <select ref="el" class="tom-select" v-model="selected">
    </select>
</template>

<script setup>
import { defineComponent, computed, defineProps, ref, onMounted } from 'vue'
import TomSelect from "tom-select";

const props = defineProps({
    modelValue: {
        type: [String, Number, Array],
        default: ''
    }
});

const emit = defineEmits(['update:modelValue']);

const el = ref(null);
const options = ref([]);
const items = ref([]);
const defaultOptions = ref({
    persist: false,
    placeholder: 'Please Select',
    valueField: 'value',
    labelField: 'name',
    searchField: 'name',
    load: function (query, callback) {
        axios.get('/api/get/dashboard/core/inbox/search/users' + '?search=' + query).then(response => {
            callback(response.data);
        }).catch(e => {
            return callback();
        });
    },
    render: {
        option: function (data, escape) {
            return '<div>' + escape(data.name) + '</div>';
        },
        item: function (data, escape) {
            return '<div>' + escape(data.name) + '</div>';
        }
    },
    plugins: {
        dropdown_input: {},
        remove_button:{
            title: 'Remove',
        }
    },
    options: options.value,
    items: items.value,
    onChange: function(values) {
        //console.log(options.value);
        //console.log(items.value);
        //console.log(el.TomSelect.options);
        //console.log(el.TomSelect.items);
    }
});

onMounted(() => {
    el.TomSelect = new TomSelect(el.value, defaultOptions.value);
});

const selected = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})
</script>
