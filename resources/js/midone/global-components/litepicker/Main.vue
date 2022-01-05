<template>
    <input v-picker="{ props, emit }" type="text" :value="modelValue" />
</template>

<script>
import { defineComponent } from 'vue'
import { setValue, init, reInit } from './index'

export default defineComponent({
    directives: {
        picker: {
            mounted(el, { value }) {
                setValue(value.props, value.emit)
                init(el, value.props, value.emit)
            },
            updated(el, { oldValue, value }) {
                if (oldValue.props.modelValue !== value.props.modelValue) {
                    reInit(el, value.props, value.emit)
                }
            }
        }
    },
    props: {
        options: {
            type: Object,
            default() {
                return {}
            }
        },
        modelValue: {
            type: String,
            default: ''
        }
    },
    setup(props, context) {
        return {
            props,
            ...context
        }
    }
})
</script>

<style scoped>
textarea {
    margin-left: 1000000px;
}
</style>
