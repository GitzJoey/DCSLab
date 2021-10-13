<template>
    <Tippy :tag="tag" :options="{ placement: 'left' }" ref-key="sideMenuTooltipRef">
        <slot></slot>
    </Tippy>
</template>

<script>
import { defineComponent, provide, ref, onMounted } from 'vue'

export default defineComponent({
    props: {
        tag: {
            type: String,
            default: 'span'
        }
    },
    setup() {
        const tippyRef = ref()

        provide('bind[sideMenuTooltipRef]', el => {
            tippyRef.value = el
        })

        const toggleTooltip = () => {
            const el = tippyRef.value
            if (cash(window).width() <= 1260) {
                el._tippy.enable()
            } else {
                el._tippy.disable()
            }
        }

        const initTooltipEvent = () => {
            window.addEventListener('resize', () => {
                toggleTooltip()
            })
        }

        onMounted(() => {
            toggleTooltip()
            initTooltipEvent()
        })
    }
})
</script>
