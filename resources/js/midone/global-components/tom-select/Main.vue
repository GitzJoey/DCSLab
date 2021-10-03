<template>
    <select v-select="{ props, emit, computedOptions }" class="tom-select">
        <slot></slot>
    </select>
</template>

<script>
import { defineComponent, computed, watch } from 'vue'
import { setValue, init, reInit } from './index'

export default defineComponent({
    directives: {
        select: {
            mounted(el, { value }) {
                // Clone the select element to prevent tom select remove the original element
                const clonedEl = cash(el)
                    .clone()
                    .insertAfter(el)[0]
                cash(el).attr('hidden', true)

                // Initialize tom select
                setValue(clonedEl, value.props)
                init(clonedEl, value.emit, value.computedOptions)
            },
            updated(el, { value }) {
                const clonedEl = cash(el).next()[0]
                setValue(clonedEl, value.props)
                reInit(clonedEl, value.props, value.emit, value.computedOptions)
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
            type: [String, Number, Array],
            default: ''
        }
    },
    setup(props, context) {
        // Compute all default options
        const computedOptions = computed(() => {
            let options = {
                ...props.options,
                plugins: {
                    dropdown_input: {},
                    ...props.options.plugins
                }
            }

            if (Array.isArray(props.modelValue)) {
                options = {
                    persist: false,
                    create: true,
                    onDelete: function(values) {
                        return confirm(
                            values.length > 1
                                ? 'Are you sure you want to remove these ' +
                                values.length +
                                ' items?'
                                : 'Are you sure you want to remove "' + values[0] + '"?'
                        )
                    },
                    ...options,
                    plugins: {
                        remove_button: {
                            title: 'Remove this item'
                        },
                        ...options.plugins
                    }
                }
            }

            return options
        })

        // Watch value change
        watch(
            computed(() => props.modelValue),
            () => {
                context.emit('change')
            }
        )

        return {
            props,
            ...context,
            computedOptions
        }
    }
})
</script>
