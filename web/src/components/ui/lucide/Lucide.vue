<script setup lang="ts">
import * as lucideIcons from 'lucide-vue-next'
import { cn } from '@midoneui/core/utils/cn'
import { computed, type HTMLAttributes } from 'vue'

export type Icon = keyof typeof lucideIcons

const props = defineProps<{
  icon: Icon
  class?: HTMLAttributes['class']
}>()

const iconComponent = computed(() => lucideIcons[props.icon] as any)
</script>

<template>
  <component
    :is="iconComponent"
    :class="
      cn(
        'size-4 stroke-[1.5] [--color:currentColor] stroke-(--color) fill-(--color)/25',
        props.class,
      )
    "
  />
</template>
