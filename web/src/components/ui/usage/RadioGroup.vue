<script lang="ts" setup>
import {
  RadioGroupRoot,
  RadioGroupLabel,
  RadioGroupItem,
  RadioGroupItemText,
  RadioGroupItemControl,
} from '@/components/ui/radio-group'

const frameworks = ['React', 'Solid', 'Vue', 'Svelte']
</script>

<template>
  <RadioGroupRoot defaultValue="React">
    <RadioGroupLabel>Framework</RadioGroupLabel>
    <RadioGroupItem v-for="framework in frameworks" :key="framework" :value="framework">
      <RadioGroupItemControl />
      <RadioGroupItemText>{{ framework }}</RadioGroupItemText>
    </RadioGroupItem>
  </RadioGroupRoot>
</template>
