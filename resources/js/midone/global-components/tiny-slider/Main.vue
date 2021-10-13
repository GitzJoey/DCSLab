<template>
    <div ref="sliderRef" v-slider="{ props, emit }" class="tiny-slider">
        <slot></slot>
    </div>
</template>

<script>
import { defineComponent, inject, onMounted, ref } from 'vue'
import { init, reInit } from './index'

export default defineComponent({
    directives: {
        slider: {
            mounted(el, { value }) {
                init(el, value.props)
            },
            updated(el) {
                reInit(el)
            }
        }
    },
    props: {
        options: {
            type: Object,
            default: () => ({})
        },
        refKey: {
            type: String,
            default: null
        }
    },
    setup(props, context) {
        const sliderRef = ref()
        const bindInstance = () => {
            if (props.refKey) {
                const bind = inject(`bind[${props.refKey}]`)
                if (bind) {
                    bind(sliderRef.value)
                }
            }
        }

        onMounted(() => {
            bindInstance()
        })

        return {
            props,
            ...context,
            sliderRef
        }
    }
})
</script>
