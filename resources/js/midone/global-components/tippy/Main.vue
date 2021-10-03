<template>
    <component :is="tag" ref="tippyRef">
        <slot></slot>
    </component>
</template>

<script>
import { defineComponent, ref, inject, onMounted } from 'vue'
import tippy, { roundArrow, animateFill } from 'tippy.js'

export default defineComponent({
    props: {
        content: {
            type: String,
            required: true
        },
        tag: {
            type: String,
            default: 'span'
        },
        options: {
            type: Object,
            default: () => ({})
        },
        refKey: {
            type: String,
            default: null
        }
    },
    setup(props) {
        const tippyRef = ref()
        const init = () => {
            tippy(tippyRef.value, {
                plugins: [animateFill],
                content: props.content,
                arrow: roundArrow,
                popperOptions: {
                    modifiers: [
                        {
                            name: 'preventOverflow',
                            options: {
                                rootBoundary: 'viewport'
                            }
                        }
                    ]
                },
                animateFill: false,
                animation: 'shift-away',
                ...props.options
            })
        }

        const bindInstance = () => {
            if (props.refKey) {
                const bind = inject(`bind[${props.refKey}]`)
                if (bind) {
                    bind(tippyRef.value)
                }
            }
        }

        onMounted(() => {
            init()
            bindInstance()
        })

        return {
            tippyRef
        }
    }
})
</script>
