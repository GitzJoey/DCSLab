<template>
    <select ref="el" class="tom-select" v-model="selected">
    </select>
</template>

<script setup>
import { computed, defineProps, ref, onMounted, toRef } from 'vue'
import TomSelect from "tom-select";

const props = defineProps({
    modelValue: {
        type: [String, Number, Array],
        default: ''
    },
    initialData: {
        type: Object,
        default: {}
    },
    searchURL: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue']);

const initialData = toRef(props, 'initialData');
const searchURL = toRef(props, 'searchURL');

const el = ref(null);
const defaultOptions = ref({
    persist: false,
    placeholder: 'Please select...',
    valueField: 'value',
    labelField: 'name',
    searchField: 'name',
    load: function (query, callback) {
        if (searchURL.value.length === 0) return callback();
        axios.get(searchURL.value + '?search=' + query).then(response => {
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
    options: [],
    items: []
});

onMounted(() => {
    if (el.TomSelect) el.TomSelect.destroy();
    el.TomSelect = new TomSelect(el.value, defaultOptions.value);
});

const selected = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})
</script>
