<template>

</template>

<script setup>
// Vue Import
import { inject, onMounted, ref, computed } from 'vue'
// Helper Import
import { getLang } from '../../lang';
import mainMixins from '../../mixins';
import { helper } from '../../utils/helper';
// Components Import
import DataList from '../../global-components/data-list/Main'
import AlertPlaceholder from '../../global-components/alert-placeholder/Main'

// Vee-Validate Schema
const schema = {
    code: 'required',
    name: 'required',
};
// Mixins
const { t, route } = mainMixins();

// Data - VueX
// Data - UI
const mode = ref('list');
const loading = ref(false);
const alertErrors = ref([]);
const deleteId = ref('');

// Data - Views

// onMounted
// Methods
// Computed
// Watcher
</script>
