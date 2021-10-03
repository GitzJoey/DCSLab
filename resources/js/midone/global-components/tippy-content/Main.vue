<template>
    <div ref="tippyRef">
        <slot></slot>
    </div>
</template>

<script>
import { defineComponent, ref, inject, onMounted } from 'vue'
import tippy, { roundArrow, animateFill } from 'tippy.js'

export default defineComponent({
    props: {
        to: {
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
            tippy(`[name="${props.to}"]`, {
                plugins: [animateFill],
                content: tippyRef.value,
                allowHTML: true,
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
                theme: 'light',
                trigger: 'click',
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
